<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = htmlspecialchars($menu['name']) . ' - Vite & Gourmand';
    $description = htmlspecialchars($menu['description']);
    require_once __DIR__ . '/partials/head.php';
    ?>
</head>
<body>

    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <main class="container my-5">
        <div class="row">
            
            <!-- Image et infos principales -->
            <div class="col-12 col-md-5 mb-4">
                <img src="/assets/images/menu-default.jpg" 
                     alt="<?php echo htmlspecialchars($menu['name']); ?>"
                     class="img-fluid rounded">
            </div>

            <div class="col-12 col-md-7">
                <h1><?php echo htmlspecialchars($menu['name']); ?>
                    <?php if ($menu['theme_name']): ?>
                        <small class="text-muted">- <?php echo htmlspecialchars($menu['theme_name']); ?></small>
                    <?php endif; ?>
                </h1>

                <p class="fs-4 fw-bold"><?php echo $menu['price_per_person']; ?>€ / personne</p>
                <p>Minimum <?php echo $menu['min_guests']; ?> personnes</p>
                <p><?php echo htmlspecialchars($menu['description']); ?></p>

                <?php if ($menu['allergens']): ?>
                <p><strong>Allergènes :</strong> <?php echo htmlspecialchars($menu['allergens']); ?></p>
                <?php endif; ?>

                <!-- Vérification disponibilité -->
                <div class="card p-3 mt-3">
                    <h2 class="h5">Vérifier la disponibilité</h2>
                    <div class="d-flex gap-2">
                        <input type="date" id="delivery_date" class="form-control">
                        <input type="number" id="guest_count" class="form-control" placeholder="Nb personnes" min="<?php echo $menu['min_guests']; ?>">
                        <button class="btn btn-primary" id="btnVerifier">Vérifier</button>
                    </div>
                    <div id="disponibilite" class="mt-2"></div>
                </div>

                <!-- Bouton commander -->
                <a href="/order/<?php echo $menu['id']; ?>" class="btn btn-primary mt-3 w-100">
                    Commander ce menu
                </a>
            </div>
        </div>

        <!-- Liste des plats -->
        <div class="row mt-5">
            <h2>Composition du menu</h2>
            <?php foreach ($dishes as $dish): ?>
            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="badge bg-secondary"><?php echo $dish['category']; ?></span>
                        <h3 class="card-title h6 mt-2"><?php echo htmlspecialchars($dish['name']); ?></h3>
                        <p class="small text-muted"><?php echo $dish['diet']; ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    <?php require_once __DIR__ . '/partials/scripts.php'; ?>
</body>
</html>