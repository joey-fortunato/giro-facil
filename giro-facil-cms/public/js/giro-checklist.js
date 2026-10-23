(function () {
    const triggers = document.querySelectorAll('[data-checklist-open]');
    let dialog = null;
    let form = document.getElementById('vehicle-checklist');

    if (!form && triggers.length) {
        document.body.insertAdjacentHTML('beforeend', `
            <dialog class="giro-checklist-dialog" id="checklist-dialog" aria-labelledby="checklist-dialog-title">
                <div class="giro-checklist-dialog-content">
                    <button class="giro-checklist-close" type="button" aria-label="Fechar checklist"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                    <div class="giro-checklist-modal-heading">
                        <div class="subtitle">Checklist rápida</div>
                        <h2 id="checklist-dialog-title">A sua viatura está pronta?</h2>
                        <p>Responda às cinco perguntas para ver o resultado.</p>
                    </div>
                    <form class="giro-checklist-form" id="vehicle-checklist">
                        <div class="giro-checklist-progress">
                            <div class="giro-checklist-progress-label"><span>Progresso</span><span id="checklist-progress-label">0 de 5 respondidas</span></div>
                            <div class="giro-checklist-progress-track" role="progressbar" aria-label="Progresso da checklist" aria-valuemin="0" aria-valuemax="5" aria-valuenow="0"><span class="giro-checklist-progress-fill"></span></div>
                        </div>
                        <div class="giro-checklist-questions">
                            <div class="giro-check-question" role="group" aria-labelledby="check-q-luzes"><p id="check-q-luzes">As luzes funcionam?</p><div class="giro-check-options"><label><input type="radio" name="luzes" value="sim"><span>Sim</span></label><label><input type="radio" name="luzes" value="nao"><span>Não</span></label></div></div>
                            <div class="giro-check-question" role="group" aria-labelledby="check-q-pneus"><p id="check-q-pneus">Os pneus estão em bom estado?</p><div class="giro-check-options"><label><input type="radio" name="pneus" value="sim"><span>Sim</span></label><label><input type="radio" name="pneus" value="nao"><span>Não</span></label></div></div>
                            <div class="giro-check-question" role="group" aria-labelledby="check-q-equipamento"><p id="check-q-equipamento">Tem triângulo e extintor?</p><div class="giro-check-options"><label><input type="radio" name="equipamento" value="sim"><span>Sim</span></label><label><input type="radio" name="equipamento" value="nao"><span>Não</span></label></div></div>
                            <div class="giro-check-question" role="group" aria-labelledby="check-q-documentos"><p id="check-q-documentos">Os documentos estão válidos?</p><div class="giro-check-options"><label><input type="radio" name="documentos" value="sim"><span>Sim</span></label><label><input type="radio" name="documentos" value="nao"><span>Não</span></label></div></div>
                            <div class="giro-check-question" role="group" aria-labelledby="check-q-parabrisas"><p id="check-q-parabrisas">Existem fissuras no para-brisas?</p><div class="giro-check-options"><label><input type="radio" name="parabrisas" value="sim"><span>Sim</span></label><label><input type="radio" name="parabrisas" value="nao"><span>Não</span></label></div></div>
                        </div>
                        <div class="giro-checklist-result" id="checklist-result" role="status" aria-live="polite" hidden><p id="checklist-result-message"></p><a href="index-ship-freight.html#marcar-inspecao">Marcar inspecção <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
                    </form>
                    <p class="giro-checklist-note">Esta checklist é apenas uma orientação rápida. A avaliação oficial é realizada durante a inspeção técnica.</p>
                </div>
            </dialog>
        `);
        dialog = document.getElementById('checklist-dialog');
        form = document.getElementById('vehicle-checklist');
    } else if (form) {
        dialog = form.closest('dialog');
    }

    if (!form) return;

    const closeButton = dialog ? dialog.querySelector('.giro-checklist-close') : null;
    const progressTrack = form.querySelector('[role="progressbar"]');
    const progressLabel = form.querySelector('#checklist-progress-label');
    const progressFill = form.querySelector('.giro-checklist-progress-fill');
    const result = form.querySelector('#checklist-result');
    const resultMessage = form.querySelector('#checklist-result-message');
    const questions = ['luzes', 'pneus', 'equipamento', 'documentos', 'parabrisas'];
    let lockedScrollY = 0;
    let bodyStyleSnapshot = null;

    function lockPageScroll() {
        if (bodyStyleSnapshot) return;
        lockedScrollY = window.scrollY;
        bodyStyleSnapshot = {
            position: document.body.style.position,
            top: document.body.style.top,
            left: document.body.style.left,
            right: document.body.style.right,
            width: document.body.style.width,
            overflow: document.body.style.overflow
        };
        document.documentElement.classList.add('giro-checklist-page-locked');
        document.body.classList.add('giro-checklist-page-locked');
        document.body.style.position = 'fixed';
        document.body.style.top = '-' + lockedScrollY + 'px';
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
        document.body.style.overflow = 'hidden';
    }

    function unlockPageScroll() {
        if (!bodyStyleSnapshot) return;
        document.documentElement.classList.remove('giro-checklist-page-locked');
        document.body.classList.remove('giro-checklist-page-locked');
        Object.keys(bodyStyleSnapshot).forEach(function (property) {
            document.body.style[property] = bodyStyleSnapshot[property];
        });
        bodyStyleSnapshot = null;
        window.scrollTo(0, lockedScrollY);
    }

    function resetChecklist() {
        form.reset();
        if (progressLabel) progressLabel.textContent = '0 de 5 respondidas';
        if (progressTrack) progressTrack.setAttribute('aria-valuenow', '0');
        if (progressFill) progressFill.style.width = '0%';
        if (result) result.hidden = true;
        if (result) result.classList.remove('is-review');
    }

    function openChecklist() {
        if (!dialog) return;
        resetChecklist();
        dialog.showModal();
        lockPageScroll();
    }

    triggers.forEach(function (trigger) { trigger.addEventListener('click', openChecklist); });
    if (closeButton) closeButton.addEventListener('click', function () { dialog.close(); });
    if (dialog) {
        dialog.addEventListener('close', unlockPageScroll);
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) dialog.close();
        });
    }
    form.addEventListener('submit', function (event) { event.preventDefault(); });

    const bookingLink = form.querySelector('.giro-checklist-result a');
    if (dialog && bookingLink) bookingLink.addEventListener('click', function (event) {
        event.preventDefault();
        const destination = bookingLink.href;
        dialog.close();
        unlockPageScroll();
        window.location.assign(destination);
    });

    form.addEventListener('change', function () {
        const answers = questions.map(function (name) {
            const selected = form.querySelector('input[name="' + name + '"]:checked');
            return selected ? selected.value : null;
        });
        const answered = answers.filter(Boolean).length;
        if (progressLabel) progressLabel.textContent = answered + ' de ' + questions.length + ' respondidas';
        if (progressTrack) progressTrack.setAttribute('aria-valuenow', String(answered));
        if (progressFill) progressFill.style.width = (answered / questions.length * 100) + '%';
        if (answered < questions.length) {
            if (result) result.hidden = true;
            return;
        }

        const hasIssue = answers.slice(0, 4).includes('nao') || answers[4] === 'sim';
        result.classList.toggle('is-review', hasIssue);
        resultMessage.textContent = hasIssue
            ? 'Identificámos alguns pontos que deve verificar antes da inspeção.'
            : 'A sua viatura parece pronta para a inspeção. Agende agora a sua visita.';
        result.hidden = false;
    });

    if (dialog && window.location.hash === '#checklist') openChecklist();
}());
