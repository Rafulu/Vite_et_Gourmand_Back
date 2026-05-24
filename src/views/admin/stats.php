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

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="filter-period" class="form-label">Période</label>
                    <select class="form-select" id="filter-period">
                        <option value="0">Toutes les dates</option>
                        <option value="7">7 derniers jours</option>
                        <option value="30" selected>30 derniers jours</option>
                        <option value="90">90 derniers jours</option>
                        <option value="365">12 derniers mois</option>
                        <option value="custom">Période personnalisée</option>
                    </select>
                </div>
                <div class="col-md-2 d-none" id="date-start-block">
                    <label for="filter-date-start" class="form-label">Du</label>
                    <input type="date" class="form-control" id="filter-date-start">
                </div>
                <div class="col-md-2 d-none" id="date-end-block">
                    <label for="filter-date-end" class="form-label">Au</label>
                    <input type="date" class="form-control" id="filter-date-end">
                </div>
                <div class="col-md-3">
                    <label for="filter-menu" class="form-label">Menu</label>
                    <select class="form-select" id="filter-menu">
                        <option value="">Tous les menus</option>
                        <?php foreach ($stats as $stat): ?>
                            <option value="<?php echo htmlspecialchars($stat['_id']); ?>">
                                <?php echo htmlspecialchars($stat['_id']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" id="btn-filter-stats">Filtrer</button>
                </div>
            </div>
        </div>
    </div>

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
        <tbody id="stats-tbody">
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

<div id="stats-data" data-stats='<?= json_encode(array_map(fn($s) => [
    'menu' => $s['_id'],
    'nb'   => (int)$s['nb_commandes'],
    'ca'   => (float)$s['ca_total'],
], $stats)) ?>'></div>

<script>
document.getElementById('filter-period').addEventListener('change', function() {
    const isCustom = this.value === 'custom';
    document.getElementById('date-start-block').classList.toggle('d-none', !isCustom);
    document.getElementById('date-end-block').classList.toggle('d-none', !isCustom);
});

document.getElementById('btn-filter-stats').addEventListener('click', function() {
    const period    = document.getElementById('filter-period').value;
    const menu      = document.getElementById('filter-menu').value;
    const dateStart = document.getElementById('filter-date-start').value;
    const dateEnd   = document.getElementById('filter-date-end').value;

    const params = new URLSearchParams();
    if (period !== 'custom') params.set('period', period);
    if (menu) params.set('menu', menu);
    if (period === 'custom' && dateStart) params.set('date_start', dateStart);
    if (period === 'custom' && dateEnd) params.set('date_end', dateEnd);

    fetch('/admin/stats/data?' + params.toString())
        .then(r => r.json())
        .then(data => {
            // Mise à jour tableau
            const tbody = document.getElementById('stats-tbody');
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3">Aucune donnée.</td></tr>';
            } else {
                tbody.innerHTML = data.map(s =>
                    `<tr>
                        <td>${s._id}</td>
                        <td>${s.nb_commandes}</td>
                        <td>${parseFloat(s.ca_total).toFixed(2).replace('.', ',')} €</td>
                    </tr>`
                ).join('');
            }

            // Mise à jour graphiques
            const labels = data.map(s => s._id);
            const nbs    = data.map(s => s.nb_commandes);
            const cas    = data.map(s => parseFloat(s.ca_total));
            chartCommandes.data.labels = labels;
            chartCommandes.data.datasets[0].data = nbs;
            chartCommandes.update();
            chartCA.data.labels = labels;
            chartCA.data.datasets[0].data = cas;
            chartCA.update();
        })
        .catch(e => console.error('Erreur stats:', e));
});
</script>

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