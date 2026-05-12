<?php

class MenuModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère tous les menus actifs
    public function findAll() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM menus WHERE is_active = 1
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère un menu par son id
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM menus WHERE id = :id AND is_active = 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}