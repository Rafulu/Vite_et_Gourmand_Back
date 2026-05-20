<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Validation des avis - Vite & Gourmand';
    $description = 'Validation des avis clients';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1>Validation des avis</h1>
    <a href="/employee" class="btn btn-secondary mb-4">← Tableau de bord</a>

    <?php if (empty($reviews)): ?>
        <p>Aucun avis en attente de validation.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Client</th>
                    <th>Commande</th>
                    <th>Note</th>
                    <th>Commentaire</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['order_number']); ?></td>
                    <td><?php echo htmlspecialchars($r['note']); ?>/5</td>
                    <td><?php echo htmlspecialchars($r['comment']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($r['created_at'])); ?></td>
                    <td>
                        <form method="POST" action="/employee/reviews/<?php echo $r['id']; ?>/validate" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                            <button type="submit" class="btn btn-sm btn-success">Valider</button>
                        </form>
                        <form method="POST" action="/employee/reviews/<?php echo $r['id']; ?>/reject" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Refuser</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>