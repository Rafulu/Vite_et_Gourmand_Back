<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Commander - Vite & Gourmand';
    $description = 'Passer une commande Vite & Gourmand';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <main class="container my-5">
        <h1>Commander - <?php echo htmlspecialchars($menu['name']); ?></h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="/orders" method="POST" id="order-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
            <input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>">
            <input type="hidden" name="menu_price_per_person" value="<?php echo (float)$menu['price_per_person']; ?>">
            <input type="hidden" name="min_guests" value="<?php echo (int)$menu['min_guests']; ?>">

            <!-- Informations client -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Vos informations</h2>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label" for="info-prenom">Prénom</label>
                            <input type="text" class="form-control" id="info-prenom"
                                   value="<?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>"
                                   aria-label="Prénom" readonly aria-readonly="true">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="info-nom">Nom</label>
                            <input type="text" class="form-control" id="info-nom"
                                   value="<?php echo htmlspecialchars($_SESSION['last_name'] ?? ''); ?>"
                                   aria-label="Nom" readonly aria-readonly="true">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="info-email">Email</label>
                            <input type="email" class="form-control" id="info-email"
                                   value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>"
                                   aria-label="Email" readonly aria-readonly="true">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="info-phone">Téléphone</label>
                            <input type="tel" class="form-control" id="info-phone"
                                   value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>"
                                   aria-label="Téléphone" readonly aria-readonly="true">
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
                            <label for="delivery_date" class="form-label">Date <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="date" class="form-control" id="delivery_date" name="delivery_date"
                                   required aria-required="true"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   aria-describedby="delivery-date-error">
                            <div id="delivery-date-error" class="invalid-feedback" role="alert" aria-live="polite"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="delivery_time" class="form-label">Heure <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="time" class="form-control" id="delivery_time" name="delivery_time"
                                   required aria-required="true"
                                   step="900" list="time-options"
                                   aria-describedby="delivery-time-error">
                            <datalist id="time-options">
                                <?php for ($h = 0; $h < 24; $h++): foreach ([0, 15, 30, 45] as $m): ?>
                                    <option value="<?php printf('%02d:%02d', $h, $m); ?>">
                                <?php endforeach; endfor; ?>
                            </datalist>
                            <div id="delivery-time-error" class="invalid-feedback" role="alert" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adresses -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">

                        <!-- Adresse de livraison -->
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h2 class="h5 mb-0">Adresse de livraison <span class="text-danger" aria-hidden="true">*</span></h2>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="same_address" checked
                                           aria-controls="billing-block">
                                    <label class="form-check-label small" for="same_address">Facturation identique</label>
                                </div>
                            </div>
                            <label for="delivery_address_id" class="visually-hidden">Adresse de livraison</label>
                            <select class="form-select" id="delivery_address_id" name="delivery_address_id"
                                    required aria-required="true"
                                    aria-describedby="delivery-address-error">
                                <option value="">Choisir une adresse</option>
                                <?php foreach ($addresses as $addr): ?>
                                    <option value="<?php echo (int)$addr['id']; ?>"
                                        data-city="<?php echo htmlspecialchars($addr['city']); ?>"
                                        data-address="<?php echo htmlspecialchars($addr['number'] . ' ' . $addr['street'] . ' ' . $addr['postal_code'] . ' ' . $addr['city'] . ' France'); ?>">
                                        <?php echo htmlspecialchars($addr['name'] . ' - ' . $addr['number'] . ' ' . $addr['street'] . ', ' . $addr['postal_code'] . ' ' . $addr['city']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="new">+ Ajouter une nouvelle adresse</option>
                            </select>
                            <div id="delivery-address-error" class="invalid-feedback" role="alert" aria-live="polite"></div>

                            <div id="new-delivery-address" class="d-none mt-3">
                                <input type="text" class="form-control mb-2" name="new_delivery_name"
                                       placeholder="Nom de l'adresse" aria-label="Nom de l'adresse de livraison"
                                       maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Nom invalide">
                                <div class="row g-2 mb-2">
                                    <div class="col-3">
                                        <input type="text" class="form-control" name="new_delivery_number"
                                               placeholder="N°" aria-label="Numéro de voie"
                                               maxlength="10" pattern="[a-zA-Z0-9\s\-]+" title="Numéro invalide">
                                    </div>
                                    <div class="col-9">
                                        <input type="text" class="form-control" name="new_delivery_street"
                                               placeholder="Rue" aria-label="Nom de la rue"
                                               maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Rue invalide">
                                    </div>
                                </div>
                                <input type="text" class="form-control mb-2" name="new_delivery_complement"
                                       placeholder="Complément d'adresse" aria-label="Complément d'adresse de livraison"
                                       maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Complément invalide">
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="new_delivery_postal"
                                               placeholder="Code postal" aria-label="Code postal de livraison"
                                               maxlength="10" pattern="[0-9A-Z\s\-]+" title="Code postal invalide">
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" name="new_delivery_city"
                                               id="new_delivery_city" placeholder="Ville" aria-label="Ville de livraison"
                                               maxlength="100" pattern="[a-zA-ZÀ-ÿ\s\-]+" title="Ville invalide">
                                    </div>
                                </div>
                                <input type="text" class="form-control" name="new_delivery_country"
                                       placeholder="Pays" aria-label="Pays de livraison"
                                       maxlength="100" pattern="[a-zA-ZÀ-ÿ\s\-]+" title="Pays invalide">
                            </div>
                        </div>

                        <!-- Adresse de facturation -->
                        <div class="col-md-6 mb-3 d-none" id="billing-block" inert>
                            <h2 class="h5 mb-2">Adresse de facturation</h2>
                            <label for="billing_address_id" class="visually-hidden">Adresse de facturation</label>
                            <select class="form-select" id="billing_address_id" name="billing_address_id"
                                    aria-describedby="billing-address-error">
                                <option value="">Choisir une adresse</option>
                                <?php foreach ($addresses as $addr): ?>
                                    <option value="<?php echo (int)$addr['id']; ?>">
                                        <?php echo htmlspecialchars($addr['name'] . ' - ' . $addr['street'] . ', ' . $addr['city']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="new">+ Ajouter une nouvelle adresse</option>
                            </select>
                            <div id="billing-address-error" class="invalid-feedback" role="alert" aria-live="polite"></div>

                            <div id="new-billing-address" class="d-none mt-3">
                                <input type="text" class="form-control mb-2" name="new_billing_name"
                                       placeholder="Nom de l'adresse" aria-label="Nom de l'adresse de facturation"
                                       maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Nom invalide">
                                <div class="row g-2 mb-2">
                                    <div class="col-3">
                                        <input type="text" class="form-control" name="new_billing_number"
                                               placeholder="N°" aria-label="Numéro de voie facturation"
                                               maxlength="10" pattern="[a-zA-Z0-9\s\-]+" title="Numéro invalide">
                                    </div>
                                    <div class="col-9">
                                        <input type="text" class="form-control" name="new_billing_street"
                                               placeholder="Rue" aria-label="Rue de facturation"
                                               maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Rue invalide">
                                    </div>
                                </div>
                                <input type="text" class="form-control mb-2" name="new_billing_complement"
                                       placeholder="Complément d'adresse" aria-label="Complément adresse facturation"
                                       maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Complément invalide">
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="new_billing_postal"
                                               placeholder="Code postal" aria-label="Code postal facturation"
                                               maxlength="10" pattern="[0-9A-Z\s\-]+" title="Code postal invalide">
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" name="new_billing_city"
                                               placeholder="Ville" aria-label="Ville de facturation"
                                               maxlength="100" pattern="[a-zA-ZÀ-ÿ\s\-]+" title="Ville invalide">
                                    </div>
                                </div>
                                <input type="text" class="form-control" name="new_billing_country"
                                       placeholder="Pays" aria-label="Pays de facturation"
                                       maxlength="100" pattern="[a-zA-ZÀ-ÿ\s\-]+" title="Pays invalide">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Nombre de personnes -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Nombre de personnes</h2>
                    <p class="text-muted">Minimum requis : <strong><?php echo (int)$menu['min_guests']; ?> personnes</strong></p>
                    <p class="text-muted">Réduction de 10% à partir de <strong><?php echo (int)$menu['min_guests'] + 5; ?> personnes</strong></p>
                    <div class="col-md-4">
                        <label for="guest_count" class="form-label">Nombre de personnes <span class="text-danger" aria-hidden="true">*</span></label>
                        <input type="number" class="form-control" id="guest_count" name="guest_count"
                               min="<?php echo (int)$menu['min_guests']; ?>" max="9999"
                               required aria-required="true"
                               aria-describedby="guest-count-error">
                        <div id="guest-count-error" class="invalid-feedback" role="alert" aria-live="polite"></div>
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
                                <span class="badge bg-secondary"
                                      id="avail-<?php echo (int)$r['id']; ?>"
                                      aria-live="polite"
                                      aria-label="Disponibilité de <?php echo htmlspecialchars($r['name']); ?>">
                                    Chargement...
                                </span>
                            </div>

                            <div class="col-md-3">
                                <div class="input-group input-group-sm" role="group"
                                     aria-label="Quantité <?php echo htmlspecialchars($r['name']); ?>">
                                    <button type="button" class="btn btn-outline-secondary btn-minus"
                                            aria-label="Diminuer la quantité de <?php echo htmlspecialchars($r['name']); ?>">−</button>
                                    <input type="number"
                                           class="form-control text-center resource-qty"
                                           name="resources[<?php echo (int)$r['id']; ?>][quantity]"
                                           id="qty-<?php echo (int)$r['id']; ?>"
                                           value="0" min="0"
                                           max="<?php echo (int)$r['total_quantity']; ?>"
                                           aria-label="Quantité de <?php echo htmlspecialchars($r['name']); ?>"
                                           aria-describedby="avail-<?php echo (int)$r['id']; ?>"
                                           data-resource-id="<?php echo (int)$r['id']; ?>">
                                    <button type="button" class="btn btn-outline-secondary btn-plus"
                                            aria-label="Augmenter la quantité de <?php echo htmlspecialchars($r['name']); ?>">+</button>
                                    <input type="hidden" name="resources[<?php echo (int)$r['id']; ?>][unit_price]" value="<?php echo (float)$r['unit_price']; ?>">
                                    <input type="hidden" name="resources[<?php echo (int)$r['id']; ?>][type]" value="<?php echo htmlspecialchars($r['type']); ?>">
                                </div>
                            </div>

                            <?php if (in_array($r['type'], ['MATERIEL_LOURD', 'MATERIEL_LEGER'])): ?>
                            <div class="col-md-2 d-none mt-2" id="return-<?php echo (int)$r['id']; ?>" inert>
                                <label for="return-date-<?php echo (int)$r['id']; ?>" class="form-label small mb-0">
                                    Date retour <span class="text-danger" aria-hidden="true">*</span>
                                </label>
                                <input type="date" class="form-control form-control-sm"
                                       id="return-date-<?php echo (int)$r['id']; ?>"
                                       name="resources[<?php echo (int)$r['id']; ?>][return_date]"
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <?php endif; ?>

                            <?php if ($r['type'] === 'PERSONNEL'): ?>
                            <div class="col-md-4 d-none mt-2" id="personnel-<?php echo (int)$r['id']; ?>" inert>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <label for="date-start-<?php echo (int)$r['id']; ?>" class="form-label small mb-0">
                                            Du <span class="text-danger" aria-hidden="true">*</span>
                                        </label>
                                        <input type="date" class="form-control form-control-sm"
                                               id="date-start-<?php echo (int)$r['id']; ?>"
                                               name="resources[<?php echo (int)$r['id']; ?>][date_start]"
                                               min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="col-6">
                                        <label for="date-end-<?php echo (int)$r['id']; ?>" class="form-label small mb-0">
                                            Au <span class="text-danger" aria-hidden="true">*</span>
                                        </label>
                                        <input type="date" class="form-control form-control-sm"
                                               id="date-end-<?php echo (int)$r['id']; ?>"
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

            <!-- Conditions (toujours affichées) -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Conditions</h2>
                    <?php if (!empty($conditions)): ?>
                    <ul class="mb-3">
                        <?php foreach ($conditions as $condition): ?>
                            <li><?php echo htmlspecialchars($condition['description']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p class="text-muted small">Aucune condition particulière associée à ce menu.</p>
                    <?php endif; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="accept_conditions" name="accept_conditions"
                               required aria-required="true">
                        <label class="form-check-label" for="accept_conditions">
                            J'ai pris connaissance des conditions et je m'engage à les respecter le jour de la prestation
                            <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Informations complémentaires -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Informations complémentaires</h2>
                    <label for="detail" class="form-label">Précisions sur votre commande</label>
                    <textarea class="form-control" id="detail" name="detail" rows="3" maxlength="2000"
                              placeholder="Précisions, besoins particuliers, ressources souhaitées non disponibles..."></textarea>
                </div>
            </div>

            <!-- Récapitulatif prix -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Récapitulatif du prix</h2>
                    <table class="table table-sm" aria-label="Récapitulatif du prix de la commande">
                        <tbody>
                        <tr>
                            <td>Prix par personne</td>
                            <td class="text-end"><?php echo number_format((float)$menu['price_per_person'], 2, ',', ' '); ?>€</td>
                        </tr>
                        <tr>
                            <td>Nombre de personnes</td>
                            <td class="text-end" id="recap-guests" aria-live="polite">-</td>
                        </tr>
                        <tr>
                            <td>Prix menu (brut)</td>
                            <td class="text-end" id="recap-menu-brut" aria-live="polite">-</td>
                        </tr>
                        <tr id="recap-discount-row" class="d-none">
                            <td>Réduction 10%</td>
                            <td class="text-end text-success" id="recap-discount" aria-live="polite">-</td>
                        </tr>
                        <tr id="recap-options-row">
                            <td>Options / Matériel</td>
                            <td class="text-end" id="recap-options" aria-live="polite">0.00€</td>
                        </tr>
                        <tr>
                            <td>Frais de livraison</td>
                            <td class="text-end" id="recap-delivery" aria-live="polite">-</td>
                        </tr>
                        <tr class="fw-bold border-top">
                            <td>Total</td>
                            <td class="text-end" id="recap-total" aria-live="polite">-</td>
                        </tr>
                        </tbody>
                    </table>
                    <input type="hidden" name="total_price" id="total_price">
                    <input type="hidden" name="menu_price" id="menu_price_hidden">
                    <input type="hidden" name="option_price" id="option_price_hidden">
                    <input type="hidden" name="delivery_price" id="delivery_price_hidden">
                    <input type="hidden" name="discount" id="discount_hidden" value="0">
                </div>
            </div>

            <!-- CGV -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="accept_cgv" name="accept_cgv"
                               required aria-required="true">
                        <label class="form-check-label" for="accept_cgv">
                            J'accepte les <a href="/cgv" target="_blank" rel="noopener noreferrer">Conditions Générales de Vente</a>
                            <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Bouton -->
            <div class="d-flex justify-content-center mb-5">
                <button type="submit" id="btn-submit" class="btn btn-primary px-5"
                        disabled aria-disabled="true"
                        aria-describedby="submit-info">
                    Valider la commande
                </button>
            </div>
            <p class="text-center text-muted small" id="submit-info">
                Le bouton se déverrouille une fois les conditions et les CGV acceptées.
            </p>

        </form>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
    <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>