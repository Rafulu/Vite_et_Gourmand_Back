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
                        <div class="col-12 col-md-6">
                            <label class="form-label">Fourchette de prix : <span id="price-display">0€ - 200€</span></label>
                            <div id="price-slider" class="mt-2 mb-2"></div>
                            <div class="d-flex gap-2 mt-2">
                                <input type="number" id="min_price_input" class="form-control form-control-sm" placeholder="Min" min="0" max="200">
                                <input type="number" id="max_price_input" class="form-control form-control-sm" placeholder="Max" min="0" max="200">
                            </div>
                            <input type="hidden" id="min_price" value="0">
                            <input type="hidden" id="max_price" value="200">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Nombre de personnes</label>
                            <input type="number" class="form-control" id="min_guests" placeholder="Min">
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
                        <div class="col-6 col-md-3">
                            <label for="theme_id" class="form-label">Thème</label>
                            <select class="form-select" id="theme_id">
                                <option value="">Tous</option>
                                <?php foreach ($themes as $theme): ?>
                                    <option value="<?php echo $theme['id']; ?>">
                                        <?php echo htmlspecialchars($theme['name']); ?>
                                    </option>
                                <?php endforeach; ?>
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

    <?php require_once __DIR__ . '/partials/scripts.php'; ?>
</body>
</html>