<nav class="navbar navbar-expand-lg">
    <div class="container">
        <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navMenu"
                aria-controls="navMenu" 
                aria-expanded="false" 
                aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand d-flex flex-column" href="/">
            <span>Vite &amp; Gourmand</span>
            <small>l'expérience au service du goût</small>
        </a>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="/">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="/menus">Menus</a></li>
                <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
            </ul>
            <div class="d-flex">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/account" class="btn btn-outline-primary me-2">Mon compte</a>
                    <a href="/logout" class="btn btn-primary">Déconnexion</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline-primary me-2">Se connecter</a>
                    <a href="/register" class="btn btn-primary">S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>