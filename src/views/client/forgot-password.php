<!DOCTYPE html>
<html lang="fr">
<head>
    <?php 
    $title = 'Mot de passe oublié - Vite & Gourmand';
    $description = 'Réinitialisez votre mot de passe';
    require_once __DIR__ . '/../partials/head.php'; 
    ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">

                <h1 class="text-center mb-4">Mot de passe oublié</h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
                <?php else: ?>

                <p class="text-muted text-center mb-4">Saisissez votre adresse email, nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>

                <form action="/forgot-password" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            required 
                            aria-required="true" 
                            autocomplete="email"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Envoyer le lien</button>

                    <p class="text-center mt-3">
                        <a href="/login">Retour à la connexion</a>
                    </p>
                </form>

                <?php endif; ?>

            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
    <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>