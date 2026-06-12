<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = isset($menuData) ? 'Modifier un menu - Vite & Gourmand' : 'Créer un menu - Vite & Gourmand';
    $description = 'Formulaire menu employé';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1><?php echo isset($menuData) ? 'Modifier le menu' : 'Créer un menu'; ?></h1>
    <a href="/employee/menus" class="btn btn-secondary mb-4">← Retour</a>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert" aria-live="polite"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo isset($menuData) ? '/employee/menus/' . $menuData['id'] . '/edit' : '/employee/menus/create'; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">

        <div class="mb-3">
            <label for="title" class="form-label">Titre <span aria-hidden="true">*</span></label>
            <input type="text" class="form-control" id="title" name="title" required
                value="<?php echo htmlspecialchars($menuData['title'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($menuData['description'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="theme_id" class="form-label">Thème</label>
            <select class="form-select" id="theme_id" name="theme_id">
                <option value="">— Aucun —</option>
                <?php foreach ($themes as $t): ?>
                    <option value="<?php echo $t['id']; ?>" <?php echo ($menuData['theme_id'] ?? '') == $t['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="min_guests" class="form-label">Convives minimum <span aria-hidden="true">*</span></label>
                <input type="number" class="form-control" id="min_guests" name="min_guests" min="1" required
                    value="<?php echo htmlspecialchars($menuData['min_guests'] ?? ''); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="price_per_person" class="form-label">Prix par personne (€) <span aria-hidden="true">*</span></label>
                <input type="number" class="form-control" id="price_per_person" name="price_per_person" min="0" step="0.01" required
                    value="<?php echo htmlspecialchars($menuData['price_per_person'] ?? ''); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" min="0"
                    value="<?php echo htmlspecialchars($menuData['stock'] ?? ''); ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="conditions" class="form-label">Conditions</label>
            <textarea class="form-control" id="conditions" name="conditions" rows="2"><?php echo htmlspecialchars($menuData['conditions'] ?? ''); ?></textarea>
        </div>

        <div class="mb-4">
            <label class="form-label">Plats associés</label>
            <?php
            $linkedIds = isset($dishes) ? array_column($dishes, 'id') : [];
            foreach ($allDishes as $d):
            ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="dish_ids[]"
                    id="dish_<?php echo $d['id']; ?>"
                    value="<?php echo $d['id']; ?>"
                    <?php echo in_array($d['id'], $linkedIds) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="dish_<?php echo $d['id']; ?>">
                    <?php echo htmlspecialchars($d['name']); ?> — <?php echo htmlspecialchars($d['category']); ?>
                </label>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-success">
            <?php echo isset($menuData) ? 'Enregistrer les modifications' : 'Créer le menu'; ?>
        </button>
    </form>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>