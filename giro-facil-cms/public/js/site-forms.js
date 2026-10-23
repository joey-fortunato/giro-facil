(() => {
    const form = document.getElementById('giro-booking-form');
    if (!form) return;

    form.method = 'post';
    form.action = '/agendamentos';
    form.dataset.endpoint = '/agendamentos';

    document.addEventListener('submit', async (event) => {
        if (event.target !== form) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        const submit = form.querySelector('[type="submit"]');
        const feedback = form.querySelector('.giro-booking-consent');
        const originalLabel = submit?.querySelector('span')?.textContent || submit?.textContent || '';
        if (submit) {
            submit.disabled = true;
            if (submit.querySelector('span')) submit.querySelector('span').textContent = 'A enviar pedido…';
            else submit.textContent = 'A enviar pedido…';
        }

        try {
            const response = await fetch(form.dataset.endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: new FormData(form),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Não foi possível enviar o pedido. Verifique os dados e tente novamente.');

            if (feedback) {
                feedback.textContent = result.message;
                feedback.setAttribute('role', 'status');
                feedback.style.color = '#17633a';
            }
            form.reset();
        } catch (error) {
            if (feedback) {
                feedback.textContent = error.message;
                feedback.setAttribute('role', 'alert');
                feedback.style.color = '#a11d2a';
            }
        } finally {
            if (submit) {
                submit.disabled = false;
                if (submit.querySelector('span')) submit.querySelector('span').textContent = originalLabel;
                else submit.textContent = originalLabel;
            }
        }
    }, true);
})();
