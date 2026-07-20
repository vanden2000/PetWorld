import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import '../../css/admin/product-description-editor.css';

document.addEventListener('DOMContentLoaded', () => {
    const editorElement = document.getElementById('description-editor');
    const descriptionInput = document.getElementById('description');

    if (!editorElement || !descriptionInput) return;

    const quill = new Quill(editorElement, {
        theme: 'snow',
        placeholder: descriptionInput.dataset.placeholder || 'Nhập mô tả sản phẩm...',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline', 'blockquote'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link'],
                ['clean'],
            ],
        },
    });

    if (descriptionInput.value.trim() !== '') {
        quill.clipboard.dangerouslyPasteHTML(descriptionInput.value);
    }

    const syncDescription = () => {
        const isEmpty = quill.getText().trim() === '';
        descriptionInput.value = isEmpty ? '' : quill.getSemanticHTML();
        descriptionInput.dispatchEvent(new Event('input', { bubbles: true }));
        window.dispatchEvent(new Event('petworld:seo-update'));
    };

    quill.on('text-change', syncDescription);
    descriptionInput.form?.addEventListener('submit', syncDescription);
});
