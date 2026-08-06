<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    /**
     * Display a listing of the vouchers.
     */
    public function index()
    {
        // Tự động cập nhật các voucher đã hết hạn theo thời gian
        Voucher::query()
            ->where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'expired']);

        $vouchers = Voucher::query()
            ->withCount(['orders' => function ($q) {
                $q->where('order_status', '<>', 'cancelled');
            }])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.Vouchers.index', compact('vouchers'));
    }

    /**
     * Show the form for creating a new voucher.
     */
    public function create()
    {
        return view('admin.Vouchers.create');
    }

    /**
     * Store a newly created voucher in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:vouchers,code'],
            'applies_to' => ['required', Rule::in(['product', 'shipping', 'order'])],
            'is_automatic' => ['nullable', 'boolean'],
            'shipping_method_code' => ['nullable', Rule::in(['standard', 'ghn_express'])],
            'usage_limit' => ['required', 'integer', 'min:0'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_shipping_discount' => ['nullable', 'numeric', 'min:0'],
            'min_order_value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'expired'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'code.required' => 'Vui lòng nhập mã voucher.',
            'code.unique' => 'Mã voucher này đã tồn tại.',
            'usage_limit.required' => 'Vui lòng nhập số lượt sử dụng tối đa.',
            'discount_value.required' => 'Vui lòng nhập số tiền giảm.',
            'min_order_value.required' => 'Vui lòng nhập giá trị đơn hàng tối thiểu.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.',
        ]);

        $data['is_automatic'] = $request->boolean('is_automatic');
        if ($data['applies_to'] !== 'shipping') {
            $data['shipping_method_code'] = null;
            $data['max_shipping_discount'] = null;
        }
        Voucher::create($data);

        return redirect()->route('admin.vouchers')
            ->with('success', 'Voucher đã được tạo thành công.');
    }

    /**
     * Show the form for editing the specified voucher.
     */
    public function edit($id)
    {
        $voucher = Voucher::findOrFail($id);
        return view('admin.Vouchers.edit', compact('voucher'));
    }

    /**
     * Update the specified voucher in storage.
     */
    public function update(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('vouchers', 'code')->ignore($voucher->id)],
            'applies_to' => ['required', Rule::in(['product', 'shipping', 'order'])],
            'is_automatic' => ['nullable', 'boolean'],
            'shipping_method_code' => ['nullable', Rule::in(['standard', 'ghn_express'])],
            'usage_limit' => ['required', 'integer', 'min:0'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_shipping_discount' => ['nullable', 'numeric', 'min:0'],
            'min_order_value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'expired'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'code.required' => 'Vui lòng nhập mã voucher.',
            'code.unique' => 'Mã voucher này đã tồn tại.',
            'usage_limit.required' => 'Vui lòng nhập số lượt sử dụng tối đa.',
            'discount_value.required' => 'Vui lòng nhập số tiền giảm.',
            'min_order_value.required' => 'Vui lòng nhập giá trị đơn hàng tối thiểu.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.',
        ]);

        $data['is_automatic'] = $request->boolean('is_automatic');
        if ($data['applies_to'] !== 'shipping') {
            $data['shipping_method_code'] = null;
            $data['max_shipping_discount'] = null;
        }

        $usedOrders = $voucher->orders()
            ->where('order_status', '<>', 'cancelled')
            ->count();

        // Chỉ tự động kích hoạt lại nếu trạng thái cũ đang là 'expired',
        // và admin đã chủ động tăng giới hạn sử dụng hoặc gia hạn thêm ngày kết thúc.
        if ($voucher->status === 'expired' && $data['status'] === 'expired') {
            $limitIncreased = (int) $data['usage_limit'] > (int) $voucher->usage_limit;
            $dateExtended = \Carbon\Carbon::parse($data['end_date'])->gt(\Carbon\Carbon::parse($voucher->end_date));

            if ($limitIncreased || $dateExtended) {
                $newLimit = (int) $data['usage_limit'];
                $endDate = \Carbon\Carbon::parse($data['end_date']);
                if (($newLimit === 0 || $usedOrders < $newLimit) && $endDate->isFuture()) {
                    $data['status'] = 'active';
                }
            }
        }

        $voucher->update($data);

        return redirect()->route('admin.vouchers')
            ->with('success', 'Voucher đã được cập nhật thành công.');
    }

    /**
     * Remove the specified voucher from storage.
     */
    public function destroy($id)
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->delete();

        return redirect()->route('admin.vouchers')
            ->with('success', 'Voucher đã được xóa thành công.');
    }
}
