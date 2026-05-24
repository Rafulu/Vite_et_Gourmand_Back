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

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['temp_password'])): ?>   // ← ICI
    <div class="alert alert-info">
        Employé créé. Identifiants à transmettre :<br>
        Email : <strong><?php echo htmlspecialchars($_SESSION['temp_email']); ?></strong><br>
        Mot de passe temporaire : <strong><?php echo htmlspecialchars($_SESSION['temp_password']); ?></strong>
    </div>
    <?php unset($_SESSION['temp_password'], $_SESSION['temp_email']); ?>
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
                            <span class="badge bg-danger">Bloquer</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button
                            class="btn btn-sm btn-toggle-employee <?php echo $e['is_blocked'] ? 'btn-warning' : 'btn-success'; ?>"
                            data-id="<?php echo $e['id']; ?>"
                            data-blocked="<?php echo $e['is_blocked'] ? '1' : '0'; ?>"
                            data-csrf="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                            <?php echo $e['is_blocked'] ? 'Bloquer' : 'Débloquer'; ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-toggle-employee');
    if (!btn) return;

    const id   = btn.dataset.id;
    const csrf = btn.dataset.csrf;
    const row  = btn.closest('tr');

    btn.disabled = true;

    fetch(`/admin/employees/${id}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'fetch'
        },
        body: `csrf_token=${encodeURIComponent(csrf)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const isBlocked = btn.dataset.blocked === '1';
            btn.dataset.blocked = isBlocked ? '0' : '1';
            btn.textContent = isBlocked ? 'Débloquer' : 'Bloquer';
            btn.classList.toggle('btn-warning', !isBlocked);
            btn.classList.toggle('btn-success', isBlocked);
            const badge = row.querySelector('.badge');
            badge.textContent = isBlocked ? 'Bloqué' : 'Actif';
            badge.className = 'badge ' + (isBlocked ? 'bg-danger' : 'bg-success');
        }
        btn.disabled = false;
    })
    .catch(() => { btn.disabled = false; });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>