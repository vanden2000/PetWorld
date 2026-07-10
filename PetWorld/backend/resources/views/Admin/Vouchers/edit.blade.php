@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa Voucher')

@section('styles')
    <style>
        .error-message {
            color: #d93025;
            font-size: 0.85rem;
            margin-top: 4px;
        }
        .form-control.is-invalid {
            border-color: #d93025;
        }
        .form-group label {
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
            display: inline-block;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: #fff;
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 120, 45, 0.15);
        }
        .btn-cancel {
            background-color: #f1f3f4;
            color: #5f6368;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-cancel:hover {
            background-color: #e8eaed;
        }
        .btn-save {
            background-color: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-save:hover {
            filter: brightness(0.95);
        }
        /* Custom layout styles matching category create grid */
        .voucher-create-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }
        .form-card {
            background-color: #fff;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 24px;
            margin-bottom: 24px;
        }
        .form-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-main);
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 12px;
        }
        .form-card-title i {
            color: var(--primary);
        }
        .form-group-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .custom-radio-container {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s;
        }
        .custom-radio-container:hover {
            background-color: #f8f9fa;
        }
        .custom-radio-container input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }
        .radio-label-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-main);
        }
        @media (max-width: 992px) {
            .voucher-create-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
<form action="{{ route('admin.vouchers.update', $voucher->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <!-- Dashboard Header Nav Bar -->
    <div class="dashboard-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div class="header-title-block">
            <h1 style="color: var(--text-main); font-weight: 700; font-size: 1.75rem;">Chỉnh sửa Voucher</h1>
            <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.95rem;">Cập nhật thông tin mã giảm giá <strong>{{ $voucher->code }}</strong>.</p>
        </div>
        <div class="header-actions" style="display: flex; gap: 12px;">
            <a href="{{ route('admin.vouchers') }}" class="btn-cancel">Hủy</a>
            <button type="submit" class="btn-save">Cập nhật voucher</button>
        </div>
    </div>

    <!-- Responsive Columns -->
    <div class="voucher-create-grid">
        <!-- Left Main Form Column -->
        <div class="voucher-main-col">
            
            <!-- General Information Card -->
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Thông tin chung</span>
                </div>

                <div class="form-group">
                    <label for="code">Mã Voucher <span class="required" style="color: #d93025;">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $voucher->code) }}" required placeholder="Ví dụ: PETWELCOME, FREESHIP99" style="text-transform: uppercase;">
                    @error('code')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">Viết liền, không dấu, không chứa ký tự đặc biệt. Tự động chuyển thành chữ in hoa.</p>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="description">Mô tả hiển thị</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Nhập mô tả hiển thị cho khách hàng (ví dụ: Giảm 50k cho đơn hàng từ 300k)...">{{ old('description', $voucher->description) }}</textarea>
                    @error('description')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Value and Limits Card -->
            <div class="form-card">
                <div class="form-card-title">
                    <i class="fa-solid fa-calculator"></i>
                    <span>Cấu hình giá trị & Giới hạn</span>
                </div>

                <div class="form-group-row">
                    <div class="form-group">
                        <label for="discount_value">Số tiền giảm (đ) <span class="required" style="color: #d93025;">*</span></label>
                        <input type="number" class="form-control @error('discount_value') is-invalid @enderror" id="discount_value" name="discount_value" value="{{ old('discount_value', (int) $voucher->discount_value) }}" min="0" required placeholder="Ví dụ: 50000">
                        @error('discount_value')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="min_order_value">Đơn hàng tối thiểu (đ) <span class="required" style="color: #d93025;">*</span></label>
                        <input type="number" class="form-control @error('min_order_value') is-invalid @enderror" id="min_order_value" name="min_order_value" value="{{ old('min_order_value', (int) $voucher->min_order_value) }}" min="0" required placeholder="Ví dụ: 300000">
                        @error('min_order_value')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="usage_limit">Số lượt sử dụng tối đa <span class="required" style="color: #d93025;">*</span></label>
                    <input type="number" class="form-control @error('usage_limit') is-invalid @enderror" id="usage_limit" name="usage_limit" value="{{ old('usage_limit', $voucher->usage_limit) }}" min="0" required placeholder="Ví dụ: 100">
                    @error('usage_limit')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">Nhập <strong>0</strong> nếu không giới hạn tổng số lượt sử dụng voucher của hệ thống.</p>
                </div>
            </div>

            <!-- Validation Dates Card -->
            <div class="form-card" style="margin-bottom: 0;">
                <div class="form-card-title">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Thời gian hiệu lực</span>
                </div>

                <div class="form-group-row" style="margin-bottom: 0;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="start_date">Ngày bắt đầu <span class="required" style="color: #d93025;">*</span></label>
                        <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $voucher->start_date ? $voucher->start_date->format('Y-m-d\TH:i') : '') }}" required>
                        @error('start_date')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="end_date">Ngày kết thúc <span class="required" style="color: #d93025;">*</span></label>
                        <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $voucher->end_date ? $voucher->end_date->format('Y-m-d\TH:i') : '') }}" required>
                        @error('end_date')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Sidebar Form Column -->
        <div class="voucher-sidebar-col">
            <!-- Status Card -->
            <div class="form-card" style="padding: 24px;">
                <div class="form-card-title" style="margin-bottom: 16px; padding-bottom: 8px;">
                    <i class="fa-solid fa-toggle-on"></i>
                    <span>Trạng thái</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <label class="custom-radio-container">
                        <input type="radio" name="status" value="active" {{ old('status', $voucher->status) === 'active' ? 'checked' : '' }}>
                        <div class="radio-label-details">
                            <span class="radio-label-title">Hoạt động (Active)</span>
                        </div>
                    </label>
                    <label class="custom-radio-container">
                        <input type="radio" name="status" value="inactive" {{ old('status', $voucher->status) === 'inactive' ? 'checked' : '' }}>
                        <div class="radio-label-details">
                            <span class="radio-label-title">Tạm ẩn (Inactive)</span>
                        </div>
                    </label>
                    <label class="custom-radio-container">
                        <input type="radio" name="status" value="expired" {{ old('status', $voucher->status) === 'expired' ? 'checked' : '' }}>
                        <div class="radio-label-details">
                            <span class="radio-label-title">Hết hạn (Expired)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Tips Card -->
            <div style="background-color: #fff9e6; border: 1px solid #ffeeba; border-radius: 12px; padding: 20px;">
                <h4 style="color: #856404; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-lightbulb"></i> Lưu ý thiết lập:
                </h4>
                <ul style="color: #856404; font-size: 0.85rem; padding-left: 20px; line-height: 1.5; margin: 0;">
                    <li>Mức giảm giá không nên lớn hơn giá trị tối thiểu của đơn hàng.</li>
                    <li>Đảm bảo ngày bắt đầu và kết thúc nằm trong chiến dịch.</li>
                    <li>Nếu để trạng thái hoạt động mà ngày kết thúc ở quá khứ, hệ thống sẽ tự hiểu là hết hạn.</li>
                </ul>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const codeInput = document.getElementById('code');

        if (codeInput) {
            codeInput.addEventListener('input', function() {
                // Tự động bỏ khoảng trắng và chuyển chữ hoa
                this.value = this.value.toUpperCase().replace(/\s+/g, '').replace(/[^A-Z0-9]/g, '');
            });
        }
    });
</script>
@endsection
