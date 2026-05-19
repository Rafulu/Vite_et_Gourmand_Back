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
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="/orders" method="POST" id="order-form">
            <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
            <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
            <input type="hidden" name="menu_price_per_person" value="<?php echo $menu['price_per_person']; ?>">
            <input type="hidden" name="min_guests" value="<?php echo $menu['min_guests']; ?>">

            <!-- Informations client -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Vos informations</h2>
                    <div class="row">
                        <div class="col">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>" placeholder="Prénom" aria-label="Prénom">
                        </div>
                        <div class="col">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['last_name'] ?? ''); ?>" placeholder="Nom" aria-label="Nom">
                        </div>
                        <div class="col">
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" placeholder="email" aria-label="Email">
                        </div>
                        <div class="col">
                            <input type="tel" class="form-control" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>" placeholder="Téléphone" arial-label="Téléphone">
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
                            <label for="delivery_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="delivery_date" name="delivery_date" required aria-required="true" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="delivery_time" class="form-label">Heure</label>
                            <input type="time" class="form-control" id="delivery_time" name="delivery_time" required aria-required="true" step="900" list="time-options">
                            <datalist id="time-options">
                                <?php
                                for ($h = 0; $h < 24; $h++) {
                                    foreach ([0, 15, 30, 45] as $m) {
                                        printf('<option value="%02d:%02d">', $h, $m);
                                    }
                                }
                                ?>
                            </datalist>
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
                                <h2 class="h5 mb-0">Adresse de livraison</h2>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="same_address" checked>
                                    <label class="form-check-label small" for="same_address">Facturation identique</label>
                                </div>
                            </div>
                            <select class="form-select" id="delivery_address_id" name="delivery_address_id" required aria-required="true">
                                <option value="">Choisir une adresse</option>
                                <?php foreach ($addresses as $addr): ?>
                                    <option value="<?php echo $addr['id']; ?>" data-city="<?php echo htmlspecialchars($addr['city']); ?>">
                                        <?php echo htmlspecialchars($addr['name'] . ' - ' . $addr['number'] . ' ' . $addr['street'] . ', ' . $addr['postal_code'] . ' ' . $addr['city']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="new">+ Ajouter une nouvelle adresse</option>
                            </select>
                            <div id="new-delivery-address" class="d-none mt-3">
                                <input type="text" class="form-control mb-2" name="new_delivery_name" placeholder="Nom de l'adresse"
                                    maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Nom invalide">
                                <div class="row g-2 mb-2">
                                    <div class="col-3">
                                        <input type="text" class="form-control" name="new_delivery_number" placeholder="N°"
                                            maxlength="10" pattern="[a-zA-Z0-9\s\-]+" title="Numéro invalide">
                                    </div>
                                    <div class="col-9">
                                        <input type="text" class="form-control" name="new_delivery_street" placeholder="Rue"
                                            maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Rue invalide">
                                    </div>
                                </div>
                                <input type="text" class="form-control mb-2" name="new_delivery_complement" placeholder="Complément d'adresse"
                                    maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Complément invalide">
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="new_delivery_postal" placeholder="Code postal"
                                            maxlength="10" pattern="[0-9A-Z\s\-]+" title="Code postal invalide">
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" name="new_delivery_city" placeholder="Ville"
                                            maxlength="100" pattern="[a-zA-ZÀ-ÿ\s\-]+" title="Ville invalide">
                                    </div>
                                </div>
                                <input type="text" class="form-control" name="new_delivery_country" placeholder="Pays"
                                    maxlength="100" pattern="[a-zA-ZÀ-ÿ\s\-]+" title="Pays invalide">
                            </div>
                        </div>

                        <!-- Adresse de facturation -->
                        <div class="col-md-6 mb-3 d-none" id="billing-block">
                            <h2 class="h5 mb-2">Adresse de facturation</h2>
                            <select class="form-select" id="billing_address_id" name="billing_address_id">
                                <option value="">Choisir une adresse</option>
                                <?php foreach ($addresses as $addr): ?>
                                    <option value="<?php echo $addr['id']; ?>">
                                        <?php echo htmlspecialchars($addr['name'] . ' - ' . $addr['street'] . ', ' . $addr['city']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="new">+ Ajouter une nouvelle adresse</option>
                            </select>
                            <div id="new-billing-address" class="d-none mt-3">
                                <input type="text" class="form-control mb-2" name="new_billing_name" placeholder="Nom de l'adresse"
                                    maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Nom invalide">
                                <div class="row g-2 mb-2">
                                    <div class="col-3">
                                        <input type="text" class="form-control" name="new_billing_number" placeholder="N°"
                                            maxlength="10" pattern="[a-zA-Z0-9\s\-]+" title="Numéro invalide">
                                    </div>
                                    <div class="col-9">
                                        <input type="text" class="form-control" name="new_billing_street" placeholder="Rue"
                                            maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Rue invalide">
                                    </div>
                                </div>
                                <input type="text" class="form-control mb-2" name="new_billing_complement" placeholder="Complément d'adresse"
                                    maxlength="100" pattern="[a-zA-ZÀ-ÿ0-9\s\-,']+" title="Complément invalide">
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <input type="text" class="form-control" name="new_billing_postal" placeholder="Code postal"
                                            maxlength="10" pattern="[0-9A-Z\s\-]+" title="Code postal invalide">
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" name="new_billing_city" placeholder="Ville"
                                            maxlength="100" pattern="[a-zA-ZÀ-ÿ\s\-]+" title="Ville invalide">
                                    </div>
                                </div>
                                <input type="text" class="form-control" name="new_billing_country" placeholder="Pays"
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
                    <p class="text-muted">Minimum requis : <strong><?php echo $menu['min_guests']; ?> personnes</strong></p>
                    <p class="text-muted">Réduction de 10% à partir de <strong><?php echo $menu['min_guests'] + 5; ?> personnes</strong></p>
                    <div class="col-md-4">
                        <label for="guest_count" class="form-label">Nombre de personnes</label>
                        <input type="number" class="form-control" id="guest_count" name="guest_count" 
                               min="<?php echo $menu['min_guests']; ?>" required aria-required="true">
                    </div>
                </div>
            </div>

            <!--  Conditions du menu -->
            <?php if (!empty($conditions)): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Conditions du menu</h2>
                    <ul>
                        <?php foreach ($conditions as $condition): ?>
                            <li><?php echo htmlspecialchars($condition['description']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="accept_conditions" name="accept_conditions" required aria-required="true">
                        <label class="form-check-label" for="accept_conditions">
                            J'ai pris connaissance des conditions de ce menu et je les accepte
                        </label>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Informations complémentaires -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Informations complémentaires</h2>
                    <label for="detail" class="form-label">Précisions sur votre commande</label>
                    <textarea class="form-control" id="detail" name="detail" rows="3"></textarea>
                </div>
            </div>

            <!-- Récapitulatif prix -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5">Récapitulatif du prix</h2>
                    <table class="table">
                        <tr>
                            <td>Prix par personne</td>
                            <td><?php echo $menu['price_per_person']; ?>€</td>
                        </tr>
                        <tr>
                            <td>Nombre de personnes</td>
                            <td id="recap-guests">-</td>
                        </tr>
                        <tr>
                            <td>Prix menu</td>
                            <td id="recap-menu-price">-</td>
                        </tr>
                        <tr id="recap-discount-row" class="d-none">
                            <td>Réduction 10%</td>
                            <td id="recap-discount" class="text-success">-</td>
                        </tr>
                        <tr>
                            <td>Frais de livraison</td>
                            <td id="recap-delivery">-</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td id="recap-total">-</td>
                        </tr>
                    </table>
                    <input type="hidden" name="total_price" id="total_price">
                    <input type="hidden" name="menu_price" id="menu_price_hidden">
                    <input type="hidden" name="delivery_price" id="delivery_price_hidden">
                    <input type="hidden" name="discount" id="discount_hidden" value="0">
                </div>
            </div>

            <button type="submit" class="position-absolute bottom-10 start-50 translate-middle btn btn-primary w-80">Valider la commande</button>
        </form>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
    <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>