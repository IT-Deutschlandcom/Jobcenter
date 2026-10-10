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
            const signature = data.promotions.map(item => `${item.id}:${item.due_hours}`).join(',');
            list.replaceChildren();
            for (const item of data.promotions) {
                const li = document.createElement('li');
                li.textContent = `${item.username} → ${item.rank} (${item.due_hours} Stunden verfügbar)`;
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
