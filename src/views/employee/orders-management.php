<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Gestion des commandes - Vite & Gourmand';
    $description = 'Gestion des commandes employé';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container-fluid my-5">
    <h1>Gestion des commandes</h1>
    <a href="/employee" class="btn btn-secondary mb-4">← Tableau de bord</a>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/employee/orders" class="row g-3">
                <div class="col-md-3">
                    <label for="filter_status" class="form-label">Statut</label>
                    <select class="form-select" id="filter_status" name="filter_status">
                        <option value="">Tous</option>
                        <option value="EN_ATTENTE" <?php echo ($_GET['filter_status'] ?? '') === 'EN_ATTENTE' ? 'selected' : ''; ?>>En attente</option>
                        <option value="ACCEPTEE" <?php echo ($_GET['filter_status'] ?? '') === 'ACCEPTEE' ? 'selected' : ''; ?>>Acceptée</option>
                        <option value="EN_PREPARATION" <?php echo ($_GET['filter_status'] ?? '') === 'EN_PREPARATION' ? 'selected' : ''; ?>>En préparation</option>
                        <option value="PRET" <?php echo ($_GET['filter_status'] ?? '') === 'PRET' ? 'selected' : ''; ?>>Prêt</option>
                        <option value="EN_LIVRAISON" <?php echo ($_GET['filter_status'] ?? '') === 'EN_LIVRAISON' ? 'selected' : ''; ?>>En livraison</option>
                        <option value="LIVREE" <?php echo ($_GET['filter_status'] ?? '') === 'LIVREE' ? 'selected' : ''; ?>>Livrée</option>
                        <option value="ATTENTE_MATERIEL" <?php echo ($_GET['filter_status'] ?? '') === 'ATTENTE_MATERIEL' ? 'selected' : ''; ?>>Attente matériel</option>
                        <option value="TERMINEE" <?php echo ($_GET['filter_status'] ?? '') === 'TERMINEE' ? 'selected' : ''; ?>>Terminée</option>
                        <option value="ANNULEE" <?php echo ($_GET['filter_status'] ?? '') === 'ANNULEE' ? 'selected' : ''; ?>>Annulée</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_date" class="form-label">Date de livraison</label>
                    <input type="date" class="form-control" id="filter_date" name="filter_date" value="<?php echo htmlspecialchars($_GET['filter_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label for="filter_cook" class="form-label">Cuisinier</label>
                    <select class="form-select" id="filter_cook" name="filter_cook">
                        <option value="">Tous</option>
                        <?php foreach ($cooks as $cook): ?>
                            <option value="<?php echo $cook['id']; ?>" <?php echo ($_GET['filter_cook'] ?? '') == $cook['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cook['first_name'] . ' ' . $cook['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_driver" class="form-label">Livreur</label>
                    <select class="form-select" id="filter_driver" name="filter_driver">
                        <option value="">Tous</option>
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?php echo $driver['id']; ?>" <?php echo ($_GET['filter_driver'] ?? '') == $driver['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="/employee/orders" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Numéro</th>
                    <th>Client</th>
                    <th>Menu</th>
                    <th>Convives</th>
                    <th>Date commande</th>
                    <th>Date livraison</th>
                    <th>Total</th>
                    <th>Cuisinier</th>
                    <th>Livreur</th>
                    <th>Statut</th>
                    <th>Dernière MAJ</th>
                    <th>Par</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $filtered = $orders;

                // Filtre statut
                if (!empty($_GET['filter_status'])) {
                    $filtered = array_filter($filtered, fn($o) => $o['status'] === $_GET['filter_status']);
                }

                // Filtre date livraison
                if (!empty($_GET['filter_date'])) {
                    $filtered = array_filter($filtered, fn($o) => date('Y-m-d', strtotime($o['delivery_date'])) === $_GET['filter_date']);
                }

                // Filtre cuisinier
                if (!empty($_GET['filter_cook'])) {
                    $filtered = array_filter($filtered, fn($o) => $o['cook_id'] == $_GET['filter_cook']);
                }

                // Filtre livreur
                if (!empty($_GET['filter_driver'])) {
                    $filtered = array_filter($filtered, fn($o) => $o['driver_id'] == $_GET['filter_driver']);
                }

                // Filtre par rôle
                foreach ($filtered as $o):
                    $role = $_SESSION['role_id'];
                    $show = false;
                    if (in_array($role, [1, 2, 6])) $show = true;
                    if ($role === 3 && ($o['cook_id'] == $_SESSION['user_id'] || $o['cook_id'] === null) && in_array($o['status'], ['ACCEPTEE', 'EN_PREPARATION', 'PRET'])) $show = true;
                    if ($role === 4 && ($o['driver_id'] == $_SESSION['user_id'] || $o['driver_id'] === null) && in_array($o['status'], ['PRET', 'EN_LIVRAISON', 'LIVREE'])) $show = true;
                    if (!$show) continue;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($o['order_number']); ?></td>
                    <td><?php echo htmlspecialchars($o['client_name']); ?></td>
                    <td><?php echo htmlspecialchars($o['menu_name']); ?></td>
                    <td><?php echo htmlspecialchars($o['guest_count']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($o['order_date'])); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($o['delivery_date'])); ?></td>
                    <td><?php echo number_format($o['total_price'], 2, ',', ' '); ?> €</td>
                    <td><?php echo htmlspecialchars($o['cook_name'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($o['driver_name'] ?? '—'); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($o['status']); ?></span></td>
                    <td><?php echo $o['last_status_change'] ? date('d/m/Y H:i', strtotime($o['last_status_change'])) : '—'; ?></td>
                    <td><?php echo htmlspecialchars($o['last_updated_by'] ?? '—'); ?></td>
                    <td>
                        <a href="/employee/orders/<?php echo $o['id']; ?>" class="btn btn-sm btn-primary">Détails</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>