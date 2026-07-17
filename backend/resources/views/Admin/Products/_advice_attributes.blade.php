@php
    $advice = old('advice_attributes', $product->advice_attributes ?? []);
    $selected = fn (string $key) => old('advice_'.$key, $advice[$key] ?? []);
    $groups = [
        'pet_types' => ['label' => 'Loài phù hợp', 'items' => ['cat' => 'Mèo', 'dog' => 'Chó']],
        'life_stages' => ['label' => 'Giai đoạn', 'items' => ['kitten' => 'Mèo con', 'puppy' => 'Chó con', 'adult' => 'Trưởng thành', 'senior' => 'Lớn tuổi', 'all_life_stages' => 'Mọi độ tuổi']],
        'product_types' => ['label' => 'Loại sản phẩm', 'items' => ['dry_food' => 'Hạt', 'wet_food' => 'Pate/ướt', 'treat' => 'Snack', 'toy' => 'Đồ chơi', 'litter' => 'Cát vệ sinh', 'accessory' => 'Phụ kiện']],
        'needs' => ['label' => 'Nhu cầu tư vấn', 'items' => ['daily_nutrition' => 'Dinh dưỡng hằng ngày', 'picky_eater' => 'Kén ăn', 'skin_coat' => 'Da & lông', 'weight_control' => 'Kiểm soát cân nặng', 'dental' => 'Răng miệng', 'indoor' => 'Nuôi trong nhà']],
    ];
@endphp

<div class="form-card">
    <div class="form-card-title">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
        <span>Thuộc tính tư vấn AI</span>
    </div>
    <p class="slug-field-note" style="margin-top:0">Chọn thông tin đã được xác nhận để chatbot gợi ý đúng sản phẩm. Có thể để trống với sản phẩm chưa phân loại.</p>
    @foreach($groups as $key => $group)
        <div class="form-control-group" style="margin-top: 14px;">
            <label class="form-field-label">{{ $group['label'] }}</label>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                @foreach($group['items'] as $value => $label)
                    <label style="display:inline-flex; gap:6px; align-items:center; font-size:13px; cursor:pointer;">
                        <input type="checkbox" name="advice_{{ $key }}[]" value="{{ $value }}" @checked(in_array($value, $selected($key), true))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @error('advice_'.$key) <span class="error-text">{{ $message }}</span> @enderror
        </div>
    @endforeach
</div>
