<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Modifier ma commande - Vite & Gourmand';
    $description = 'Modifier votre commande';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <main class="container my-5">
        <h1>Modifier la commande <?php echo htmlspecialchars($orderData['order_number']); ?></h1>
        <a href="/my-orders" class="btn btn-secondary mb-4">← Mes commandes</a>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <form action="/orders/<?php echo (int)$orderData['id']; ?>/edit" method="POST" id="order-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
            <input type="hidden" name="menu_price_per_person" value="<?php echo (float)$menuData['price_per_person']; ?>">
            <input type="hidden" name="min_guests" value="<?php echo (int)$menuData['min_guests']; ?>">

            <!-- Menu (non modifiable) -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Menu</h2>
                    <p class="mb-0"><strong><?php echo htmlspecialchars($menuData['name']); ?></strong> — <?php echo number_format($menuData['price_per_person'], 2, ',', ' '); ?>€/personne</p>
                </div>
            </div>

            <!-- Informations client -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Vos informations</h2>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['last_name'] ?? ''); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date et heure -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Date et heure de livraison</h2>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="delivery_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="delivery_date" name="delivery_date"
                                   required min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo date('Y-m-d', strtotime($orderData['delivery_date'])); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="delivery_time" class="form-label">Heure <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="delivery_time" name="delivery_time"
                                   required step="900"
                                   value="<?php echo date('H:i', strtotime($orderData['delivery_date'])); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adresses -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h2 class="h5 mb-0">Adresse de livraison <span class="text-danger">*</span></h2>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="same_address" checked aria-controls="billing-block">
                                    <label class="form-check-label small" for="same_address">Facturation identique</label>
                                </div>
                            </div>
                            <select class="form-select" id="delivery_address_id" name="delivery_address_id" required>
                                <option value="">Choisir une adresse</option>
                                <?php foreach ($addresses as $addr): ?>
                                    <option value="<?php echo (int)$addr['id']; ?>"
                                        data-city="<?php echo htmlspecialchars($addr['city']); ?>"
                                        data-address="<?php echo htmlspecialchars($addr['number'] . ' ' . $addr['street'] . ' ' . $addr['postal_code'] . ' ' . $addr['city'] . ' France'); ?>"
                                        <?php echo $addr['id'] == $orderData['delivery_address_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($addr['name'] . ' - ' . $addr['number'] . ' ' . $addr['street'] . ', ' . $addr['postal_code'] . ' ' . $addr['city']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3 d-none" id="billing-block" inert>
                            <h2 class="h5 mb-2">Adresse de facturation</h2>
                            <select class="form-select" id="billing_address_id" name="billing_address_id">
                                <option value="">Choisir une adresse</option>
                                <?php foreach ($addresses as $addr): ?>
                                    <option value="<?php echo (int)$addr['id']; ?>"
                                        <?php echo $addr['id'] == $orderData['billing_address_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($addr['name'] . ' - ' . $addr['street'] . ', ' . $addr['city']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nombre de personnes -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Nombre de personnes</h2>
                    <p class="text-muted">Minimum requis : <strong><?php echo (int)$menuData['min_guests']; ?> personnes</strong></p>
                    <p class="text-muted">Réduction de 10% à partir de <strong><?php echo (int)$menuData['min_guests'] + 5; ?> personnes</strong></p>
                    <div class="col-md-4">
                        <label for="guest_count" class="form-label">Nombre de personnes <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="guest_count" name="guest_count"
                               min="<?php echo (int)$menuData['min_guests']; ?>" max="9999" required
                               value="<?php echo (int)$orderData['guest_count']; ?>">
                    </div>
                </div>
            </div>

            <!-- Ressources -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Options et matériel</h2>
                    <p class="text-muted small">Si une ressource est insuffisante, précisez votre besoin dans les informations complémentaires.</p>
                    <?php
                    $types = [
                        'MATERIEL_LOURD' => 'Matériel lourd',
                        'MATERIEL_LEGER' => 'Matériel léger',
                        'CONSOMMABLE'    => 'Consommables',
                        'PERSONNEL'      => 'Personnel',
                    ];
                    $grouped = [];
                    foreach ($resources as $r) {
                        $grouped[$r['type']][] = $r;
                    }
                    ?>
                    <?php foreach ($types as $typeKey => $typeLabel): ?>
                        <?php if (!empty($grouped[$typeKey])): ?>
                        <h3 class="h6 mt-3 text-muted"><?php echo htmlspecialchars($typeLabel); ?></h3>
                        <?php foreach ($grouped[$typeKey] as $r): ?>
                        <div class="row align-items-center mb-3 resource-row"
                             data-resource-id="<?php echo (int)$r['id']; ?>"
                             data-type="<?php echo htmlspecialchars($r['type']); ?>"
                             data-price="<?php echo (float)$r['unit_price']; ?>"
                             data-total="<?php echo (int)$r['total_quantity']; ?>">
                            <div class="col-md-4">
                                <span class="fw-semibold"><?php echo htmlspecialchars($r['name']); ?></span>
                                <span class="text-muted small ms-2"><?php echo number_format($r['unit_price'], 2, ',', ' '); ?>€/unité</span>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-secondary" id="avail-<?php echo (int)$r['id']; ?>" aria-live="polite">
                                    Sélectionnez une date
                                </span>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-minus">−</button>
                                    <input type="number" class="form-control text-center resource-qty"
                                           name="resources[<?php echo (int)$r['id']; ?>][quantity]"
                                           value="0" min="0" max="<?php echo (int)$r['total_quantity']; ?>">
                                    <button type="button" class="btn btn-outline-secondary btn-plus">+</button>
                                    <input type="hidden" name="resources[<?php echo (int)$r['id']; ?>][unit_price]" value="<?php echo (float)$r['unit_price']; ?>">
                                    <input type="hidden" name="resources[<?php echo (int)$r['id']; ?>][type]" value="<?php echo htmlspecialchars($r['type']); ?>">
                                </div>
                            </div>
                            <?php if (in_array($r['type'], ['MATERIEL_LOURD', 'MATERIEL_LEGER'])): ?>
                            <div class="col-md-2 d-none mt-2" id="return-<?php echo (int)$r['id']; ?>" inert>
                                <label class="form-label small mb-0">Date retour <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm"
                                       name="resources[<?php echo (int)$r['id']; ?>][return_date]"
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <?php endif; ?>
                            <?php if ($r['type'] === 'PERSONNEL'): ?>
                            <div class="col-md-4 d-none mt-2" id="personnel-<?php echo (int)$r['id']; ?>" inert>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <label class="form-label small mb-0">Du <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm"
                                               name="resources[<?php echo (int)$r['id']; ?>][date_start]"
                                               min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-0">Au <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm"
                                               name="resources[<?php echo (int)$r['id']; ?>][date_end]"
                                               min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Informations complémentaires -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Informations complémentaires</h2>
                    <label for="detail" class="form-label">Précisions sur votre commande</label>
                    <textarea class="form-control" id="detail" name="detail" rows="3" maxlength="2000"><?php echo htmlspecialchars($orderData['detail'] ?? '', ENT_COMPAT, 'UTF-8'); ?></textarea>
                </div>
            </div>

            <!-- Récapitulatif prix -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Récapitulatif du prix</h2>
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td>Prix par personne</td>
                            <td class="text-end"><?php echo number_format((float)$menuData['price_per_person'], 2, ',', ' '); ?>€</td>
                        </tr>
                        <tr><td>Nombre de personnes</td><td class="text-end" id="recap-guests" aria-live="polite">-</td></tr>
                        <tr><td>Prix menu (brut)</td><td class="text-end" id="recap-menu-brut" aria-live="polite">-</td></tr>
                        <tr id="recap-discount-row" class="d-none">
                            <td>Réduction 10%</td><td class="text-end text-success" id="recap-discount" aria-live="polite">-</td>
                        </tr>
                        <tr><td>Options / Matériel</td><td class="text-end" id="recap-options" aria-live="polite">0.00€</td></tr>
                        <tr><td>Frais de livraison</td><td class="text-end" id="recap-delivery" aria-live="polite">-</td></tr>
                        <tr class="fw-bold border-top"><td>Total</td><td class="text-end" id="recap-total" aria-live="polite">-</td></tr>
                        </tbody>
                    </table>
                    <input type="hidden" name="total_price" id="total_price">
                    <input type="hidden" name="menu_price" id="menu_price_hidden">
                    <input type="hidden" name="option_price" id="option_price_hidden">
                    <input type="hidden" name="delivery_price" id="delivery_price_hidden">
                    <input type="hidden" name="discount" id="discount_hidden" value="0">
                </div>
            </div>

            <!-- Conditions -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Conditions</h2>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="accept_conditions" name="accept_conditions" required>
                        <label class="form-check-label" for="accept_conditions">
                            J'ai pris connaissance des conditions et je m'engage à les respecter
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- CGV -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="accept_cgv" name="accept_cgv" required>
                        <label class="form-check-label" for="accept_cgv">
                            J'accepte les <a href="/cgv" target="_blank">Conditions Générales de Vente</a>
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Bouton -->
            <div class="d-flex justify-content-center mb-5">
                <button type="submit" id="btn-submit" class="btn btn-primary px-5" disabled aria-disabled="true">
                    Enregistrer les modifications
                </button>
            </div>
            <p class="text-center text-muted small">Le bouton se déverrouille une fois les conditions et les CGV acceptées.</p>

        </form>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
    <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>