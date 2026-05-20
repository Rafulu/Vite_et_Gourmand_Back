<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Gestion des employés - Vite & Gourmand';
    $description = 'Gestion des comptes employés';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1>Gestion des employés</h1>
    <a href="/admin" class="btn btn-secondary mb-4">← Tableau de bord</a>
    <a href="/admin/employees/create" class="btn btn-success mb-4 ms-2">+ Créer un employé</a>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $e): ?>
                <tr>
                    <td><?php echo htmlspecialchars($e['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($e['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($e['email']); ?></td>
                    <td><?php echo htmlspecialchars($e['role_name']); ?></td>
                    <td>
                        <?php if ($e['is_blocked']): ?>
                            <span class="badge bg-success">Actif</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Bloqué</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="/admin/employees/<?php echo $e['id']; ?>/toggle" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                            <button type="submit" class="btn btn-sm <?php echo $e['is_blocked'] ? 'btn-warning' : 'btn-success'; ?>">
                                <?php echo $e['is_blocked'] ? 'Bloquer' : 'Débloquer'; ?>
                            </button>
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