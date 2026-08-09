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
        
        /* Custom Confirmation Modal Styles */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .custom-modal-overlay.show {
            opacity: 1;
        }
        .custom-modal-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            transform: scale(0.9);
            transition: transform 0.25s ease;
        }
        .custom-modal-overlay.show .custom-modal-card {
            transform: scale(1);
        }
        .custom-modal-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            font-size: 1.5rem;
        }
        .custom-modal-icon.info {
            background-color: #fff7ed;
            color: #ea580c;
            border: 1px solid #ffedd5;
        }
        .custom-modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .custom-modal-message {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .custom-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn-modal {
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            flex: 1;
        }
        .btn-modal-cancel {
            background-color: #f1f5f9;
            color: #475569;
        }
        .btn-modal-cancel:hover {
            background-color: #e2e8f0;
        }
        .btn-modal-confirm {
            background-color: var(--primary);
            color: #ffffff;
        }
        .btn-modal-confirm:hover {
            filter: brightness(0.9);
        }
    </style>
@endsection

@section('content')
<form action="{{ route('admin.vouchers.update', $voucher->id) }}" method="POST" id="voucherEditForm">
    @csrf
    @method('PUT')
    <div class="form-card" style="margin-bottom: 24px;">
        <div class="form-card-title"><i class="fa-solid fa-truck"></i><span>Loại ưu đãi</span></div>
        <div class="form-group-row" style="margin-bottom: 0;">
            <div class="form-group"><label for="applies_to">Áp dụng cho</label><select class="form-control" id="applies_to" name="applies_to"><option value="product" {{ old('applies_to', $voucher->applies_to ?? 'product') === 'product' ? 'selected' : '' }}>Giảm tiền sản phẩm</option><option value="shipping" {{ old('applies_to', $voucher->applies_to) === 'shipping' ? 'selected' : '' }}>Giảm phí vận chuyển</option></select></div>
            <div class="form-group shipping-only"><label for="shipping_method_code">Phương thức giao</label><select class="form-control" id="shipping_method_code" name="shipping_method_code"><option value="">Cả Standard và GHN</option><option value="standard" {{ old('shipping_method_code', $voucher->shipping_method_code) === 'standard' ? 'selected' : '' }}>Chỉ Standard</option><option value="ghn_express" {{ old('shipping_method_code', $voucher->shipping_method_code) === 'ghn_express' ? 'selected' : '' }}>Chỉ GHN nhanh</option></select></div>
            <div class="form-group shipping-only"><label><input type="checkbox" name="is_automatic" value="1" {{ old('is_automatic', $voucher->is_automatic) ? 'checked' : '' }}> Tự động áp dụng khi đủ điều kiện</label></div>
            <div class="form-group shipping-only"><label for="max_shipping_discount">Hỗ trợ ship tối đa (đ)</label><input class="form-control" type="number" min="0" name="max_shipping_discount" id="max_shipping_discount" value="{{ old('max_shipping_discount', $voucher->max_shipping_discount) }}"></div>
        </div>
    </div>
    
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
                        <input type="text" inputmode="numeric" data-currency class="form-control @error('discount_value') is-invalid @enderror" id="discount_value" name="discount_value" value="{{ old('discount_value', (int) $voucher->discount_value) }}" required placeholder="Ví dụ: 50.000">
                        @error('discount_value')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="min_order_value">Đơn hàng tối thiểu (đ) <span class="required" style="color: #d93025;">*</span></label>
                        <input type="text" inputmode="numeric" data-currency class="form-control @error('min_order_value') is-invalid @enderror" id="min_order_value" name="min_order_value" value="{{ old('min_order_value', (int) $voucher->min_order_value) }}" required placeholder="Ví dụ: 300.000">
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
                        <input type="hidden" id="start_date" name="start_date" value="{{ old('start_date', $voucher->start_date ? $voucher->start_date->format('Y-m-d H:i') : '') }}">
                        <input type="text" class="form-control @error('start_date') is-invalid @enderror" id="start_date_display" data-datetime-for="start_date" value="{{ old('start_date') ? \Carbon\Carbon::parse(old('start_date'))->format('d/m/Y H:i') : ($voucher->start_date ? $voucher->start_date->format('d/m/Y H:i') : '') }}" required placeholder="dd/mm/yyyy hh:mm" inputmode="numeric" autocomplete="off">
                        @error('start_date')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="end_date">Ngày kết thúc <span class="required" style="color: #d93025;">*</span></label>
                        <input type="hidden" id="end_date" name="end_date" value="{{ old('end_date', $voucher->end_date ? $voucher->end_date->format('Y-m-d H:i') : '') }}">
                        <input type="text" class="form-control @error('end_date') is-invalid @enderror" id="end_date_display" data-datetime-for="end_date" value="{{ old('end_date') ? \Carbon\Carbon::parse(old('end_date'))->format('d/m/Y H:i') : ($voucher->end_date ? $voucher->end_date->format('d/m/Y H:i') : '') }}" required placeholder="dd/mm/yyyy hh:mm" inputmode="numeric" autocomplete="off">
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
                        <span class="radio-indicator"></span>
                        <div class="radio-label-details">
                            <span class="radio-label-title">Hoạt động (Active)</span>
                        </div>
                    </label>
                    <label class="custom-radio-container">
                        <input type="radio" name="status" value="inactive" {{ old('status', $voucher->status) === 'inactive' ? 'checked' : '' }}>
                        <span class="radio-indicator"></span>
                        <div class="radio-label-details">
                            <span class="radio-label-title">Tạm ẩn (Inactive)</span>
                        </div>
                    </label>
                    <label class="custom-radio-container">
                        <input type="radio" name="status" value="expired" {{ old('status', $voucher->status) === 'expired' ? 'checked' : '' }}>
                        <span class="radio-indicator"></span>
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

<!-- Custom Confirmation Modal -->
<div id="confirmModal" class="custom-modal-overlay" style="display: none;">
    <div class="custom-modal-card">
        <div class="custom-modal-icon info">
            <i class="fa-solid fa-circle-question"></i>
        </div>
        <h3 class="custom-modal-title">Xác nhận cập nhật</h3>
        <p class="custom-modal-message">Bạn có chắc chắn muốn lưu lại các thay đổi của voucher này không?</p>
        <div class="custom-modal-actions">
            <button type="button" class="btn-modal btn-modal-cancel" id="btnCancelModal">Hủy bỏ</button>
            <button type="button" class="btn-modal btn-modal-confirm" id="btnConfirmModal">Xác nhận</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const codeInput = document.getElementById('code');
        const voucherForm = document.getElementById('voucherEditForm');
        const datetimeInputs = document.querySelectorAll('[data-datetime-for]');
        const syncDatetime = (input) => {
            const match = input.value.trim().match(/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})$/);
            const target = document.getElementById(input.dataset.datetimeFor);
            if (!match || !target) {
                input.setCustomValidity('Nhập ngày theo dạng dd/mm/yyyy HH:mm.');
                return false;
            }
            const [, day, month, year, hour, minute] = match;
            const date = new Date(`${year}-${month}-${day}T${hour}:${minute}:00`);
            if (Number.isNaN(date.getTime()) || date.getDate() !== Number(day) || date.getMonth() + 1 !== Number(month)) {
                input.setCustomValidity('Ngày hoặc giờ không hợp lệ.');
                return false;
            }
            target.value = `${year}-${month}-${day} ${hour}:${minute}`;
            input.setCustomValidity('');
            return true;
        };
        datetimeInputs.forEach((input) => input.addEventListener('input', () => syncDatetime(input)));
        const maxShippingDiscountInput = document.getElementById('max_shipping_discount');
        if (maxShippingDiscountInput) {
            maxShippingDiscountInput.type = 'text';
            maxShippingDiscountInput.inputMode = 'numeric';
            maxShippingDiscountInput.dataset.currency = '';
            if (/^\d+\.00$/.test(maxShippingDiscountInput.value)) {
                maxShippingDiscountInput.value = String(Number(maxShippingDiscountInput.value));
            }
        }
        const currencyInputs = document.querySelectorAll('[data-currency]');
        const formatCurrency = (value) => {
            const digits = String(value || '').replace(/\D/g, '');
            return digits ? new Intl.NumberFormat('vi-VN').format(Number(digits)) : '';
        };
        currencyInputs.forEach((input) => {
            input.value = formatCurrency(input.value);
            input.addEventListener('focus', () => {
                input.value = String(input.value || '').replace(/\D/g, '');
                input.select();
            });
            input.addEventListener('input', () => { input.value = String(input.value || '').replace(/\D/g, ''); });
            input.addEventListener('blur', () => { input.value = formatCurrency(input.value); });
        });
        voucherForm?.addEventListener('submit', (event) => {
            if (![...datetimeInputs].every(syncDatetime)) {
                event.preventDefault();
                voucherForm.reportValidity();
                return;
            }
            currencyInputs.forEach((input) => { input.value = String(input.value || '').replace(/\D/g, ''); });
        });
        const typeInput = document.getElementById('applies_to');
        const automaticInput = document.querySelector('[name="is_automatic"]');
        const automaticField = automaticInput?.closest('.shipping-only');
        const shippingFields = Array.from(document.querySelectorAll('.shipping-only')).filter((field) => field !== automaticField);
        const toggleShippingFields = () => {
            shippingFields.forEach((field) => field.style.display = typeInput.value === 'shipping' ? '' : 'none');
            automaticField?.style.setProperty('display', '', 'important');
        };
        if (typeInput) {
            typeInput.addEventListener('change', toggleShippingFields);
            toggleShippingFields();
        }
        const editForm = document.getElementById('voucherEditForm');
        const confirmModal = document.getElementById('confirmModal');
        const btnCancelModal = document.getElementById('btnCancelModal');
        const btnConfirmModal = document.getElementById('btnConfirmModal');
        let isConfirmed = false;

        if (codeInput) {
            codeInput.addEventListener('input', function() {
                // Tự động bỏ khoảng trắng và chuyển chữ hoa
                this.value = this.value.toUpperCase().replace(/\s+/g, '').replace(/[^A-Z0-9]/g, '');
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                if (!isConfirmed) {
                    e.preventDefault();
                    confirmModal.style.display = 'flex';
                    setTimeout(() => {
                        confirmModal.classList.add('show');
                    }, 10);
                }
            });
        }

        btnCancelModal.addEventListener('click', function() {
            confirmModal.classList.remove('show');
            setTimeout(() => {
                confirmModal.style.display = 'none';
            }, 250);
        });

        btnConfirmModal.addEventListener('click', function() {
            isConfirmed = true;
            confirmModal.classList.remove('show');
            confirmModal.style.display = 'none';
            if (editForm) {
                editForm.requestSubmit();
                isConfirmed = false;
            }
        });

        confirmModal.addEventListener('click', function(e) {
            if (e.target === confirmModal) {
                btnCancelModal.click();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('voucherEditForm');
        if (!form) return;
        const showToast = (message, isError = false) => {
            document.getElementById('admin-global-toast')?.remove();
            const toast = document.createElement('div');
            toast.id = 'admin-global-toast';
            toast.className = `admin-global-toast ${isError ? 'error' : 'success'}`;
            toast.innerHTML = `<i class="fa-solid ${isError ? 'fa-triangle-exclamation' : 'fa-circle-check'}"></i><span></span><button type="button" aria-label="Đóng">&times;</button>`;
            toast.querySelector('span').textContent = message;
            toast.querySelector('button').addEventListener('click', () => toast.remove());
            document.body.appendChild(toast);
            window.setTimeout(() => toast.classList.add('is-hidden'), 3500);
        };
        form.addEventListener('submit', async (event) => {
            if (event.defaultPrevented) return;
            event.preventDefault();
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton?.setAttribute('disabled', 'disabled');
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form),
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const firstError = Object.values(payload.errors || {}).flat()[0];
                    showToast(firstError || payload.message || 'Vui lòng kiểm tra lại thông tin voucher.', true);
                    return;
                }
                showToast(payload.message || 'Voucher đã được cập nhật thành công.');
            } catch {
                showToast('Không thể kết nối máy chủ. Vui lòng thử lại.', true);
            } finally {
                submitButton?.removeAttribute('disabled');
            }
        });
    });
</script>
@endsection
