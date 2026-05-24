export function init() {
    const btnCheckDispo = document.getElementById('btn-check-dispo');
    const btnVerifier = document.getElementById('btnVerifier');
    if (!btnCheckDispo && !btnVerifier) return;

    // =============================================
    // DISPONIBILITÉ GLOBALE PAGE MENUS
    // =============================================
    if (btnCheckDispo) {
        btnCheckDispo.addEventListener('click', async function() {
            const date = document.getElementById('global-date').value;
            if (!date) return;

            const badges = document.querySelectorAll('.dispo-badge');
            badges.forEach(b => { b.textContent = '...'; b.className = 'badge bg-secondary dispo-badge'; });

            const cards = document.querySelectorAll('[id^="dispo-"]');
            for (const badge of cards) {
                const menuId = badge.id.replace('dispo-', '');
                try {
                    const res  = await fetch(`/menus/capacity?menu_id=${menuId}&date=${encodeURIComponent(date)}`);
                    const data = await res.json();
                    const link = document.getElementById('btn-detail-' + menuId);

                    if (data.error) {
                        badge.textContent = data.error;
                        badge.className = 'badge bg-warning text-dark dispo-badge';
                    } else if (data.status === 'unavailable') {
                        badge.textContent = 'Indisponible';
                        badge.className = 'badge bg-danger dispo-badge';
                    } else {
                        badge.textContent = data.message;
                        badge.className = 'badge bg-success dispo-badge';
                        if (link) link.href = `/menus/${menuId}?date=${encodeURIComponent(date)}`;
                    }
                } catch(e) {
                    badge.textContent = 'Erreur';
                    badge.className = 'badge bg-danger dispo-badge';
                }
            }
        });
    }

    // =============================================
    // VÉRIFICATION DISPONIBILITÉ MENU
    // =============================================
    if (btnVerifier) {
        btnVerifier.addEventListener('click', async function() {
            const date    = document.getElementById('check_date')?.value;
            const guests  = parseInt(document.getElementById('check_guests')?.value) || 0;
            const menuId  = window.location.pathname.split('/').pop();
            const div     = document.getElementById('disponibilite');

            if (!date) {
                div.innerHTML = '<span class="text-danger">Veuillez choisir une date.</span>';
                return;
            }

            div.innerHTML = '<span class="text-muted">Vérification en cours...</span>';

            try {
                const res  = await fetch(`/menus/capacity?menu_id=${menuId}&date=${encodeURIComponent(date)}`);
                const data = await res.json();

                if (data.error) {
                    div.innerHTML = `<span class="text-danger">${data.error}</span>`;
                } else if (data.status === 'unavailable') {
                    div.innerHTML = '<span class="badge bg-danger">Indisponible</span>';
                } else {
                    const guestsOk = guests > 0 && guests <= parseInt(data.message);
                    div.innerHTML = `<span class="badge bg-success">${data.message}</span>`;
                }
            } catch(e) {
                div.innerHTML = '<span class="text-danger">Erreur de connexion.</span>';
            }
        });
    }
}    