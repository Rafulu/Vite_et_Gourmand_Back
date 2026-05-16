<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $title = 'Nos Menus - Vite & Gourmand';
    $description = 'Découvrez nos menus traiteur';
    require_once __DIR__ . '/partials/head.php'; 
    ?>
</head>
<body>

    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <main class="container my-5">
        <h1>Nos Menus</h1>

        <!-- Filtres -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card p-3">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Prix minimum</label>
                            <input type="number" class="form-control" id="min_price" placeholder="0">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Prix maximum</label>
                            <input type="number" class="form-control" id="max_price" placeholder="100">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Nombre de personnes</label>
                            <input type="number" class="form-control" id="min_guests" placeholder="10">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Régime</label>
                            <select class="form-select" id="diet">
                                <option value="">Tous</option>
                                <option value="VEGAN">Vegan</option>
                                <option value="VEGETARIEN">Végétarien</option>
                                <option value="CLASSIQUE">Classique</option>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" id="btnFiltrer">Filtrer</button>
                </div>
            </div>
        </div>

        <!-- Liste des menus -->
        <div id="liste-menus">
            <?php foreach ($menus as $m): ?>
            <div class="card mb-3">
                <div class="row g-0">
                    <div class="col-4">
                        <img src="/assets/images/menu-default.jpg" 
                             alt="<?php echo htmlspecialchars($m['name']); ?>" 
                             class="img-fluid rounded-start h-100" 
                             style="object-fit: cover;">
                    </div>
                    <div class="col-8">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h2 class="card-title h5">
                                    <?php echo htmlspecialchars($m['name']); ?>
                                    <?php if ($m['theme_name']): ?>
                                        - <?php echo htmlspecialchars($m['theme_name']); ?>
                                    <?php endif; ?>
                                </h2>
                                <span class="fw-bold"><?php echo $m['price_per_person']; ?>€/pers</span>
                            </div>
                            <p class="text-muted small"><?php echo htmlspecialchars($m['description']); ?></p>
                            
                            <?php if ($m['allergens']): ?>
                            <p class="small">Allergènes : <?php echo htmlspecialchars($m['allergens']); ?></p>
                            <?php endif; ?>

                            <div class="mt-2">
                                <input type="date" class="form-control form-control-sm d-inline w-auto" 
                                       id="date-<?php echo $m['id']; ?>">
                                <a href="/menus/<?php echo $m['id']; ?>" class="btn btn-primary btn-sm ms-2">Détails</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>