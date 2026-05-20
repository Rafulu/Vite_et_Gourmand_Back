<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Espace employé - Vite & Gourmand';
    $description = 'Tableau de bord employé';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1>Espace employé</h1>

    <div class="row mt-4">
        <div class="col-12 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title h5">Commandes</h2>
                    <p>Gérer et suivre les commandes en cours.</p>
                    <a href="/employee/orders" class="btn btn-primary">Voir les commandes</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title h5">Avis clients</h2>
                    <p>Valider ou refuser les avis déposés.</p>
                    <a href="/employee/reviews" class="btn btn-primary">Voir les avis</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title h5">Avis clients</h2>
                    <p>Valider ou refuser les avis déposés.</p>
                    <a href="/employee/reviews" class="btn btn-primary">Voir les avis</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>