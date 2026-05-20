<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $title = 'Détail commande - Vite & Gourmand';
    $description = 'Détail commande employé';
    require_once __DIR__ . '/../partials/head.php';
    ?>
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <h1>Commande <?php echo htmlspecialchars($orderData['order_number']); ?></h1>
    <a href="/employee/orders" class="btn btn-secondary mb-4">← Retour aux commandes</a>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row">

        <!-- Infos commande -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><strong>Informations commande</strong></div>
                <div class="card-body">
                    <p><strong>Statut :</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($orderData['status']); ?></span></p>
                    <p><strong>Date commande :</strong> <?php echo date('d/m/Y H:i', strtotime($orderData['order_date'])); ?></p>
                    <p><strong>Date livraison :</strong> <?php echo date('d/m/Y H:i', strtotime($orderData['delivery_date'])); ?></p>
                    <p><strong>Menu :</strong> <?php echo htmlspecialchars($orderData['menu_name']); ?></p>
                    <p><strong>Convives :</strong> <?php echo htmlspecialchars($orderData['guest_count']); ?></p>
                    <p><strong>Prix menu :</strong> <?php echo number_format($orderData['menu_price'], 2, ',', ' '); ?> €</p>
                    <p><strong>Prix option :</strong> <?php echo number_format($orderData['option_price'], 2, ',', ' '); ?> €</p>
                    <p><strong>Prix livraison :</strong> <?php echo number_format($orderData['delivery_price'], 2, ',', ' '); ?> €</p>
                    <p><strong>Remise :</strong> <?php echo $orderData['discount'] ? 'Oui' : 'Non'; ?></p>
                    <p><strong>Total :</strong> <?php echo number_format($orderData['total_price'], 2, ',', ' '); ?> €</p>
                    <p><strong>Matériel prêté :</strong> <?php echo $orderData['equipement_loan'] ? 'Oui' : 'Non'; ?></p>
                    <p><strong>Matériel retourné :</strong> <?php echo $orderData['equipement_return'] ? 'Oui' : 'Non'; ?></p>
                    <?php if (!empty($orderData['detail'])): ?>
                        <p><strong>Détails client :</strong> <?php echo htmlspecialchars($orderData['detail']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Client + Adresses -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><strong>Client & Adresses</strong></div>
                <div class="card-body">
                    <p><strong>Client :</strong> <?php echo htmlspecialchars($orderData['client_name']); ?></p>
                    <p><strong>Email :</strong> <?php echo htmlspecialchars($orderData['client_email']); ?></p>
                    <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($orderData['client_phone'] ?? '—'); ?></p>
                    <hr>
                    <p><strong>Adresse de livraison :</strong><br>
                        <?php echo htmlspecialchars($orderData['delivery_street']); ?><br>
                        <?php echo htmlspecialchars($orderData['delivery_postal_code'] . ' ' . $orderData['delivery_city']); ?>
                    </p>
                    <hr>
                    <p><strong>Adresse de facturation :</strong><br>
                        <?php if ($orderData['billing_street']): ?>
                            <?php echo htmlspecialchars($orderData['billing_street']); ?><br>
                            <?php echo htmlspecialchars($orderData['billing_postal_code'] . ' ' . $orderData['billing_city']); ?>
                        <?php else: ?>
                            Identique à la livraison
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Attribution -->
        <?php if (in_array($_SESSION['role_id'], [1, 2, 6])): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><strong>Attribution</strong></div>
                <div class="card-body">
                    <form method="POST" action="/employee/orders/<?php echo $orderData['id']; ?>/assign">
                        <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                        <div class="mb-3">
                            <label for="cook_id" class="form-label">Cuisinier</label>
                            <select class="form-select" id="cook_id" name="cook_id">
                                <option value="">— Aucun —</option>
                                <?php foreach ($cooks as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $orderData['cook_id'] == $c['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="driver_id" class="form-label">Livreur</label>
                            <select class="form-select" id="driver_id" name="driver_id">
                                <option value="">— Aucun —</option>
                                <?php foreach ($drivers as $d): ?>
                                    <option value="<?php echo $d['id']; ?>" <?php echo $orderData['driver_id'] == $d['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Auto-attribution cuisinier -->
        <?php if ($_SESSION['role_id'] === 3 && $orderData['cook_id'] === null && in_array($orderData['status'], ['ACCEPTEE', 'EN_PREPARATION'])): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><strong>Prendre en charge (cuisine)</strong></div>
                <div class="card-body">
                    <form method="POST" action="/employee/orders/<?php echo $orderData['id']; ?>/self-assign">
                        <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                        <input type="hidden" name="role" value="cook">
                        <button type="submit" class="btn btn-success">M'attribuer cette commande</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Auto-attribution livreur -->
        <?php if ($_SESSION['role_id'] === 4 && $orderData['driver_id'] === null && in_array($orderData['status'], ['PRET', 'EN_LIVRAISON'])): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><strong>Prendre en charge (livraison)</strong></div>
                <div class="card-body">
                    <form method="POST" action="/employee/orders/<?php echo $orderData['id']; ?>/self-assign">
                        <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                        <input type="hidden" name="role" value="driver">
                        <button type="submit" class="btn btn-success">M'attribuer cette commande</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Changement de statut -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><strong>Changer le statut</strong></div>
                <div class="card-body">
                    <form method="POST" action="/employee/orders/<?php echo $orderData['id']; ?>/status">
                        <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                        <div class="mb-3">
                            <label for="status" class="form-label">Nouveau statut</label>
                            <select class="form-select" id="status" name="status">
                                <?php
                                $allStatuses = ['EN_ATTENTE','ACCEPTEE','EN_PREPARATION','PRET','EN_LIVRAISON','LIVREE','ATTENTE_MATERIEL','TERMINEE','ANNULEE'];
                                $role = $_SESSION['role_id'];
                                foreach ($allStatuses as $s):
                                    // Cuisinier
                                    if ($role === 3 && !in_array($s, ['ACCEPTEE','EN_PREPARATION','PRET'])) continue;
                                    // Livreur
                                    if ($role === 4 && !in_array($s, ['PRET','EN_LIVRAISON','LIVREE'])) continue;
                                    // Employé polyvalent — pas ANNULEE sans motif (géré plus bas)
                                ?>
                                <option value="<?php echo $s; ?>" <?php echo $orderData['status'] === $s ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Motif annulation -->
                        <?php if (in_array($role, [1, 2, 6])): ?>
                        <div class="mb-3" id="cancel-block" style="display:none;">
                            <label for="cancellation_reason" class="form-label">Motif d'annulation</label>
                            <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="2"></textarea>
                            <label for="contact_channel" class="form-label mt-2">Canal de contact</label>
                            <select class="form-select" id="contact_channel" name="contact_channel">
                                <option value="mail">Mail</option>
                                <option value="telephone">Téléphone</option>
                            </select>
                            <?php if ($role === 6): ?>
                            <label for="authorized_by" class="form-label mt-2">Autorisé par</label>
                            <select class="form-select" id="authorized_by" name="authorized_by">
                                <option value="">— Sélectionner —</option>
                                <?php foreach ($managers as $mgr): ?>
                                    <option value="<?php echo $mgr['id']; ?>">
                                        <?php echo htmlspecialchars($mgr['first_name'] . ' ' . $mgr['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-warning">Mettre à jour</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Commentaire interne -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><strong>Commentaire interne</strong></div>
                <div class="card-body">
                    <form method="POST" action="/employee/orders/<?php echo $orderData['id']; ?>/comment">
                        <input type="hidden" name="csrf_token" value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
                        <div class="mb-3">
                            <textarea class="form-control" name="comment" rows="4" placeholder="Ajouter une note interne..."><?php echo htmlspecialchars($orderData['internal_comment'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Dernière MAJ -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header"><strong>Dernière mise à jour</strong></div>
                <div class="card-body">
                    <p><strong>Par :</strong> <?php echo htmlspecialchars($orderData['last_updated_by'] ?? '—'); ?></p>
                    <p><strong>Le :</strong> <?php echo $orderData['last_status_change'] ? date('d/m/Y H:i', strtotime($orderData['last_status_change'])) : '—'; ?></p>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
document.getElementById('status')?.addEventListener('change', function() {
    const block = document.getElementById('cancel-block');
    if (block) block.style.display = this.value === 'ANNULEE' ? 'block' : 'none';
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<?php require_once __DIR__ . '/../partials/scripts.php'; ?>
</body>
</html>