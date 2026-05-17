document.addEventListener('DOMContentLoaded', function() {

    // Filtres menus - AJAX sans rechargement de page
    const btnFiltrer = document.getElementById('btnFiltrer');
    
    if (btnFiltrer) {
        btnFiltrer.addEventListener('click', function() {
            
            const filters = {
                min_price: parseFloat(document.getElementById('min_price').value) || null,
                max_price: parseFloat(document.getElementById('max_price').value) || null,
                min_guests: parseInt(document.getElementById('min_guests').value) || null,
                theme_id: parseInt(document.getElementById('theme_id').value) || null,
                diet: document.getElementById('diet').value || null
            };

            fetch('/menus', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(filters)
            })
            .then(response => response.json())
            .then(menus => afficherMenus(menus))
            .catch(error => console.error('Erreur:', error));
        });
    }
});

function afficherMenus(menus) {
    const liste = document.getElementById('liste-menus');
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
                            <div class="mt-2">
                                <input type="date" class="form-control form-control-sm d-inline w-auto">
                                <a href="/menus/${m.id}" class="btn btn-primary btn-sm ms-2">Détails</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
}