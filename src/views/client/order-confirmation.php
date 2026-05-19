<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Confirmation de commande - Vite & Gourmand';
    $description = 'Votre commande a bien été enregistrée';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">

                <div class="alert alert-success text-center">
                    <h1 class="h4">Commande enregistrée !</h1>
                    <p class="mb-0">Vous allez recevoir un email de confirmation.</p>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="h5">Récapitulatif</h2>
                        <table class="table">
                            <tr>
                                <td>Numéro de commande</td>
                                <td><?php echo htmlspecialchars($orderData['order_number']); ?></td>
                            </tr>
                            <tr>
                                <td>Menu</td>
                                <td><?php echo htmlspecialchars($menuData['name']); ?></td>
                            </tr>
                            <tr>
                                <td>Date de livraison</td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($orderData['delivery_date']))); ?></td>
                            </tr>
                            <tr>
                                <td>Nombre de personnes</td>
                                <td><?php echo htmlspecialchars($orderData['guest_count']); ?></td>
                            </tr>
                            <tr>
                                <td>Total</td>
                                <td><?php echo htmlspecialchars($orderData['total_price']); ?>€</td>
                            </tr>
                            <tr>
                                <td>Statut</td>
                                <td><?php echo htmlspecialchars($orderData['status']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="text-center">
                    <a href="/account" class="btn btn-primary">Voir mes commandes</a>
                    <a href="/menus" class="btn btn-outline-secondary ms-2">Retour aux menus</a>
                </div>

            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
    <?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>