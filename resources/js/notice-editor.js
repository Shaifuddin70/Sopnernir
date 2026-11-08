import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const READY = 'quillReady';

function syncHiddenInput(quill, input) {
    const text = quill.getText().replace(/\n$/, '').trim();
    input.value = text === '' ? '' : quill.root.innerHTML;
}

function initNoticeEditors(root = document) {
    root.querySelectorAll('[data-quill-editor]').forEach((el) => {
        if (!(el instanceof HTMLElement) || el.dataset[READY] === '1') {
            return;
        }

        const inputSelector = el.dataset.input || '#body';
        const input = document.querySelector(inputSelector);
        if (!(input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement)) {
            return;
        }

        el.dataset[READY] = '1';

        const quill = new Quill(el, {
            theme: 'snow',
            placeholder: el.dataset.placeholder || '',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote', 'link'],
                    ['clean'],
                ],
            },
        });

        const initial = input.value.trim();
        if (initial !== '') {
            quill.clipboard.dangerouslyPasteHTML(initial);
        }

        quill.on('text-change', () => syncHiddenInput(quill, input));
        syncHiddenInput(quill, input);

        const form = document.getElementById('notice-editor-form') || el.closest('form');
        if (form instanceof HTMLFormElement) {
            form.addEventListener('submit', (event) => {
                syncHiddenInput(quill, input);

                if (input.value.trim() === '') {
                    event.preventDefault();
                    el.classList.add('ring-2', 'ring-error');
                    input.setCustomValidity(input.dataset.requiredMessage || 'Required');
                    input.reportValidity?.();
                    return;
                }

                input.setCustomValidity('');
                el.classList.remove('ring-2', 'ring-error');
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initNoticeEditors(document);
});
