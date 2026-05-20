<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Déposer un avis - Vite & Gourmand';
    $description = 'Déposez votre avis sur votre commande';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1>Déposer un avis</h1>
    <a href="/my-reviews" class="btn btn-secondary mb-4">← Mes avis</a>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/reviews/submit/<?= (int)$order_id ?>">
        <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">

        <div class="mb-3">
            <label for="note" class="form-label">Note (1 à 5)</label>
            <select name="note" id="note" class="form-select" required>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= isset($_POST['note']) && (int)$_POST['note'] === $i ? 'selected' : '' ?>>
                        <?= $i ?> / 5
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="comment" class="form-label">Commentaire</label>
            <textarea name="comment" id="comment" class="form-control" rows="4" maxlength="1000"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Envoyer mon avis</button>
    </form>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>