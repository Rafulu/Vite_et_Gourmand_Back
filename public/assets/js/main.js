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

   // Initialisation du slider de prix
    const priceSlider = document.getElementById('price-slider');

    if (priceSlider) {
        noUiSlider.create(priceSlider, {
            start: [0, 200],
            connect: true,
            step: 1,
            range: { 'min': 0, 'max': 200 }
        });

        // Mise à jour des inputs et du texte quand le slider bouge
        priceSlider.noUiSlider.on('update', function(values) {
            document.getElementById('min_price').value = Math.round(values[0]);
            document.getElementById('max_price').value = Math.round(values[1]);
            document.getElementById('min_price_input').value = Math.round(values[0]);
            document.getElementById('max_price_input').value = Math.round(values[1]);
            document.getElementById('price-display').textContent = 
                Math.round(values[0]) + '€ - ' + Math.round(values[1]) + '€';
        });

        // Mise à jour du slider quand on tape dans les inputs
        document.getElementById('min_price_input').addEventListener('change', function() {
            priceSlider.noUiSlider.set([this.value, null]);
        });

        document.getElementById('max_price_input').addEventListener('change', function() {
            priceSlider.noUiSlider.set([null, this.value]);
        });
    }

    // Calcul des prix pour le formulaire de commande
    const guestInput = document.getElementById('guest_count');
    const addressSelect = document.getElementById('delivery_address_id');

    if (guestInput) {
    
        const pricePerPerson = parseFloat(document.querySelector('[name="menu_price_per_person"]').value);
        const minGuests = parseInt(document.querySelector('[name="min_guests"]').value);

        function calculerPrix() {
            const guests = parseInt(guestInput.value) || 0;
            const city = addressSelect.options[addressSelect.selectedIndex]?.dataset.city || '';
        
            if (guests < minGuests) return;

            // Prix menu
            let menuPrice = pricePerPerson * guests;
        
            // Réduction 10% si guests >= min_guests + 5
            let discount = 0;
            if (guests >= minGuests + 5) {
                discount = menuPrice * 0.10;
                menuPrice = menuPrice - discount;
                document.getElementById('recap-discount-row').classList.remove('d-none');
                document.getElementById('recap-discount').textContent = '-' + discount.toFixed(2) + '€';
                document.getElementById('discount_hidden').value = 1;
            } else {
                document.getElementById('recap-discount-row').classList.add('d-none');
                document.getElementById('discount_hidden').value = 0;
            }

            // Frais livraison
            let deliveryPrice = 0;
            if (city.toLowerCase() !== 'bordeaux') {
                deliveryPrice = 5;
                // On mettra le calcul km plus tard
            }

            // Total
            const total = menuPrice + deliveryPrice;

            // Affichage
            document.getElementById('recap-guests').textContent = guests + ' personnes';
            document.getElementById('recap-menu-price').textContent = menuPrice.toFixed(2) + '€';
            document.getElementById('recap-delivery').textContent = deliveryPrice === 0 ? 'Gratuit' : deliveryPrice.toFixed(2) + '€';
            document.getElementById('recap-total').textContent = total.toFixed(2) + '€';

            // Valeurs cachées pour le formulaire
            document.getElementById('total_price').value = total.toFixed(2);
            document.getElementById('menu_price_hidden').value = menuPrice.toFixed(2);
            document.getElementById('delivery_price_hidden').value = deliveryPrice.toFixed(2);
        }

        guestInput.addEventListener('input', calculerPrix);
        addressSelect.addEventListener('change', calculerPrix);
    }

    // Afficher formulaire caché de l'adresse de livraison
    document.getElementById('delivery_address_id').addEventListener('change', function () {
        document.getElementById('new-delivery-address').classList.toggle('d-none', this.value !== 'new');
    });

    // Afficher/cacher adresse facturation
    const sameAddress = document.getElementById('same_address');
    if (sameAddress) {
        sameAddress.addEventListener('change', function() {
            document.getElementById('billing-block').classList.toggle('d-none', this.checked);
        });
        const billingSelect = document.getElementById('billing_address_id');
        if (billingSelect) {
            billingSelect.addEventListener('change', function () {
                document.getElementById('new-billing-address').classList.toggle('d-none', this.value !== 'new');
            });
        }
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