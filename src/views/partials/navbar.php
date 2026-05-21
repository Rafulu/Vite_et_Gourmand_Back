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
                <?php
                $role = $_SESSION['role_id'] ?? null;
                if ($role === 1): ?>
                    <!-- ADMIN -->
                    <li class="nav-item"><a class="nav-link" href="/admin">Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="/employee/orders">Commandes</a></li>
                    <li class="nav-item"><a class="nav-link" href="/employee/reviews">Avis</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/employees">Employés</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/stats">Statistiques</a></li>
                <?php elseif (in_array($role, [2, 3, 4, 6])): ?>
                    <!-- EMPLOYE -->
                    <li class="nav-item"><a class="nav-link" href="/employee">Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="/employee/orders">Commandes</a></li>
                    <?php if (in_array($role, [2, 6])): ?>
                    <li class="nav-item"><a class="nav-link" href="/employee/reviews">Avis</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- CLIENT / PUBLIC -->
                    <li class="nav-item"><a class="nav-link" href="/">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="/menus">Menus</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($role === 5): ?>
                        <a href="/account" class="btn btn-outline-primary me-2">Mon compte</a>
                    <?php else: ?>
                        <span class="btn btn-outline-secondary me-2 disabled">
                            <?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>
                        </span>
                    <?php endif; ?>
                    <a href="/logout" class="btn btn-primary">Déconnexion</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline-primary me-2">Se connecter</a>
                    <a href="/register" class="btn btn-primary">S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>