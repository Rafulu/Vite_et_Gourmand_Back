<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $title = 'Connexion - Vite & Gourmand';
    $description = 'Connectez-vous à votre espace Vite & Gourmand';
    require_once __DIR__ . '/../partials/head.php'; 
    ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                
                <h1 class="text-center mb-4">Connexion</h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="/login" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required aria-required="true" autocomplete="email">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required aria-required="true" autocomplete="current-password">
                    </div>

                    <div class="text-end mb-3">
                        <a href="/forgot-password">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>

                    <p class="text-center mt-3">
                        Pas encore de compte ? 
                        <a href="/register">S'inscrire</a>
                    </p>
                </form>

            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>

    <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>