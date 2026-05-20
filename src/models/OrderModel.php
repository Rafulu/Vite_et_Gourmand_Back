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
            SELECT o.*,
                m.name as menu_name,
                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                CONCAT(cook.first_name, ' ', cook.last_name) as cook_name,
                CONCAT(driver.first_name, ' ', driver.last_name) as driver_name,
                CONCAT(author.first_name, ' ', author.last_name) as last_updated_by,
                h.changed_at as last_status_change
            FROM orders o
            JOIN menus m ON o.menu_id = m.id
            JOIN users u ON o.user_id = u.id
            LEFT JOIN users cook ON o.cook_id = cook.id
            LEFT JOIN users driver ON o.driver_id = driver.id
            LEFT JOIN order_status_history h ON h.order_id = o.id
                AND h.changed_at = (
                    SELECT MAX(changed_at) FROM order_status_history WHERE order_id = o.id
                )
            LEFT JOIN users author ON h.author_id = author.id
            ORDER BY o.order_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère une commande par son id
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT o.*,
                m.name as menu_name,
                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                u.email as client_email,
                u.phone as client_phone,
                CONCAT(cook.first_name, ' ', cook.last_name) as cook_name,
                CONCAT(driver.first_name, ' ', driver.last_name) as driver_name,
                da.street as delivery_street,
                da.city as delivery_city,
                da.postal_code as delivery_postal_code,
                ba.street as billing_street,
                ba.city as billing_city,
                ba.postal_code as billing_postal_code,
                CONCAT(author.first_name, ' ', author.last_name) as last_updated_by,
                h.changed_at as last_status_change
            FROM orders o
            JOIN menus m ON o.menu_id = m.id
            JOIN users u ON o.user_id = u.id
            LEFT JOIN users cook ON o.cook_id = cook.id
            LEFT JOIN users driver ON o.driver_id = driver.id
            LEFT JOIN addresses da ON o.delivery_address_id = da.id
            LEFT JOIN addresses ba ON o.billing_address_id = ba.id
            LEFT JOIN order_status_history h ON h.order_id = o.id
                AND h.changed_at = (
                    SELECT MAX(changed_at) FROM order_status_history WHERE order_id = o.id
                )
            LEFT JOIN users author ON h.author_id = author.id
            WHERE o.id = :id
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
    
    public function updateAssignment($id, $cook_id, $driver_id) {
        $stmt = $this->pdo->prepare("
            UPDATE orders SET cook_id = :cook_id, driver_id = :driver_id, update_at = NOW() WHERE id = :id
        ");
        $stmt->execute([
            ':cook_id'   => $cook_id,
            ':driver_id' => $driver_id,
            ':id'        => $id
        ]);
    }

    public function updateComment($id, $comment) {
        $stmt = $this->pdo->prepare("
            UPDATE orders SET internal_comment = :comment, update_at = NOW() WHERE id = :id
        ");
        $stmt->execute([
            ':comment' => $comment,
            ':id'      => $id
        ]);
    }
}