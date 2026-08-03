@php($speciesSelected = old('pet_species_ids', $product->petSpecies?->pluck('id')->all() ?? []))
<div class="form-control-group no-margin product-species-field"><label class="form-field-label">Loài thú cưng phù
        hợp</label>
    <p>Thông tin dùng cho bộ lọc cửa hàng và chatbot.</p>
    <div class="product-species-options">@forelse($petSpecies ?? [] as $species)<input
        id="classification-species-{{ $species->id }}" type="checkbox" name="pet_species_ids[]"
        value="{{ $species->id }}" @checked(in_array($species->id, $speciesSelected))><label
        data-species-slug="{{ $species->slug }}" for="classification-species-{{ $species->id }}"><i
            class="fa-solid fa-paw"></i><span>{{ $species->name }}</span><b><i
    class="fa-solid fa-check"></i></b></label>@empty<span class="slug-field-note">Chưa có loài. Hãy chạy
                    migration để tạo Mèo và Chó.</span>@endforelse</div>
</div>
<style>
    .product-species-field {
        margin-top: 16px !important;
    }

    .product-species-field>p {
        margin: 0;
        color: var(--theme-text-gray);
        font-size: .74rem
    }

    .product-species-options {
        display: flex;
        flex-wrap: wrap;
        gap: 8px
    }

    .product-species-options input {
        position: absolute;
        opacity: 0
    }

    .product-species-options label {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 9px 12px;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        font-size: .8rem;
        font-weight: 800
    }

    .product-species-options label>i {
        color: var(--theme-primary)
    }

    .product-species-options b {
        display: grid;
        width: 15px;
        height: 15px;
        place-items: center;
        border: 1px solid #d6ddd9;
        border-radius: 50%;
        color: transparent;
        font-size: 8px
    }

    .product-species-options input:checked+label {
        border-color: var(--theme-primary);
        background: #fff5ee
    }

    .product-species-options input:checked+label b {
        border-color: var(--theme-primary);
        background: var(--theme-primary);
        color: #fff
    }

    .product-species-options input:focus-visible+label {
        outline: 3px solid rgba(255, 120, 45, .18)
    }
</style>