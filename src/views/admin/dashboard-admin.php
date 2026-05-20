<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Espace administrateur - Vite & Gourmand';
    $description = 'Tableau de bord administrateur';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1>Espace administrateur</h1>

    <div class="row mt-4">
        <div class="col-12 col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title h5">Commandes</h2>
                    <p>Gérer et suivre toutes les commandes.</p>
                    <a href="/employee/orders" class="btn btn-primary">Voir les commandes</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title h5">Avis clients</h2>
                    <p>Valider ou refuser les avis déposés.</p>
                    <a href="/employee/reviews" class="btn btn-primary">Voir les avis</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title h5">Comptes employés</h2>
                    <p>Créer et gérer les comptes employés.</p>
                    <a href="/admin/employees" class="btn btn-primary">Gérer les employés</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title h5">Statistiques</h2>
                    <p>Visualiser les statistiques de l'activité.</p>
                    <a href="/admin/stats" class="btn btn-primary">Voir les stats</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title h5">Menus & Plats</h2>
                    <p>Gérer les menus, plats et horaires.</p>
                    <a href="/employee/menus" class="btn btn-primary">Gérer les menus</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>