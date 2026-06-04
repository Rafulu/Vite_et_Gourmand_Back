<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Mes commandes - Vite & Gourmand';
    $description = 'Historique de vos commandes';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1>Mes commandes</h1>
    <a href="/account" class="btn btn-secondary mb-4">← Mon compte</a>

    <?php if (empty($orders)): ?>
        <p>Vous n'avez pas encore de commande.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Numéro</th>
                        <th>Date</th>
                        <th>Livraison prévue</th>
                        <th>Convives</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($o['order_number']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($o['order_date'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($o['delivery_date'])); ?></td>
                        <td><?php echo (int)$o['guest_count']; ?></td>
                        <td><?php echo htmlspecialchars($o['status']); ?></td>
                        <td>
                            <a href="/orders/<?php echo $o['id']; ?>" class="btn btn-sm btn-primary">Voir</a>
                            <?php if ($o['status'] === 'EN_ATTENTE'): ?>
                                <a href="/orders/<?php echo $o['id']; ?>/edit" class="btn btn-sm btn-warning ms-1">Modifier</a>
                                <form method="POST" action="/orders/<?php echo $o['id']; ?>/cancel" class="d-inline ms-1"
                                      onsubmit="return confirm('Confirmer l\'annulation ?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Annuler</button>
                                </form>
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