<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Gestion des plats - Vite & Gourmand';
    $description = 'Gestion des plats employé';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container-fluid my-5">
    <h1>Gestion des plats</h1>
    <a href="<?php echo $_SESSION['role_id'] === 1 ? '/admin' : '/employee'; ?>" class="btn btn-secondary mb-3">← Tableau de bord</a>
    <a href="/employee/dishes/create" class="btn btn-success mb-3 ms-2">+ Nouveau plat</a>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/employee/dishes" class="row g-3">
                <div class="col-md-3">
                    <label for="filter_active" class="form-label">Statut</label>
                    <select class="form-select" id="filter_active" name="filter_active">
                        <option value="">Tous</option>
                        <option value="1" <?php echo ($_GET['filter_active'] ?? '') === '1' ? 'selected' : ''; ?>>Actifs</option>
                        <option value="0" <?php echo ($_GET['filter_active'] ?? '') === '0' ? 'selected' : ''; ?>>Inactifs</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_category" class="form-label">Catégorie</label>
                    <select class="form-select" id="filter_category" name="filter_category">
                        <option value="">Toutes</option>
                        <option value="ENTREE" <?php echo ($_GET['filter_category'] ?? '') === 'ENTREE' ? 'selected' : ''; ?>>Entrée</option>
                        <option value="PLAT" <?php echo ($_GET['filter_category'] ?? '') === 'PLAT' ? 'selected' : ''; ?>>Plat</option>
                        <option value="DESSERT" <?php echo ($_GET['filter_category'] ?? '') === 'DESSERT' ? 'selected' : ''; ?>>Dessert</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="/employee/dishes" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Régime</th>
                    <th>Temps de préparation (min)</th>
                    <th>Allergènes</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $filtered = $dishes;
                if (isset($_GET['filter_active']) && $_GET['filter_active'] !== '') {
                    $active = (int)$_GET['filter_active'];
                    $filtered = array_filter($filtered, fn($d) => (int)$d['is_active'] === $active);
                }
                if (!empty($_GET['filter_category'])) {
                    $filtered = array_filter($filtered, fn($d) => $d['category'] === $_GET['filter_category']);
                }
                foreach ($filtered as $d):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($d['name']); ?></td>
                    <td><?php echo htmlspecialchars($d['category']); ?></td>
                    <td><?php echo htmlspecialchars($d['diet']); ?></td>
                    <td><?php echo (int)$d['preparation_time']; ?></td>
                    <td><?php echo htmlspecialchars($d['allergens'] ?? '—'); ?></td>
                    <td>
                        <span class="badge <?php echo $d['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $d['is_active'] ? 'Actif' : 'Inactif'; ?>
                        </span>
                    </td>
                    <td class="d-flex gap-2">
                        <a href="/employee/dishes/<?php echo $d['id']; ?>/edit" class="btn btn-sm btn-primary">Modifier</a>
                        <?php if ($d['is_active']): ?>
                        <form method="POST" action="/employee/dishes/<?php echo $d['id']; ?>/disable">
                            <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                            <button type="submit" class="btn btn-sm btn-warning">Désactiver</button>
                        </form>
                        <?php else: ?>
                        <form method="POST" action="/employee/dishes/<?php echo $d['id']; ?>/enable">
                            <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                            <button type="submit" class="btn btn-sm btn-success">Réactiver</button>
                        </form>
                        <?php endif; ?>
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