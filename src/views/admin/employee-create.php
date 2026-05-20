<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Créer un employé - Vite & Gourmand';
    $description = 'Création d\'un compte employé';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1>Créer un employé</h1>
    <a href="/admin/employees" class="btn btn-secondary mb-4">← Retour</a>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="/admin/employees/create">
                <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                <div class="mb-3">
                    <label for="first_name" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="first_name" name="first_name"
                        value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                        required maxlength="100">
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="last_name" name="last_name"
                        value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                        required maxlength="100">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required maxlength="255">
                </div>
                <div class="mb-3">
                    <label for="role_id" class="form-label">Rôle</label>
                    <select class="form-select" id="role_id" name="role_id" required>
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo ($_POST['role_id'] ?? '') == $role['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p class="text-muted">Un mot de passe temporaire sera généré et envoyé par email à l'employé.</p>
                <button type="submit" class="btn btn-success">Créer le compte</button>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>