<div id="product-exit-modal" class="product-save-modal" hidden role="dialog" aria-modal="true" aria-labelledby="product-exit-modal-title" data-is-create="{{ $isCreate ? '1' : '0' }}">
    <div class="product-save-modal-card product-exit-modal-card">
        <div class="product-save-modal-icon"><i class="fa-solid fa-pen-to-square"></i></div>
        <h3 id="product-exit-modal-title">Bạn có thay đổi chưa lưu</h3>
        <p>Sản phẩm đang được chỉnh sửa nhưng chưa lưu. Bạn muốn tiếp tục, lưu thay đổi rồi rời đi, hay rời mà không lưu?</p>
        <div class="product-save-modal-actions product-exit-modal-actions">
            <button type="button" id="btn-stay-product-page" class="btn-action-cancel">Ở lại</button>
            <button type="button" id="btn-save-product-and-leave" class="btn-action-save">Lưu &amp; rời đi</button>
            <button type="button" id="btn-discard-product-and-leave" class="product-exit-discard">Rời không lưu</button>
        </div>
    </div>
</div>

<style>
    .product-exit-modal-actions { flex-wrap: wrap; }
    .product-exit-discard { width: 100%; border: 0; background: transparent; color: #a12626; cursor: pointer; font: inherit; font-size: .8rem; font-weight: 800; padding: 5px; }
    .product-exit-discard:hover { text-decoration: underline; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('product-edit-form');
        const modal = document.getElementById('product-exit-modal');
        if (!form || !modal) return;

        const isCreate = modal.dataset.isCreate === '1';
        let baseline = '';
        let pendingUrl = null;

        const fingerprint = () => [...form.querySelectorAll('input, select, textarea')]
            .filter((field) => !['_token', '_method'].includes(field.name))
            .map((field) => {
                if (field.type === 'file') return `${field.name}:${[...field.files].map((file) => `${file.name}:${file.size}:${file.lastModified}`).join('|')}`;
                if (field.type === 'checkbox' || field.type === 'radio') return `${field.name}:${field.value}:${field.checked ? '1' : '0'}`;
                return `${field.name}:${field.value}`;
            })
            .join('\n');

        // Chờ các script khởi tạo biến thể/ảnh hoàn tất rồi mới lấy trạng thái ban đầu.
        window.setTimeout(() => { baseline = fingerprint(); }, 0);
        window.addEventListener('petworld:product-saved', () => { baseline = fingerprint(); });

        const hasChanges = () => baseline !== '' && fingerprint() !== baseline;
        const close = () => { modal.hidden = true; pendingUrl = null; };
        const leave = (discard = false) => {
            if (!pendingUrl) return;
            if (discard) {
                const nextUrl = pendingUrl;
                baseline = fingerprint();
                close();
                window.location.assign(nextUrl);
                return;
            }

            // Trang tạo sản phẩm đang dùng submit chuẩn; trang sửa lưu AJAX và sẽ chuyển trang sau khi thành công.
            if (isCreate) sessionStorage.removeItem('petworld.product.exit_after_save');
            else sessionStorage.setItem('petworld.product.exit_after_save', pendingUrl);
            close();
            form.requestSubmit();
            window.setTimeout(() => document.getElementById('btn-confirm-product-save')?.click(), 0);
        };

        document.addEventListener('click', (event) => {
            if (event.defaultPrevented || !modal.hidden || !hasChanges()) return;
            const link = event.target.closest('a[href]');
            if (!link || link.target || link.hasAttribute('download')) return;
            const next = new URL(link.href, window.location.href);
            const sameDocument = next.pathname === window.location.pathname && next.search === window.location.search && next.hash;
            if (next.origin !== window.location.origin || sameDocument) return;
            event.preventDefault();
            pendingUrl = next.href;
            modal.hidden = false;
            document.getElementById('btn-stay-product-page').focus();
        }, true);

        window.addEventListener('beforeunload', (event) => {
            if (!hasChanges()) return;
            event.preventDefault();
            event.returnValue = '';
        });

        document.getElementById('btn-stay-product-page').addEventListener('click', close);
        document.getElementById('btn-save-product-and-leave').addEventListener('click', () => leave(false));
        document.getElementById('btn-discard-product-and-leave').addEventListener('click', () => leave(true));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) close();
        });
    });
</script>
