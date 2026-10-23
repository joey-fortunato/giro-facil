(() => {
    'use strict';

    const decodeUtf8Base64 = (value) => {
        try {
            const bytes = Uint8Array.from(atob(value), (character) => character.charCodeAt(0));
            return new TextDecoder().decode(bytes);
        } catch (error) {
            console.error('Não foi possível carregar o conteúdo do editor visual.', error);
            return '';
        }
    };

    const initializeVisualEditor = (frame) => {
        if (!frame || frame.dataset.initialized === 'true') return;
        frame.dataset.initialized = 'true';
        const content = decodeUtf8Base64(frame.dataset.content || '');
        const extraCss = frame.dataset.extraCss ? decodeUtf8Base64(frame.dataset.extraCss) : '';
        const safeCss = extraCss.replace(/<\/style/gi, '<\\/style');
        const base = `${window.location.origin}/`;
        const documentHtml = `<!doctype html>
<html lang="pt-PT"><head><meta charset="utf-8"><base href="${base}">
<link rel="stylesheet" href="/css/bootstrap.min.css">
<link rel="stylesheet" href="/css/plugins.css">
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/css/coloring.css">
<link rel="stylesheet" href="/css/colors/scheme-01.css">
<link rel="stylesheet" href="/css/giro-responsive.css">
<style>${safeCss}
html,body{min-height:100%;}body{padding:16px;outline:none;cursor:text}
body:before{position:fixed;right:10px;bottom:10px;z-index:9999;padding:6px 10px;border-radius:4px;background:#043081;color:#fff;content:'Pré-visualização editável';font:600 11px Arial,sans-serif;pointer-events:none}
h1:hover,h2:hover,h3:hover,h4:hover,p:hover,li:hover{outline:1px dashed rgba(210,156,99,.8);outline-offset:2px}
img{cursor:pointer}img:hover{outline:3px solid #d29c63;outline-offset:2px}
#como-funciona h1,#como-funciona h2,#como-funciona h3,#como-funciona h4,
.giro-why-section h1,.giro-why-section h2,.giro-why-section h3,.giro-why-section h4,
.giro-final-cta h1,.giro-final-cta h2,.giro-final-cta h3,.giro-final-cta h4,
#subheader.giro-about-hero h1,#subheader.giro-about-hero h2,#subheader.giro-booking-hero h1,#subheader.giro-booking-hero h2,
#subheader h1{color:#fff!important}
</style></head><body>${content}</body></html>`;
        frame.srcdoc = documentHtml;

        frame.addEventListener('load', () => {
            const editorDocument = frame.contentDocument;
            if (!editorDocument?.body) return;

            editorDocument.body.contentEditable = 'true';
            editorDocument.body.spellcheck = true;
            editorDocument.addEventListener('click', (event) => {
                if (event.target?.tagName !== 'IMG') return;
                event.preventDefault();
                window.parent.openMediaPicker?.(event.target);
            });
        });
    };

    const sectionEditors = document.querySelectorAll('[data-section-editor]');
    sectionEditors.forEach((details) => {
        details.addEventListener('toggle', () => {
            if (!details.open) return;
            sectionEditors.forEach((other) => {
                if (other !== details) other.open = false;
            });
            initializeVisualEditor(details.querySelector('[data-visual-editor]'));
        });
    });
    document.querySelectorAll('[data-visual-editor]').forEach((frame) => {
        const sectionEditor = frame.closest('[data-section-editor]');
        if (!sectionEditor || sectionEditor.open) initializeVisualEditor(frame);
    });

    document.querySelectorAll('[data-editor-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('[data-visual-editor]').forEach((frame) => {
                const field = form.querySelector(`[data-editor-source="${frame.dataset.editor}"]`);
                if (field && frame.contentDocument?.body) field.value = frame.contentDocument.body.innerHTML;
            });
        });
    });

    document.querySelectorAll('[data-editor-command]').forEach((button) => {
        button.addEventListener('click', () => {
            const frame = document.querySelector(`[data-editor="${button.dataset.editorCommand}"]`);
            if (!frame?.contentDocument) return;
            frame.contentWindow.focus();
            const value = button.dataset.command === 'createLink' ? window.prompt('Endereço do link:') : null;
            if (button.dataset.command !== 'createLink' || value) {
                frame.contentDocument.execCommand(button.dataset.command, false, value);
            }
        });
    });

    document.querySelectorAll('[data-reorder]').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('input[name="order[]"]').forEach((input) => input.remove());
            document.querySelectorAll('#section-list .section-card').forEach((card) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order[]';
                input.value = card.dataset.section;
                form.appendChild(input);
            });
        });
    });

    document.querySelectorAll('[data-move]').forEach((button) => {
        button.addEventListener('click', () => {
            const card = button.closest('.section-card');
            if (!card) return;
            if (button.dataset.move === 'up' && card.previousElementSibling) {
                card.parentNode.insertBefore(card, card.previousElementSibling);
            }
            if (button.dataset.move === 'down' && card.nextElementSibling) {
                card.parentNode.insertBefore(card.nextElementSibling, card);
            }
        });
    });

    const mediaPicker = document.querySelector('[data-media-picker]');
    let selectedImage = null;
    window.openMediaPicker = (image) => {
        selectedImage = image;
        mediaPicker?.showModal();
    };
    mediaPicker?.querySelectorAll('[data-media-url]').forEach((button) => {
        button.addEventListener('click', () => {
            if (selectedImage) {
                selectedImage.setAttribute('src', button.dataset.mediaUrl);
                selectedImage.removeAttribute('srcset');
            }
            mediaPicker.close();
            selectedImage = null;
        });
    });
    mediaPicker?.querySelector('[data-media-close]')?.addEventListener('click', () => mediaPicker.close());
    mediaPicker?.addEventListener('click', (event) => {
        if (event.target === mediaPicker) mediaPicker.close();
    });
})();
