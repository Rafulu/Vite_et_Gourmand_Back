<?php

class OrderModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère toutes les commandes d'un client
    public function findByUserId($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère toutes les commandes (pour les employés)
    public function findAll() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM orders ORDER BY order_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère une commande par son id
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM orders WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer une commande
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO orders (user_id, delivery_address_id, billing_address_id, order_date, delivery_date, guest_count, status, updated_at)
            VALUES (:user_id, :delivery_address_id, :billing_address_id, NOW(), :delivery_date, :guest_count, 'EN_ATTENTE', NOW())
        ");
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':delivery_address_id' => $data['delivery_address_id'],
            ':billing_address_id' => $data['billing_address_id'],
            ':delivery_date' => $data['delivery_date'],
            ':guest_count' => $data['guest_count']
        ]);
        return $this->pdo->lastInsertId();
    }

    // Mettre à jour le statut
    public function updateStatus($id, $status, $author_id, $reason = null, $contact = null) {
        $stmt = $this->pdo->prepare("
            UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id
        ");
        $stmt->execute([':status' => $status, ':id' => $id]);

        // Enregistrer dans l'historique
        $stmt = $this->pdo->prepare("
            INSERT INTO order_status_history (order_id, new_status, changed_at, author_id, cancellation_reason, contact_channel)
            VALUES (:order_id, :new_status, NOW(), :author_id, :cancellation_reason, :contact_channel)
        ");
        $stmt->execute([
            ':order_id' => $id,
            ':new_status' => $status,
            ':author_id' => $author_id,
            ':cancellation_reason' => $reason,
            ':contact_channel' => $contact
        ]);
    }
}<?php

class OrderModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère toutes les commandes d'un client
    public function findByUserId($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère toutes les commandes (pour les employés)
    public function findAll() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM orders ORDER BY order_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère une commande par son id
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM orders WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer une commande
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO orders (user_id, delivery_address_id, billing_address_id, order_date, delivery_date, guest_count, status, updated_at)
            VALUES (:user_id, :delivery_address_id, :billing_address_id, NOW(), :delivery_date, :guest_count, 'EN_ATTENTE', NOW())
        ");
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':delivery_address_id' => $data['delivery_address_id'],
            ':billing_address_id' => $data['billing_address_id'],
            ':delivery_date' => $data['delivery_date'],
            ':guest_count' => $data['guest_count']
        ]);
        return $this->pdo->lastInsertId();
    }

    // Mettre à jour le statut
    public function updateStatus($id, $status, $author_id, $reason = null, $contact = null) {
        $stmt = $this->pdo->prepare("
            UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id
        ");
        $stmt->execute([':status' => $status, ':id' => $id]);

        // Enregistrer dans l'historique
        $stmt = $this->pdo->prepare("
            INSERT INTO order_status_history (order_id, new_status, changed_at, author_id, cancellation_reason, contact_channel)
            VALUES (:order_id, :new_status, NOW(), :author_id, :cancellation_reason, :contact_channel)
        ");
        $stmt->execute([
            ':order_id' => $id,
            ':new_status' => $status,
            ':author_id' => $author_id,
            ':cancellation_reason' => $reason,
            ':contact_channel' => $contact
        ]);
    }
}