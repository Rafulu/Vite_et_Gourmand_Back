<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Accueil - Vite & Gourmand';
    $description = 'Vite & Gourmand - Service traiteur de qualité à Bordeaux';
    require_once __DIR__ . '/partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<main>
    <!-- Présentation -->
    <section class="container my-5">
        <h2>Présentation de l'entreprise</h2>

        <div class="row align-items-center mb-4">
            <div class="col-5">
                <img src="/assets/images/photo1.jpg" alt="Notre cuisine" class="img-fluid rounded">
            </div>
            <div class="col-7">
                <h3>Notre Histoire</h3>
                <p>Depuis 25 ans à Bordeaux, Vite & Gourmand propose ses prestations pour tout événement au travers de menus en constante évolution.</p>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-7 order-1">
                <h3>Notre Philosophie</h3>
                <p>Des produits frais, une cuisine généreuse et un service à la hauteur de vos attentes.</p>
            </div>
            <div class="col-5 order-2">
                <img src="/assets/images/photo2.jpg" alt="Notre philosophie" class="img-fluid rounded">
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-5">
                <img src="/assets/images/photo3.jpg" alt="Notre équipe" class="img-fluid rounded">
            </div>
            <div class="col-7">
                <h3>Notre Engagement</h3>
                <p>Julie et José s'engagent à vous offrir une expérience culinaire mémorable pour chaque événement.</p>
            </div>
        </div>
    </section>

    <!-- Équipe -->
    <section class="container my-5">
        <h2>Notre Équipe</h2>
        <div class="row justify-content-center">
            <div class="col-6 col-md-3 text-center">
                <img src="/assets/images/equipe1.jpg" alt="Julie" class="img-fluid rounded-circle mb-2">
                <p>Julie — Fondatrice</p>
            </div>
            <div class="col-6 col-md-3 text-center">
                <img src="/assets/images/equipe2.jpg" alt="José" class="img-fluid rounded-circle mb-2">
                <p>José — Chef cuisinier</p>
            </div>
        </div>
    </section>

    <!-- Avis validés -->
    <section class="container my-5">
        <h2>Avis Vérifiés</h2>

        <?php if (!empty($validatedReviews)): ?>
        <div id="avisCarousel" class="carousel slide" data-bs-ride="carousel">

            <div class="carousel-indicators">
                <?php foreach (array_chunk($validatedReviews, 3) as $i => $chunk): ?>
                    <button type="button" data-bs-target="#avisCarousel" data-bs-slide-to="<?= $i ?>"
                        <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?>
                        aria-label="Avis <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>

            <div class="carousel-inner">
                <?php foreach (array_chunk($validatedReviews, 3) as $i => $chunk): ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                        <div class="row justify-content-center">
                            <?php foreach ($chunk as $avis): ?>
                                <div class="col-6 col-md-4 text-center">
                                    <p><strong><?= htmlspecialchars($avis['first_name'] . ' ' . mb_substr($avis['last_name'], 0, 1)) ?>.</strong></p>
                                    <p><em><?= htmlspecialchars($avis['menu_name']) ?></em></p>
                                    <p><?= str_repeat('⭐', $avis['note']) ?></p>
                                    <p><?= htmlspecialchars($avis['comment']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#avisCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Précédent</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#avisCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Suivant</span>
            </button>

        </div>
        <?php else: ?>
            <p>Aucun avis pour le moment.</p>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<?php require_once __DIR__ . '/partials/scripts.php'; ?>

</body>
</html>