<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $title = 'Mon compte - Vite & Gourmand';
    $description = 'Votre espace personnel Vite & Gourmand';
    require_once __DIR__ . '/../partials/head.php'; 
    ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <main class="container my-5">
        <h1>Mon compte</h1>
        
        <div class="row">
            <!-- Informations personnelles -->
            <div class="col-12 col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title h5">Mes informations</h2>
                        <p>Prénom : <?php echo $_SESSION['first_name'] ?? ''; ?></p>
                        <p>Email : <?php echo $_SESSION['email'] ?? ''; ?></p>
                        <a href="/account/edit" class="btn btn-primary">Modifier</a>
                    </div>
                </div>
            </div>

            <!-- Mes commandes -->
            <div class="col-12 col-md-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title h5">Mes commandes</h2>
                        <a href="/orders" class="btn btn-primary">Voir mes commandes</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>

    <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>