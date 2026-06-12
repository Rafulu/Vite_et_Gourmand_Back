<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Gestion des menus - Vite & Gourmand';
    $description = 'Gestion des menus employé';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container-fluid my-5">
    <h1>Gestion des menus</h1>
    <a href="<?php echo $_SESSION['role_id'] === 1 ? '/admin' : '/employee'; ?>" class="btn btn-secondary mb-3">← Tableau de bord</a>
    <a href="/employee/menus/create" class="btn btn-success mb-3 ms-2">+ Nouveau menu</a>

    <!-- Filtre actif/inactif -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/employee/menus" class="row g-3">
                <div class="col-md-3">
                    <label for="filter_active" class="form-label">Statut</label>
                    <select class="form-select" id="filter_active" name="filter_active">
                        <option value="">Tous</option>
                        <option value="1" <?php echo ($_GET['filter_active'] ?? '') === '1' ? 'selected' : ''; ?>>Actifs</option>
                        <option value="0" <?php echo ($_GET['filter_active'] ?? '') === '0' ? 'selected' : ''; ?>>Inactifs</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="/employee/menus" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Titre</th>
                    <th>Thème</th>
                    <th>Prix/pers.</th>
                    <th>Min. convives</th>
                    <th>Stock</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $filtered = $menus;
                if (isset($_GET['filter_active']) && $_GET['filter_active'] !== '') {
                    $active = (int)$_GET['filter_active'];
                    $filtered = array_filter($filtered, fn($m) => (int)$m['is_active'] === $active);
                }
                foreach ($filtered as $m):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['title']); ?></td>
                    <td><?php echo htmlspecialchars($m['theme_name'] ?? '—'); ?></td>
                    <td><?php echo number_format($m['price_per_person'], 2, ',', ' '); ?> €</td>
                    <td><?php echo $m['min_guests']; ?></td>
                    <td><?php echo $m['stock']; ?></td>
                    <td>
                        <span class="badge <?php echo $m['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $m['is_active'] ? 'Actif' : 'Inactif'; ?>
                        </span>
                    </td>
                    <td class="d-flex gap-2">
                        <a href="/employee/menus/<?php echo $m['id']; ?>/edit" class="btn btn-sm btn-primary">Modifier</a>
                        <?php if ($m['is_active']): ?>
                        <form method="POST" action="/employee/menus/<?php echo $m['id']; ?>/disable">
                            <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                            <button type="submit" class="btn btn-sm btn-warning">Désactiver</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="/employee/menus/<?php echo $m['id']; ?>/delete" onsubmit="return confirm('Supprimer définitivement ce menu ?');">
                            <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
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