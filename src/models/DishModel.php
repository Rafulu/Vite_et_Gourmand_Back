<?php

class DishModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère tous les plats actifs
    public function findAll() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM dishes WHERE is_active = 1
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère un plat par son id
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM dishes WHERE id = :id AND is_active = 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer un plat
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO dishes (name, category, diet, preparation_time, is_active)
            VALUES (:name, :category, :diet, :preparation_time, 1)
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':category' => $data['category'],
            ':diet' => $data['diet'],
            ':preparation_time' => $data['preparation_time']
        ]);
        return $this->pdo->lastInsertId();
    }

    // Modifier un plat
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE dishes SET name = :name, category = :category, diet = :diet, preparation_time = :preparation_time
            WHERE id = :id
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':category' => $data['category'],
            ':diet' => $data['diet'],
            ':preparation_time' => $data['preparation_time'],
            ':id' => $id
        ]);
    }

    // Désactiver un plat
    public function disable($id) {
        $stmt = $this->pdo->prepare("
            UPDATE dishes SET is_active = 0 WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }
}