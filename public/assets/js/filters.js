export function init() {
    const btnFiltrer = document.getElementById('btnFiltrer');
    const priceSlider = document.getElementById('price-slider');
    if (!btnFiltrer && !priceSlider) return;
    
    // =============================================
    // FILTRES MENUS - AJAX
    // =============================================
    if (btnFiltrer) {
        btnFiltrer.addEventListener('click', function() {
            const filters = {
                min_price:  parseFloat(document.getElementById('min_price').value) || null,
                max_price:  parseFloat(document.getElementById('max_price').value) || null,
                min_guests: parseInt(document.getElementById('min_guests').value) || null,
                theme_id:   parseInt(document.getElementById('theme_id').value) || null,
                diet:       document.getElementById('diet').value || null
            };
            fetch('/menus', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(filters)
            })
            .then(r => r.json())
            .then(menus => afficherMenus(menus))
            .catch(e => console.error('Erreur filtres:', e));
        });
    }

    // =============================================
    // SLIDER PRIX
    // =============================================
    if (priceSlider) {
        noUiSlider.create(priceSlider, {
            start: [0, 200], connect: true, step: 1,
            range: { 'min': 0, 'max': 200 }
        });
        priceSlider.noUiSlider.on('update', function(values) {
            document.getElementById('min_price').value       = Math.round(values[0]);
            document.getElementById('max_price').value       = Math.round(values[1]);
            document.getElementById('min_price_input').value = Math.round(values[0]);
            document.getElementById('max_price_input').value = Math.round(values[1]);
            document.getElementById('price-display').textContent =
                Math.round(values[0]) + '€ - ' + Math.round(values[1]) + '€';
        });
        document.getElementById('min_price_input').addEventListener('change', function() {
            priceSlider.noUiSlider.set([this.value, null]);
        });
        document.getElementById('max_price_input').addEventListener('change', function() {
            priceSlider.noUiSlider.set([null, this.value]);
        });
    }
}    




export function afficherMenus(menus) {
    const liste = document.getElementById('liste-menus');
    if (!liste) return;
    liste.innerHTML = '';
    menus.forEach(m => {
        liste.innerHTML += `
            <div class="card mb-3">
                <div class="row g-0">
                    <div class="col-4">
                        <img src="/assets/images/menu-default.jpg"
                             alt="${m.name}"
                             class="img-fluid rounded-start h-100"
                             style="object-fit: cover;">
                    </div>
                    <div class="col-8">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h2 class="h5">${m.name} ${m.theme_name ? '- ' + m.theme_name : ''}</h2>
                                <span class="fw-bold">${m.price_per_person}€/pers</span>
                            </div>
                            <p class="text-muted small">${m.description}</p>
                            ${m.allergens ? `<p class="small">Allergènes : ${m.allergens}</p>` : ''}
                           <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-secondary dispo-badge" id="dispo-${m.id}"></span>
                                <a href="/menus/${m.id}" class="btn btn-primary btn-sm" id="btn-detail-${m.id}">Détails</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
}