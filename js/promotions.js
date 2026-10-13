(() => {
    'use strict';
    const dialog = document.getElementById('promotion-popup');
    if (!dialog) return;
    const list = document.getElementById('promotion-list');
    const error = document.getElementById('promotion-error');
    let lastSignature = '';
    let busy = false;
    document.getElementById('promotion-close').addEventListener('click', () => dialog.close());
    async function poll() {
        if (busy) return;
        busy = true;
        try {
            const response = await fetch('promotion_status.php', {credentials: 'same-origin', cache: 'no-store'});
            if (!response.ok) throw new Error('Status konnte nicht geladen werden.');
            const data = await response.json();
            error.textContent = '';
            const total = document.getElementById('work-total');
            if (total) total.textContent = `${Math.floor(data.work.total_seconds / 3600)} h ${Math.floor(data.work.total_seconds % 3600 / 60)} min`;
            const payable = document.getElementById('work-payable');
            if (payable) payable.textContent = data.work.payable_cents === null
                ? '—'
                : `${new Intl.NumberFormat('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(data.work.payable_cents / 100)} Taler`;
            const paid = document.getElementById('work-paid');
            if (paid) paid.textContent = data.work.paid_cents === null
                ? '—'
                : `${new Intl.NumberFormat('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(data.work.paid_cents / 100)} Taler`;
            const signature = data.promotions.map(item => `${item.id}:${item.due_hours}`).join(',');
            list.replaceChildren();
            for (const item of data.promotions) {
                const li = document.createElement('li');
                li.textContent = `${item.username} → ${item.rank} (${item.due_hours} Stunden verfügbar)`;
                if (item.can_confirm) {
                    const form = document.createElement('form');
                    form.method = 'post';
                    form.action = 'bf_action.php';
                    for (const [name, value] of Object.entries({
                        csrf_token: dialog.dataset.csrf, user_id: item.user_id,
                        action: 'confirm', promotion_id: item.id
                    })) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        input.value = String(value);
                        form.appendChild(input);
                    }
                    const button = document.createElement('button');
                    button.textContent = 'Beförderung bestätigen';
                    form.appendChild(button);
                    li.appendChild(form);
                }
                list.appendChild(li);
            }
            if (data.can_manage && data.promotions.length) {
                const li = document.createElement('li');
                const link = document.createElement('a');
                link.href = 'bf.php';
                link.textContent = 'Im BF-Panel bestätigen';
                li.appendChild(link);
                list.appendChild(li);
            }
            if (signature && signature !== lastSignature && !dialog.open) dialog.showModal();
            if (!signature && dialog.open) dialog.close();
            lastSignature = signature;
        } catch (failure) {
            error.textContent = 'Beförderungsstatus nicht erreichbar. Die Prüfung wird automatisch wiederholt.';
        } finally {
            busy = false;
        }
    }
    poll();
    setInterval(poll, 15000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) poll();
    });
})();
