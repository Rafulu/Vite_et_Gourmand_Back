<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $title = 'Réinitialisation du mot de passe - Vite & Gourmand';
    $description = 'Réinitialisez votre mot de passe Vite & Gourmand';
    require_once __DIR__ . '/../partials/head.php'; 
    ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">

                <h1 class="text-center mb-4">Nouveau mot de passe</h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="/reset-password?token=<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">

                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            required 
                            aria-required="true"
                            autocomplete="new-password"
                            minlength="14"
                        >
                        <div class="form-text">14 caractères minimum, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password_confirm" 
                            name="password_confirm" 
                            required 
                            aria-required="true"
                            autocomplete="new-password"
                            minlength="14"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Réinitialiser le mot de passe</button>

                    <p class="text-center mt-3">
                        <a href="/login">Retour à la connexion</a>
                    </p>
                </form>

            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
    <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>