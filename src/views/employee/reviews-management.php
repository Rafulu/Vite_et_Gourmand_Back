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
    <a href="<?php echo $_SESSION['role_id'] === 1 ? '/admin' : '/employee'; ?>" class="btn btn-secondary mb-4">← Tableau de bord</a>

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
                        <button 
                            class="btn btn-sm btn-success btn-review-action"
                            data-id="<?php echo $r['id']; ?>"
                            data-action="validate"
                            data-csrf="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                            Valider
                        </button>
                        <button 
                            class="btn btn-sm btn-danger btn-review-action"
                            data-id="<?php echo $r['id']; ?>"
                            data-action="reject"
                            data-csrf="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                            Refuser
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-review-action');
    if (!btn) return;

    const id     = btn.dataset.id;
    const action = btn.dataset.action;
    const csrf   = btn.dataset.csrf;
    const row    = btn.closest('tr');

    btn.disabled = true;

    fetch(`/employee/reviews/${id}/${action}`, {
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
            row.remove();
            const tbody = document.querySelector('tbody');
            if (tbody && tbody.querySelectorAll('tr').length === 0) {
                tbody.closest('.table-responsive').innerHTML = '<p>Aucun avis en attente de validation.</p>';
            }
        }
    })
    .catch(() => { btn.disabled = false; });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>