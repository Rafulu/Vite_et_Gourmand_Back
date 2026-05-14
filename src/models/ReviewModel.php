<?php

class ReviewModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère tous les avis validés
    public function findAll() {
        $stmt = $this->pdo->prepare("
            SELECT r.*, u.first_name, u.last_name 
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Créer un avis
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO reviews (user_id, order_id, note, comment, created_at)
            VALUES (:user_id, :order_id, :note, :comment, NOW())
        ");
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':order_id' => $data['order_id'],
            ':note' => $data['note'],
            ':comment' => $data['comment']
        ]);
        return $this->pdo->lastInsertId();
    }
}