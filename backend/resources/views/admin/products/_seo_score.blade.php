<div class="form-control-group">
    <label for="focus_keyword" class="form-field-label">Từ khóa chính</label>
    <input id="focus_keyword" name="focus_keyword" class="input-text-field"
           value="{{ old('focus_keyword', $product->focus_keyword) }}"
           placeholder="Ví dụ: hạt cho mèo con">
</div>

<section class="seo-score" aria-live="polite">
    <div class="seo-score-head">
        <strong>Điểm SEO</strong>
        <b data-seo-score>0</b>
        <span data-seo-label>Cần bổ sung</span>
    </div>
    <ul data-seo-checklist></ul>
    <div class="seo-image-alt-status" data-seo-image-alts></div>
</section>

<style>
    .seo-score { margin-top: 16px; padding: 14px; border: 1px solid var(--theme-border); border-radius: 8px; background: #fff; }
    .seo-score-head { display: flex; align-items: center; gap: 9px; }
    .seo-score-head strong { color: var(--theme-text-main); font-size: .88rem; }
    .seo-score-head b { display: grid; width: 34px; height: 34px; place-items: center; border-radius: 50%; background: #fff0e6; color: var(--theme-primary); font-size: .8rem; }
    .seo-score-head span { color: var(--theme-text-gray); font-size: .75rem; font-weight: 700; }
    .seo-score ul { display: grid; gap: 6px; margin: 12px 0 0; padding: 0; list-style: none; }
    .seo-score li { font-size: .75rem; color: var(--theme-text-gray); }
    .seo-score li.ok { color: #237343; }
    .seo-score li::before { content: '!'; display: inline-grid; width: 15px; height: 15px; place-items: center; margin-right: 6px; border-radius: 50%; background: #fff0e6; color: #a45b14; font-size: 10px; font-weight: 800; }
    .seo-score li.ok::before { content: '✓'; background: #edf9f0; color: #237343; }
    .seo-image-alt-status { margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--theme-border); }
    .seo-image-alt-status strong { display: block; margin-bottom: 7px; color: var(--theme-text-main); font-size: .78rem; }
    .seo-image-alt-status p { margin: 0; color: var(--theme-text-gray); font-size: .75rem; }
    .seo-image-alt-status ul { margin-top: 8px; }
    .seo-image-alt-status li { display: grid; grid-template-columns: 78px 1fr; gap: 8px; align-items: start; }
    .seo-image-alt-status li::before { display: none; }
    .seo-image-alt-status li span { color: #237343; font-weight: 700; }
    .seo-image-alt-status li.missing span { color: #b45309; }
    .seo-image-alt-status li em { color: var(--theme-text-gray); font-style: normal; word-break: break-word; }
</style>

<script>
    (() => {
        const initialiseSeoAssistant = () => {
            const box = document.querySelector('.seo-score');
            if (!box || box.dataset.ready) return;
            box.dataset.ready = '1';

            const field = (id) => document.getElementById(id);
            const text = (id) => field(id)?.value?.trim() || '';
            const normalise = (value) => value.toLocaleLowerCase('vi').trim();
            const plainText = (value) => (value || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            const clip = (value, length) => value.length > length ? `${value.slice(0, length - 1).trimEnd()}…` : value;
            const countPhrase = (content, phrase) => {
                if (!content || !phrase) return 0;
                let count = 0;
                let position = 0;
                while ((position = content.indexOf(phrase, position)) !== -1) {
                    count += 1;
                    position += phrase.length;
                }
                return count;
            };
            const escapeHtml = (value) => String(value).replace(/[&<>'"]/g, (character) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
            })[character]);

            const update = () => {
                const keyword = normalise(text('focus_keyword'));
                const productName = text('name');
                const seoTitle = text('seo_title');
                const seoDescription = text('seo_description');
                const shortDescription = text('short_description');
                const editorText = document.querySelector('#description-editor .ql-editor')?.innerText || '';
                const editorDescription = plainText(editorText) || plainText(field('description')?.value);
                const slug = text('slug');
                const title = seoTitle || productName;
                const description = seoDescription || shortDescription || editorDescription;
                const slugText = normalise(slug.replace(/-/g, ' '));
                const content = normalise(editorDescription);
                const contentLength = editorDescription.length;
                const contentWordCount = content ? content.split(/\s+/).length : 0;
                const keywordOccurrences = countPhrase(content, keyword);
                const recommendedMinimum = contentWordCount ? Math.max(1, Math.ceil(contentWordCount / 250)) : 1;
                const recommendedMaximum = contentWordCount ? Math.max(3, Math.floor(contentWordCount / 50)) : 3;
                const keywordDensity = contentWordCount ? ((keywordOccurrences / contentWordCount) * 100).toFixed(1) : '0.0';
                const hasNaturalKeywordUse = keywordOccurrences >= recommendedMinimum && keywordOccurrences <= recommendedMaximum;
                const imageBoxes = [...document.querySelectorAll('#upload-thumbnails-preview .thumbnail-img-box')]
                    .filter((image) => !image.classList.contains('pending-delete'));
                const hasImage = imageBoxes.length > 0;
                const imagesHaveAlt = hasImage && imageBoxes.every((image) => (image.dataset.altText || '').trim().length > 2);
                const keywordNearTitleStart = !!keyword && normalise(title).indexOf(keyword) >= 0
                    && normalise(title).indexOf(keyword) <= Math.max(12, Math.floor(title.length * 0.3));

                const previewTitle = field('seo-preview-title');
                const previewUrl = field('seo-preview-url');
                const previewDescription = field('seo-preview-description');
                if (previewTitle) previewTitle.textContent = clip(title || 'Tên sản phẩm', 60);
                if (previewUrl) previewUrl.textContent = `PetWorld › shop › ${slug || 'ten-san-pham'}`;
                if (previewDescription) previewDescription.textContent = clip(description || 'Mô tả sản phẩm sẽ hiển thị tại đây.', 160);

                const titleCount = field('seo-title-count');
                const descriptionCount = field('seo-description-count');
                if (titleCount) titleCount.textContent = `${seoTitle.length}/60`;
                if (descriptionCount) descriptionCount.textContent = `${seoDescription.length}/160`;

                const checks = [
                    ['Có từ khóa chính', keyword.length > 1],
                    ['Từ khóa có trong tiêu đề', !!keyword && normalise(title).includes(keyword)],
                    ['Từ khóa nằm gần đầu tiêu đề', keywordNearTitleStart],
                    ['Từ khóa có trong mô tả', !!keyword && normalise(description).includes(keyword)],
                    ['Từ khóa có trong URL', !!keyword && slugText.includes(keyword)],
                    ['Tiêu đề dài 30–60 ký tự', title.length >= 30 && title.length <= 60],
                    ['Mô tả dài 120–160 ký tự', description.length >= 120 && description.length <= 160],
                    ['Slug ngắn, dễ đọc', slug.length > 2 && slug.length <= 80],
                    [`Nội dung sản phẩm: ${contentLength} ký tự (từ 600 ký tự khuyến nghị)`, contentLength >= 600],
                    [`Từ khóa trong nội dung: ${keywordOccurrences} lần (${recommendedMinimum}–${recommendedMaximum} lần khuyến nghị · ${keywordDensity}%)`, !!keyword && hasNaturalKeywordUse],
                    ['Có ảnh sản phẩm', hasImage],
                    ['Ảnh có mô tả alt', imagesHaveAlt],
                    ['Trang sản phẩm có Product schema tự động', true],
                ];
                const score = Math.round((checks.filter(([, passed]) => passed).length / checks.length) * 100);

                box.querySelector('[data-seo-score]').textContent = score;
                box.querySelector('[data-seo-label]').textContent = score >= 80 ? 'Tốt' : score >= 50 ? 'Đạt cơ bản' : 'Cần bổ sung';
                box.querySelector('[data-seo-checklist]').innerHTML = checks
                    .map(([label, passed]) => `<li class="${passed ? 'ok' : ''}">${label}</li>`)
                    .join('');

                const imageAltPanel = box.querySelector('[data-seo-image-alts]');
                if (imageAltPanel) {
                    imageAltPanel.innerHTML = hasImage
                        ? `<strong>Kiểm tra mô tả alt ảnh</strong><ul>${imageBoxes.map((image, index) => {
                            const alt = (image.dataset.altText || '').trim();
                            const primary = image.classList.contains('is-primary') ? ' · ảnh chính' : '';
                            return `<li class="${alt.length > 2 ? '' : 'missing'}"><span>Ảnh ${index + 1}${primary}</span><em>${alt.length > 2 ? escapeHtml(alt) : 'Chưa có mô tả alt'}</em></li>`;
                        }).join('')}</ul>`
                        : '<strong>Kiểm tra mô tả alt ảnh</strong><p>Chưa có ảnh sản phẩm để kiểm tra.</p>';
                }
            };

            ['focus_keyword', 'seo_title', 'seo_description', 'short_description', 'name', 'slug', 'description']
                .forEach((id) => {
                    const input = field(id);
                    input?.addEventListener('input', update);
                    input?.addEventListener('change', update);
                });

            document.addEventListener('input', (event) => {
                if (event.target.matches('#image-alt-input, [name^="image_alt_texts"], [name^="new_image_alt_texts"]')) update();
            });
            document.addEventListener('change', (event) => {
                if (event.target.matches('#product-images, #primary-image-id, #primary-image-new-index')) {
                    window.setTimeout(update, 0);
                }
            });

            const editor = document.querySelector('#description-editor .ql-editor');
            editor?.addEventListener('input', update);
            editor?.addEventListener('keyup', update);
            if (editor) {
                new MutationObserver(update).observe(editor, {
                    childList: true,
                    characterData: true,
                    subtree: true,
                });
            }

            const thumbnailList = document.getElementById('upload-thumbnails-preview');
            if (thumbnailList) {
                new MutationObserver(update).observe(thumbnailList, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class', 'data-alt-text'],
                });
            }

            window.addEventListener('petworld:seo-update', update);
            update();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initialiseSeoAssistant);
        } else {
            initialiseSeoAssistant();
        }
    })();
</script>
