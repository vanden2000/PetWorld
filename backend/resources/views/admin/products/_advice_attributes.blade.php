@php
    $advice = old('advice_attributes', $product->advice_attributes ?? []);
    $selected = fn($key) => old('advice_' . $key, $advice[$key] ?? []);
    $groups = ['life_stages' => ['Độ tuổi phù hợp', ['kitten' => ['Mèo con', 'fa-paw'], 'puppy' => ['Chó con', 'fa-paw'], 'adult' => ['Trưởng thành', 'fa-heart'], 'senior' => ['Lớn tuổi', 'fa-heart'], 'all_life_stages' => ['Mọi độ tuổi', 'fa-infinity']]], 'needs' => ['Nhu cầu được hỗ trợ', ['skin_coat' => ['Da & lông', 'fa-wand-magic-sparkles'], 'picky_eater' => ['Kén ăn', 'fa-utensils'], 'dental' => ['Răng miệng', 'fa-tooth'], 'weight_control' => ['Kiểm soát cân nặng', 'fa-scale-balanced'], 'indoor' => ['Nuôi trong nhà', 'fa-house'], 'daily_nutrition' => ['Dinh dưỡng hằng ngày', 'fa-seedling']]]];
@endphp
<section class="form-card chatbot-profile">
    <header>
        <div>
            <div class="form-card-title" style="margin:0;border:0;padding:0"><i
                    class="fa-solid fa-comment-dots"></i><span>Hồ sơ tư vấn chatbot</span></div>
            <p>Loài đã chọn trong phần Phân loại. Bổ sung độ tuổi và lợi ích được hãng xác nhận để chatbot tư vấn chính xác hơn.</p>
        </div><span data-status>Chưa bổ sung</span>
    </header>
    <div class="profile-grid">@foreach($groups as $key => [$title, $items])<fieldset>
        <legend>{{ $title }}</legend>
        <p>{{ $key === 'life_stages' ? 'Chọn giai đoạn ghi trên bao bì. “Mọi độ tuổi” sẽ thay thế các độ tuổi cụ thể.' : 'Chỉ chọn lợi ích được hãng xác nhận.' }}</p>
        <div class="profile-cards">@foreach($items as $value => [$label, $icon])<input id="advice-{{ $value }}"
            type="checkbox" name="advice_{{ $key }}[]" value="{{ $value }}"
            @checked(in_array($value, $selected($key), true))><label for="advice-{{ $value }}"><i
                class="fa-solid {{ $icon }}"></i><span><strong>{{ $label }}</strong><small>{{ $key === 'life_stages' ? 'Giai đoạn sử dụng' : 'Lợi ích sản phẩm' }}</small></span><b><i
        class="fa-solid fa-check"></i></b></label>@endforeach</div>
    </fieldset>@endforeach</div>
    <footer><i class="fa-solid fa-robot"></i>
        <div><strong>Chatbot sẽ nhận biết</strong>
            <p data-summary>Chưa có thông tin bổ sung.</p>
        </div>
    </footer>
</section>
<style>
    .chatbot-profile {
        border-color: #f0ddd1;
        background: #fffaf7
    }

    .chatbot-profile header {
        display: flex;
        justify-content: space-between;
        gap: 16px
    }

    .chatbot-profile header p,
    .chatbot-profile fieldset>p {
        margin: 5px 0 0;
        color: var(--theme-text-gray);
        font-size: .78rem;
        line-height: 1.45
    }

    .chatbot-profile header>span {
        height: max-content;
        padding: 6px 9px;
        border-radius: 999px;
        background: #fff0e6;
        color: #9a4b1b;
        font-size: .72rem;
        font-weight: 800
    }

    .chatbot-profile header>span.ready {
        background: #edf9f0;
        color: #237343
    }

    .profile-cards input:disabled+label {
        cursor: not-allowed;
        opacity: .45;
        filter: grayscale(1)
    }

    .chatbot-profile fieldset {
        min-width: 0;
        margin: 20px 0 0;
        padding: 0;
        border: 0
    }

    .chatbot-profile legend {
        font-size: .88rem;
        font-weight: 800;
        color: var(--theme-text-main)
    }

    .profile-grid {
        display: grid;
        gap: 10px
    }

    .profile-cards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 10px
    }

    .profile-cards.species {
        grid-template-columns: repeat(2, minmax(0, 220px))
    }

    .profile-cards input {
        position: absolute;
        opacity: 0
    }

    .profile-cards label {
        position: relative;
        display: flex;
        min-height: 59px;
        align-items: center;
        gap: 9px;
        padding: 9px;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        background: #fff;
        cursor: pointer
    }

    .profile-cards label>i {
        display: grid;
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        place-items: center;
        border-radius: 7px;
        background: #fff0e6;
        color: var(--theme-primary)
    }

    .profile-cards label span {
        display: grid;
        gap: 2px
    }

    .profile-cards strong {
        font-size: .76rem
    }

    .profile-cards small {
        color: var(--theme-text-gray);
        font-size: .66rem
    }

    .profile-cards b {
        position: absolute;
        right: 7px;
        top: 7px;
        display: grid;
        width: 16px;
        height: 16px;
        place-items: center;
        border: 1px solid #d6ddd9;
        border-radius: 50%;
        color: transparent;
        font-size: 8px
    }

    .profile-cards input:checked+label {
        border-color: var(--theme-primary);
        background: #fff5ee
    }

    .profile-cards input:checked+label b {
        border-color: var(--theme-primary);
        background: var(--theme-primary);
        color: #fff
    }

    .profile-cards input:focus-visible+label {
        outline: 3px solid rgba(255, 120, 45, .18)
    }

    .chatbot-profile footer {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding: 12px;
        border: 1px solid #f0ddd1;
        border-radius: 8px;
        background: #fff
    }

    .chatbot-profile footer>i {
        color: var(--theme-primary)
    }

    .chatbot-profile footer strong {
        font-size: .8rem
    }

    .chatbot-profile footer p {
        margin: 3px 0 0;
        color: var(--theme-text-gray);
        font-size: .78rem
    }

    @media(max-width:720px) {
        .profile-grid {
            grid-template-columns: 1fr
        }

        .chatbot-profile header {
            flex-direction: column
        }
    }

    @media(max-width:420px) {

        .profile-cards,
        .profile-cards.species {
            grid-template-columns: 1fr
        }
    }
