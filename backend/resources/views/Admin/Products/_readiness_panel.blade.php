<aside class="product-readiness" id="product-readiness" aria-live="polite">
    <button type="button" class="product-readiness-toggle" id="product-readiness-toggle" aria-expanded="false">
        <i class="fa-solid fa-clipboard-check"></i><span id="product-readiness-summary">Kiểm tra sản phẩm</span><i
            class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="product-readiness-panel" id="product-readiness-panel" hidden>
        <div class="product-readiness-head"><strong id="product-readiness-title">Hồ sơ sản phẩm</strong><span
                id="product-readiness-score">0/0</span></div>
        <p id="product-readiness-note"></p>
        <div class="product-readiness-section"><strong>Bắt buộc để lưu</strong>
            <ul id="product-readiness-required"></ul>
        </div>
        <div class="product-readiness-section"><strong>Nên bổ sung</strong>
            <ul id="product-readiness-recommended"></ul>
        </div>
        <div class="product-readiness-section" id="product-readiness-warning-section"><strong>Cần kiểm tra</strong>
            <ul id="product-readiness-warnings"></ul>
        </div>
    </div>
</aside>

<style>
    .product-readiness {
        position: fixed;
        top: 82px;
        right: 22px;
        z-index: 45;
        width: min(340px, calc(100vw - 28px));
        font-size: .8rem;
    }

    .product-readiness-toggle {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border: 1px solid #f0c7ad;
        border-radius: 10px;
        color: #9a3f0d;
        background: #fffaf6;
        box-shadow: 0 8px 22px rgba(151, 74, 23, .16);
        font: inherit;
        font-weight: 800;
        cursor: pointer;
    }

    .product-readiness-toggle i:last-child {
        margin-left: auto;
        transition: transform .16s ease;
    }

    .product-readiness.is-open .product-readiness-toggle {
        border-radius: 10px 10px 0 0;
    }

    .product-readiness.is-open .product-readiness-toggle i:last-child {
        transform: rotate(180deg);
    }

    .product-readiness-panel {
        max-height: min(68vh, 580px);
        overflow-y: auto;
        padding: 12px;
        border: 1px solid #f0c7ad;
        border-top: 0;
        border-radius: 0 0 10px 10px;
        background: #fff;
        box-shadow: 0 12px 26px rgba(151, 74, 23, .16);
    }

    .product-readiness-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        color: var(--theme-text-main);
    }

    .product-readiness-head span {
        padding: 4px 7px;
        border-radius: 999px;
        color: #a8440d;
        background: #fff0e5;
        font-size: .72rem;
        font-weight: 800;
    }

    .product-readiness-panel>p {
        margin: 6px 0 12px;
        color: var(--theme-text-gray);
        line-height: 1.45;
    }

    .product-readiness-section {
        margin-top: 12px;
    }

    .product-readiness-section>strong {
        color: var(--theme-text-main);
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .product-readiness-section ul {
        display: grid;
        gap: 5px;
        margin: 7px 0 0;
        padding: 0;
        list-style: none;
    }

    .product-readiness-section li {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 7px;
        border-radius: 6px;
        color: var(--theme-text-gray);
        background: #fafafa;
        cursor: pointer;
    }

    .product-readiness-section li i {
        width: 14px;
        text-align: center;
    }

    .product-readiness-section li.ok i {
        color: #16a34a;
    }

    .product-readiness-section li.missing {
        color: #b91c1c;
        background: #fef2f2;
    }

    .product-readiness-section li.missing i {
        color: #dc2626;
    }

    .product-readiness-section li.warning {
        color: #a8440d;
        background: #fff7ed;
    }

    .product-readiness-section li.warning i {
        color: #ea580c;
    }

    .product-readiness-section[hidden] {
        display: none;
    }

    @media (max-width: 900px) {
        .product-readiness {
            top: 72px;
            right: 12px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('product-readiness');
        const form = document.getElementById('product-edit-form');
        if (!root || !form) return;
        const toggle = document.getElementById('product-readiness-toggle');
        const panel = document.getElementById('product-readiness-panel');
        const summary = document.getElementById('product-readiness-summary');
        const score = document.getElementById('product-readiness-score');
        const note = document.getElementById('product-readiness-note');
        const requiredList = document.getElementById('product-readiness-required');
        const recommendedList = document.getElementById('product-readiness-recommended');
        const warningList = document.getElementById('product-readiness-warnings');
        const warningSection = document.getElementById('product-readiness-warning-section');
        const field = (id) => document.getElementById(id);
        const hasText = (id) => Boolean(field(id)?.value?.trim());
        const variants = () => [...document.querySelectorAll('#variants-card-list article[data-index]')];
        const validVariant = (row) => {
            const sku = row.querySelector('.js-variant-sku')?.value?.trim();
            const price = Number(row.querySelector('.js-variant-price')?.value);
            const quantity = Number(row.querySelector('.js-variant-quantity')?.value);
            const weight = Number(row.querySelector('.js-variant-weight')?.value);
            const visible = row.querySelector('.js-variant-visible')?.checked;
            return Boolean(sku) && Number.isFinite(price) && price >= 0 && Number.isFinite(quantity) && quantity >= 0
                && (!visible || (Number.isFinite(weight) && weight > 0));
        };
        const addItem = (list, item, kind) => {
            const row = document.createElement('li'); row.className = kind;
            row.innerHTML = `<i class="fa-solid ${kind === 'ok' ? 'fa-circle-check' : kind === 'missing' ? 'fa-circle-xmark' : 'fa-triangle-exclamation'}"></i><span>${item.label}</span>`;
            if (item.target) row.addEventListener('click', () => { const target = document.querySelector(item.target); target?.scrollIntoView({ behavior: 'smooth', block: 'center' }); target?.focus?.({ preventScroll: true }); });
            list.appendChild(row);
        };
        const update = () => {
            const required = [
                { label: 'Tên sản phẩm', target: '#name', ok: hasText('name') }, { label: 'Slug', target: '#slug', ok: hasText('slug') },
                { label: 'Danh mục', target: '#category_id', ok: Boolean(field('category_id')?.value) }, { label: 'Thương hiệu', target: '#brand_id', ok: Boolean(field('brand_id')?.value) },
                { label: 'Ít nhất một ảnh sản phẩm', target: '#product-image-dropzone', ok: document.querySelectorAll('.thumbnail-img-box:not(.pending-delete)').length > 0 },
                { label: 'Biến thể hiển thị có SKU, giá, tồn kho và cân nặng đóng gói', target: '#variants-card-list', ok: variants().some(validVariant) },
            ];
            const recommended = [
                { label: 'Mô tả ngắn', target: '#short_description', ok: hasText('short_description') }, { label: 'Mô tả chi tiết', target: '#description-editor', ok: hasText('description') },
                { label: 'Focus keyword', target: '#focus_keyword', ok: hasText('focus_keyword') }, { label: 'SEO title', target: '#seo_title', ok: hasText('seo_title') },
                { label: 'Mô tả SEO', target: '#seo_description', ok: hasText('seo_description') }, { label: 'Loài thú cưng phù hợp', target: '.product-species-field', ok: Boolean(form.querySelector('input[name="pet_species_ids[]"]:checked')) },
                { label: 'Hồ sơ tư vấn chatbot', target: '.chatbot-profile', ok: Boolean(form.querySelector('input[name="advice_life_stages[]"]:checked, input[name="advice_needs[]"]:checked')) },
                { label: 'Alt text cho tất cả ảnh', target: '#image-alt-text', ok: [...document.querySelectorAll('.thumbnail-img-box:not(.pending-delete)')].every((box) => Boolean(box.dataset.altText?.trim())) },
            ];
            const activeVariants = variants().filter((row) => row.querySelector('.js-variant-visible')?.checked);
            const warnings = [
                { label: 'Có biến thể hết hàng', target: '#variants-card-list', show: activeVariants.some((row) => Number(row.querySelector('.js-variant-quantity')?.value) === 0) },
                { label: 'Có biến thể sắp hết hàng', target: '#variants-card-list', show: activeVariants.some((row) => { const qty = Number(row.querySelector('.js-variant-quantity')?.value); return qty > 0 && qty < 10; }) },
                { label: 'Cân nặng ship chưa nhập cho biến thể', target: '#variants-card-list', show: activeVariants.some((row) => !row.querySelector('.js-variant-weight')?.value) },
            ].filter((item) => item.show);
            requiredList.innerHTML = ''; recommendedList.innerHTML = ''; warningList.innerHTML = '';
            required.forEach((item) => addItem(requiredList, item, item.ok ? 'ok' : 'missing'));
            recommended.forEach((item) => addItem(recommendedList, item, item.ok ? 'ok' : 'warning'));
            warnings.forEach((item) => addItem(warningList, item, 'warning'));
            warningSection.hidden = warnings.length === 0;
            const completed = [...required, ...recommended].filter((item) => item.ok).length;
            const total = required.length + recommended.length;
            const missingRequired = required.filter((item) => !item.ok).length;
            score.textContent = `${completed}/${total}`;
            summary.textContent = missingRequired ? `${missingRequired} mục bắt buộc còn thiếu` : `Hồ sơ sản phẩm ${completed}/${total}`;
            note.textContent = missingRequired ? 'Hoàn tất các mục đỏ để có thể lưu sản phẩm.' : warnings.length ? 'Sản phẩm có thể lưu, nhưng còn mục cần kiểm tra.' : 'Sản phẩm đã sẵn sàng để lưu.';
        };
        toggle.addEventListener('click', () => { const open = panel.hidden; panel.hidden = !open; root.classList.toggle('is-open', open); toggle.setAttribute('aria-expanded', String(open)); });
        form.addEventListener('input', update); form.addEventListener('change', update);
        new MutationObserver(update).observe(form, { childList: true, subtree: true, attributes: true, attributeFilter: ['data-alt-text', 'class'] });
        update();
    });
</script>
