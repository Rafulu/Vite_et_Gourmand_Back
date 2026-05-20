<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Statistiques - Vite & Gourmand';
    $description = 'Statistiques des commandes par menu';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1>Statistiques</h1>

    <div class="row mt-4">
        <div class="col-12 col-md-6 mb-4">
            <canvas id="chartCommandes"></canvas>
        </div>
        <div class="col-12 col-md-6 mb-4">
            <canvas id="chartCA"></canvas>
        </div>
    </div>

    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Menu</th>
                <th>Nb commandes</th>
                <th>CA total (€)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats as $stat): ?>
            <tr>
                <td><?= htmlspecialchars($stat['_id']) ?></td>
                <td><?= (int)$stat['nb_commandes'] ?></td>
                <td><?= number_format((float)$stat['ca_total'], 2, ',', ' ') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<div id="stats-data" data-stats='<?= json_encode(array_map(fn($s) => [
    'menu' => $s['_id'],
    'nb'   => (int)$s['nb_commandes'],
    'ca'   => (float)$s['ca_total'],
], $stats)) ?>'></div>

<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
<script src="/assets/js/stats.js"></script>
</body>
</html>