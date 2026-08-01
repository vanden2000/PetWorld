<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeSectionController extends Controller
{
    /**
     * Hiển thị trang quản lý các khối hiển thị trang chủ.
     */
    public function index(): View
    {
        $sections = HomeSection::orderBy('order', 'asc')->get();

        return view('Admin.home-sections.index', compact('sections'));
    }

    /**
     * Cập nhật tất cả các khối trang chủ (Thứ tự, Tiêu đề, Trạng thái, Limit).
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:home_sections,id',
            'sections.*.order' => 'required|integer|min:0',
            'sections.*.custom_title' => 'nullable|string|max:255',
            'sections.*.limit' => 'nullable|integer|min:1|max:100',
        ]);

        $activeSectionIds = $request->input('active_sections', []);

        foreach ($request->input('sections') as $sectionData) {
            $section = HomeSection::find($sectionData['id']);
            if ($section) {
                $customTitle = isset($sectionData['custom_title']) && trim($sectionData['custom_title']) !== '' ? trim($sectionData['custom_title']) : null;
                $limit = isset($sectionData['limit']) && $sectionData['limit'] !== null && $sectionData['limit'] !== '' ? (int) $sectionData['limit'] : null;

                $section->update([
                    'order' => (int) $sectionData['order'],
                    'custom_title' => $customTitle,
                    'limit' => $limit,
                    'is_active' => in_array((string) $section->id, array_map('strval', $activeSectionIds), true),
                ]);
            }
        }

        // Xóa cache API trang chủ để thay đổi có hiệu lực tức thì
        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.home-sections.index')
            ->with('success', 'Đã cập nhật cấu hình hiển thị trang chủ thành công!');
    }

    /**
     * Đổi nhanh trạng thái Bật/Tắt của 1 khối.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $section = HomeSection::findOrFail($id);
        $section->is_active = !$section->is_active;
        $section->save();

        Cache::forget('api.home.sections.v1');

        $statusText = $section->is_active ? 'Bật' : 'Tắt';

        return redirect()->back()
            ->with('success', "Đã {$statusText} khối '{$section->name}'!");
    }

    /**
     * Khôi phục cài đặt mặc định của 12 khối trang chủ.
     */
    public function resetDefaults(): RedirectResponse
    {
        $defaultSeeder = new \Database\Seeders\HomeSectionSeeder();
        $defaultSeeder->run();

        Cache::forget('api.home.sections.v1');

        return redirect()->route('admin.home-sections.index')
            ->with('success', 'Đã khôi phục cài đặt mặc định cho các khối trang chủ!');
    }
}
