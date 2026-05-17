<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $title = 'Créer un compte - Vite & Gourmand';
    $description = 'Créez votre compte Vite & Gourmand';
    require_once __DIR__ . '/../partials/head.php'; 
    ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                
                <h1 class="text-center mb-4">Créer un compte</h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="/register" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required aria-required="true" autocomplete="given-name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required aria-required="true" autocomplete="family-name">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required aria-required="true" autocomplete="email">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Numéro GSM</label>
                        <input type="tel" class="form-control" id="phone" name="phone" autocomplete="tel">
                    </div>

                    <hr>
                    <h2 class="h5 mb-3">Adresse postale</h2>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="number" class="form-label">Numéro</label>
                            <input type="text" class="form-control" id="number" name="number">
                        </div>
                        <div class="col-md-9 mb-3">
                            <label for="street" class="form-label">Rue</label>
                            <input type="text" class="form-control" id="street" name="street" required aria-required="true">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="complement" class="form-label">Complément d'adresse</label>
                        <input type="text" class="form-control" id="complement" name="complement">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="postal_code" class="form-label">Code postal</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required aria-required="true">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="city" class="form-label">Ville</label>
                            <input type="text" class="form-control" id="city" name="city" required aria-required="true">
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required aria-required="true" autocomplete="new-password">
                        <small class="form-text text-muted">14 caractères minimum, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Créer mon compte</button>

                    <p class="text-center mt-3">
                        Déjà un compte ? 
                        <a href="/login">Se connecter</a>
                    </p>

                </form>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>

    <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>