</style>
<script>
(() => {
  const profile = document.querySelector('.chatbot-profile');
  if (!profile || profile.dataset.ageRuleReady) return;
  profile.dataset.ageRuleReady = '1';
  const ages = [...profile.querySelectorAll('input[name="advice_life_stages[]"]')];
  const allAges = ages.find((input) => input.value === 'all_life_stages');
  const kitten = ages.find((input) => input.value === 'kitten');
  const puppy = ages.find((input) => input.value === 'puppy');
  const species = [...document.querySelectorAll('input[name="pet_species_ids[]"]')];
  const hasSpecies = (slug) => species.some((input) => input.checked && document.querySelector(`label[for="${input.id}"]`)?.dataset.speciesSlug === slug);
  const setAgeAvailability = (input, disabled, note) => {
    if (!input) return false;
    const label = document.querySelector(`label[for="${input.id}"]`);
    input.disabled = disabled;
    label?.setAttribute('title', disabled ? note : '');
    if (disabled && input.checked) {
      input.checked = false;
      return true;
    }
    return false;
  };
  const syncSpeciesAndAges = () => {
    const catOnly = hasSpecies('cat') && !hasSpecies('dog');
    const dogOnly = hasSpecies('dog') && !hasSpecies('cat');
    const changed = setAgeAvailability(kitten, dogOnly, 'Mèo con chỉ dùng khi sản phẩm phù hợp với mèo')
      || setAgeAvailability(puppy, catOnly, 'Chó con chỉ dùng khi sản phẩm phù hợp với chó');
    if (changed) profile.dispatchEvent(new Event('change', { bubbles: true }));
  };
  profile.addEventListener('change', (event) => {
    if (event.target.name !== 'advice_life_stages[]') return;
    if (event.target === allAges && allAges.checked) ages.filter((input) => input !== allAges).forEach((input) => { input.checked = false; });
    if (event.target !== allAges && event.target.checked) allAges.checked = false;
  });
  species.forEach((input) => input.addEventListener('change', syncSpeciesAndAges));
  syncSpeciesAndAges();
})();
</script>
<script>(() => { const r = document.querySelector('.chatbot-profile'); if (!r || r.dataset.ready) return; r.dataset.ready = 1; const n = { kitten: 'mèo con', puppy: 'chó con', adult: 'trưởng thành', senior: 'lớn tuổi', all_life_stages: 'mọi độ tuổi', skin_coat: 'da & lông', picky_eater: 'kén ăn', dental: 'răng miệng', weight_control: 'kiểm soát cân nặng', indoor: 'nuôi trong nhà', daily_nutrition: 'dinh dưỡng hằng ngày' }; const u = () => { const v = [...r.querySelectorAll('input:checked')].map(x => n[x.value] || x.closest('label')?.innerText).filter(Boolean), s = r.querySelector('[data-summary]'), b = r.querySelector('[data-status]'); s.textContent = v.length ? `Phù hợp: ${v.join(', ')}.` : 'Chưa có thông tin bổ sung.'; b.textContent = v.length ? 'Đã bổ sung' : 'Chưa bổ sung'; b.classList.toggle('ready', v.length > 0) }; r.addEventListener('change', u); u() })();</script>
