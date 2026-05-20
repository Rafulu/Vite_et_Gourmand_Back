<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Mes avis - Vite & Gourmand';
    $description = 'Vos avis sur vos commandes';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1>Mes avis</h1>
    <a href="/account" class="btn btn-secondary mb-4">← Mon compte</a>

    <?php if (empty($reviews)): ?>
        <p>Vous n'avez pas encore déposé d'avis.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Commande</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Date</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($r['note']); ?>/5</td>
                            <td><?php echo htmlspecialchars($r['comment']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($r['created_at'])); ?></td>
                            <td>
                                <?php if ($r['is_validated']): ?>
                                    <span class="badge bg-success">Validé</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">En attente</span>
                                <?php endif; ?>
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