<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Contact - Vite & Gourmand';
    $description = 'Contactez Vite & Gourmand pour toute demande';
    require_once __DIR__ . '/partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<main class="container my-5" id="main-content">
    <h1>Contactez-nous</h1>

    <?php if (isset($success)): ?>
        <div class="alert alert-success" role="alert">Votre message a bien été envoyé.</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12 col-md-6">
            <form method="POST" action="/contact" novalidate>
                <input type="hidden" name="csrf_token" value="<?= SecurityHelper::generateCsrfToken() ?>">

                <div class="mb-3">
                    <label for="subject" class="form-label">Titre <span aria-hidden="true">*</span></label>
                    <input type="text"
                           class="form-control"
                           id="subject"
                           name="subject"
                           required
                           aria-required="true"
                           aria-describedby="subject-help"
                           maxlength="150"
                           pattern=".{3,150}"
                           autocomplete="off"
                           value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                    <div id="subject-help" class="form-text">Entre 3 et 150 caractères.</div>
                    <div class="invalid-feedback">Veuillez saisir un titre (3 à 150 caractères).</div>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Description <span aria-hidden="true">*</span></label>
                    <textarea class="form-control"
                              id="message"
                              name="message"
                              rows="5"
                              required
                              aria-required="true"
                              aria-describedby="message-help"
                              maxlength="2000"
                              minlength="10"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    <div id="message-help" class="form-text">Entre 10 et 2000 caractères.</div>
                    <div class="invalid-feedback">Veuillez saisir une description (10 à 2000 caractères).</div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Votre email <span aria-hidden="true">*</span></label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           required
                           aria-required="true"
                           aria-describedby="email-help"
                           maxlength="255"
                           autocomplete="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <div id="email-help" class="form-text">Nous répondrons à cette adresse.</div>
                    <div class="invalid-feedback">Veuillez saisir un email valide.</div>
                </div>

                <button type="submit" class="btn btn-primary">Envoyer</button>
            </form>
        </div>
    </div>
</main>

<script>
(function () {
    'use strict';
    const form = document.querySelector('form');
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
})();
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<?php require_once __DIR__ . '/partials/scripts.php'; ?>

</body>
</html>