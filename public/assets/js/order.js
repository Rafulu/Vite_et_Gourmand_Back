export function init() {
    const guestInput = document.getElementById('guest_count');
    if (!guestInput) return;

    const addressSelect = document.getElementById('delivery_address_id');
    const btnSubmit     = document.getElementById('btn-submit');
    const acceptCond    = document.getElementById('accept_conditions');
    const acceptCgv     = document.getElementById('accept_cgv');


    const pricePerPerson = parseFloat(document.querySelector('[name="menu_price_per_person"]').value);
    const minGuests      = parseInt(document.querySelector('[name="min_guests"]').value);

    const ORS_API_KEY       = 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6ImVkMmQ0NGMyYWFhOTQ0ZDU4NTNiOGI0OWVhMzBiNjA4IiwiaCI6Im11cm11cjY0In0=';
    const ENTREPRISE_COORDS = [-0.5792, 44.8378];
    const geoCache = {};

    // ---- Calcul options ----
    function calculerOptions() {
        let total = 0;
        document.querySelectorAll('.resource-qty').forEach(input => {
            const qty   = parseInt(input.value) || 0;
            const price = parseFloat(input.closest('.resource-row').dataset.price) || 0;
            total += qty * price;
        });
        return total;
    }

    // ---- Recap options (toujours affiché) ----
    function majRecapOptions(optionPrice) {
        document.getElementById('recap-options').textContent    = optionPrice.toFixed(2) + '€';
        document.getElementById('option_price_hidden').value    = optionPrice.toFixed(2);
    }

    // ---- Calcul prix principal ----
    async function calculerPrix() {
        const guests = parseInt(guestInput.value) || 0;

        if (guests < minGuests) {
            document.getElementById('recap-guests').textContent    = '-';
            document.getElementById('recap-menu-brut').textContent = '-';
            document.getElementById('recap-discount-row').classList.add('d-none');
            document.getElementById('recap-delivery').textContent  = '-';
            document.getElementById('recap-total').textContent     = '-';
            majRecapOptions(calculerOptions());
            majBouton();
            return;
        }

        const selectedOption = addressSelect.options[addressSelect.selectedIndex];
        let city    = selectedOption?.dataset.city || '';
        let address = selectedOption?.dataset.address || '';

        if (addressSelect.value === 'new') {
            const number  = document.querySelector('[name="new_delivery_number"]')?.value.trim() || '';
            const street  = document.querySelector('[name="new_delivery_street"]')?.value.trim() || '';
            const postal  = document.querySelector('[name="new_delivery_postal"]')?.value.trim() || '';
            const newCity = document.querySelector('[name="new_delivery_city"]')?.value.trim() || '';
            city    = newCity;
            address = (number + ' ' + street + ' ' + postal + ' ' + newCity + ' France').trim();
        }

        // Prix brut
        const menuBrut = pricePerPerson * guests;

        // Réduction
        let discount = 0;
        let menuNet  = menuBrut;
        if (guests >= minGuests + 5) {
            discount = menuBrut * 0.10;
            menuNet  = menuBrut - discount;
            document.getElementById('recap-discount-row').classList.remove('d-none');
            document.getElementById('recap-discount').textContent = '-' + discount.toFixed(2) + '€';
            document.getElementById('discount_hidden').value = 1;
        } else {
            document.getElementById('recap-discount-row').classList.add('d-none');
            document.getElementById('discount_hidden').value = 0;
        }

        // Options
        const optionPrice = calculerOptions();
        majRecapOptions(optionPrice);

        // Livraison
        let deliveryPrice = 0;
        let deliveryLabel = 'Gratuit';

        if (city.toLowerCase() !== 'bordeaux' && address) {
            document.getElementById('recap-delivery').textContent = 'Calcul en cours...';
            try {
                let coords = geoCache[address] ?? null;
                if (!coords) {
                    const geoRes  = await fetch(
                        `https://api.openrouteservice.org/geocode/search?api_key=${ORS_API_KEY}&text=${encodeURIComponent(address)}&size=1`
                    );
                    const geoData = await geoRes.json();
                    coords = geoData.features?.[0]?.geometry?.coordinates;
                    if (coords) geoCache[address] = coords;
                }

                if (coords) {
                    const routeRes = await fetch('https://api.openrouteservice.org/v2/directions/driving-car', {
                        method: 'POST',
                        headers: {
                            'Authorization': ORS_API_KEY,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ coordinates: [ENTREPRISE_COORDS, coords] })
                    });
                    const routeData = await routeRes.json();
                    const km        = (routeData.routes?.[0]?.summary?.distance || 0) / 1000;
                    deliveryPrice   = 5 + (km * 0.59);
                    deliveryLabel   = km.toFixed(1) + ' km — ' + deliveryPrice.toFixed(2) + '€';
                } else {
                    deliveryPrice = 5;
                    deliveryLabel = '5.00€ (adresse introuvable)';
                }
            } catch (e) {
                deliveryPrice = 5;
                deliveryLabel = '5.00€ (erreur réseau)';
            }
        } else if (city.toLowerCase() !== 'bordeaux' && !address) {
            deliveryPrice = 5;
            deliveryLabel = '5.00€ (hors Bordeaux)';
        }

        // Total
        const total = menuNet + optionPrice + deliveryPrice;

        // Affichage
        document.getElementById('recap-guests').textContent     = guests + ' personnes';
        document.getElementById('recap-menu-brut').textContent  = menuBrut.toFixed(2) + '€';
        document.getElementById('recap-delivery').textContent   = deliveryLabel;
        document.getElementById('recap-total').textContent      = total.toFixed(2) + '€';

        // Champs hidden
        document.getElementById('total_price').value           = total.toFixed(2);
        document.getElementById('menu_price_hidden').value     = menuNet.toFixed(2);
        document.getElementById('delivery_price_hidden').value = deliveryPrice.toFixed(2);

        majBouton();
    }

    // ---- Déverrouillage bouton ----
    function majBouton() {
        const guests    = parseInt(guestInput.value) || 0;
        const condOk    = acceptCond?.checked;
        const cgvOk     = acceptCgv?.checked;
        const totalOk   = parseFloat(document.getElementById('total_price').value) > 0;
        const adresseOk = addressSelect.value !== '' && addressSelect.value !== null;

        const ok = guests >= minGuests && condOk && cgvOk && totalOk && adresseOk;
        btnSubmit.disabled = !ok;
        btnSubmit.setAttribute('aria-disabled', String(!ok));
    }

    // ---- Compteurs ressources +/- ----
    document.querySelectorAll('.resource-row').forEach(row => {
        const qtyInput = row.querySelector('.resource-qty');
        const btnMinus = row.querySelector('.btn-minus');
        const btnPlus  = row.querySelector('.btn-plus');
        const type     = row.dataset.type;
        const id       = row.dataset.resourceId;
        const total    = parseInt(row.dataset.total);

        btnMinus.addEventListener('click', function() {
            const val = parseInt(qtyInput.value) || 0;
            if (val > 0) {
                qtyInput.value = val - 1;
                majDatesVisibilite(id, val - 1, type);
                calculerPrix();
            }
        });

        btnPlus.addEventListener('click', function() {
            const val   = parseInt(qtyInput.value) || 0;
            const avail = parseInt(document.getElementById('avail-' + id)?.dataset.available) || total;
            if (val < avail) {
                qtyInput.value = val + 1;
                majDatesVisibilite(id, val + 1, type);
                calculerPrix();
            }
        });

        qtyInput.addEventListener('change', function() {
            const avail = parseInt(document.getElementById('avail-' + id)?.dataset.available) || total;
            let val = parseInt(qtyInput.value) || 0;
            if (val > avail) val = avail;
            if (val < 0)     val = 0;
            qtyInput.value = val;
            majDatesVisibilite(id, val, type);
            calculerPrix();
        });
    });

    // ---- Visibilité dates selon type et quantité ----
    function majDatesVisibilite(id, qty, type) {
        if (type === 'MATERIEL_LOURD' || type === 'MATERIEL_LEGER') {
            const bloc = document.getElementById('return-' + id);
            if (bloc) {
                const hidden = qty === 0;
                bloc.classList.toggle('d-none', hidden);
                if (hidden) bloc.setAttribute('inert', '');
                else bloc.removeAttribute('inert');
                const input = bloc.querySelector('input[type="date"]');
                if (input) input.required = !hidden;
            }
        }
        if (type === 'PERSONNEL') {
            const bloc = document.getElementById('personnel-' + id);
            if (bloc) {
                const hidden = qty === 0;
                bloc.classList.toggle('d-none', hidden);
                if (hidden) bloc.setAttribute('inert', '');
                else bloc.removeAttribute('inert');
                bloc.querySelectorAll('input[type="date"]').forEach(i => i.required = !hidden);
            }
        }
    }

    // ---- Disponibilité ressources selon date ----
    async function chargerDisponibilites() {
        const deliveryDate = document.getElementById('delivery_date')?.value;
        if (!deliveryDate) return;

        try {
            const res  = await fetch('/resources/available?date=' + encodeURIComponent(deliveryDate));
            const data = await res.json();

            document.querySelectorAll('.resource-row').forEach(row => {
                const id    = row.dataset.resourceId;
                const total = parseInt(row.dataset.total);
                const avail = data[id] !== undefined ? data[id] : total;
                const badge = document.getElementById('avail-' + id);
                const qty   = row.querySelector('.resource-qty');

                if (badge) {
                    badge.dataset.available = avail;
                    badge.textContent       = avail + ' disponible(s)';
                    badge.className         = 'badge ' + (avail > 0 ? 'bg-success' : 'bg-danger');
                }
                if (qty) qty.max = avail;
            });
        } catch (e) {
            document.querySelectorAll('.resource-row').forEach(row => {
                const id    = row.dataset.resourceId;
                const total = parseInt(row.dataset.total);
                const badge = document.getElementById('avail-' + id);
                if (badge) {
                    badge.dataset.available = total;
                    badge.textContent       = total + ' disponible(s)';
                    badge.className         = 'badge bg-secondary';
                }
            });
        }
    }

    // ---- Listeners ----
    let debounceTimer;
    guestInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(calculerPrix, 500);
    });
    addressSelect.addEventListener('change', calculerPrix);
    ['new_delivery_number','new_delivery_street','new_delivery_postal','new_delivery_city'].forEach(name => {
        document.querySelector('[name="' + name + '"]')?.addEventListener('input', calculerPrix);
    });
    acceptCond?.addEventListener('change', majBouton);
    acceptCgv?.addEventListener('change', majBouton);

    document.getElementById('delivery_date')?.addEventListener('change', function() {
        chargerDisponibilites();
        calculerPrix();
    });

    // Adresse livraison
    addressSelect.addEventListener('change', function() {
        const newDelivery = document.getElementById('new-delivery-address');
        newDelivery.classList.toggle('d-none', this.value !== 'new');
        if (this.value === 'new') newDelivery.removeAttribute('inert');
        else newDelivery.setAttribute('inert', '');
    });

    // Adresse facturation
    const sameAddress = document.getElementById('same_address');
    if (sameAddress) {
        sameAddress.addEventListener('change', function() {
            const bloc = document.getElementById('billing-block');
            bloc.classList.toggle('d-none', this.checked);
            if (!this.checked) bloc.removeAttribute('inert');
            else bloc.setAttribute('inert', '');
        });
        const billingSelect = document.getElementById('billing_address_id');
        if (billingSelect) {
            billingSelect.addEventListener('change', function() {
                const newBilling = document.getElementById('new-billing-address');
                newBilling.classList.toggle('d-none', this.value !== 'new');
                if (this.value === 'new') newBilling.removeAttribute('inert');
                else newBilling.setAttribute('inert', '');
            });
        }
    }

    // Init
    chargerDisponibilites();
    majBouton();
}