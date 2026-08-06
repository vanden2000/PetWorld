<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Xuất một báo cáo admin ra sheet Excel.
 *
 * Nhận sẵn phần tiêu đề, các ô tổng quan và bảng chi tiết đã dựng ở controller
 * nên dùng chung được cho mọi loại báo cáo dù cấu trúc dữ liệu khác nhau.
 */
class ReportExport implements FromArray, ShouldAutoSize, WithTitle, WithEvents
{
    /** Dòng (1-indexed) cần in đậm: tiêu đề bảng và header cột. */
    private array $boldRows = [];

    /** Dòng header cột, tô nền cam giống giao diện admin. */
    private array $headerRows = [];

    private int $columnCount = 1;

    public function __construct(
        private string $title,
        private string $periodLabel,
        private array $summary,
        private array $sections,
    ) {
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = [$this->title];
        $this->boldRows[] = count($rows);

        $rows[] = ['Kỳ báo cáo: ' . $this->periodLabel];
        $rows[] = ['Xuất lúc: ' . now()->format('d/m/Y H:i')];
        $rows[] = [];

        if ($this->summary !== []) {
            $rows[] = ['TỔNG QUAN'];
            $this->boldRows[] = count($rows);

            foreach ($this->summary as $label => $value) {
                $rows[] = [$label, $value];
            }

            $rows[] = [];
        }

        foreach ($this->sections as $section) {
            $headings = $section['headings'] ?? [];
            $data = $section['rows'] ?? [];

            $rows[] = [$section['title'] ?? ''];
            $this->boldRows[] = count($rows);

            if ($headings !== []) {
                $rows[] = $headings;
                $this->headerRows[] = count($rows);
                $this->columnCount = max($this->columnCount, count($headings));
            }

            if ($data === []) {
                $rows[] = ['(Không có dữ liệu trong kỳ này)'];
            } else {
                foreach ($data as $row) {
                    $rows[] = array_values($row);
                }
            }

            $rows[] = [];
        }

        return $rows;
    }

    public function title(): string
    {
        return mb_substr($this->title, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15);

                foreach ($this->boldRows as $row) {
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
                }

                foreach ($this->headerRows as $row) {
                    $range = "A{$row}:{$lastColumn}{$row}";
                    $sheet->getStyle($range)->getFont()->setBold(true)
                        ->getColor()->setARGB('FFFFFFFF');
                    $sheet->getStyle($range)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFF782D');
                    $sheet->getStyle($range)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle($range)->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }
            },
        ];
    }
}
