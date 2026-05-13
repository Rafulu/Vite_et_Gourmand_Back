<?php

class MenuModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Requête de base réutilisable
    private function baseQuery() {
        return "SELECT * FROM menus WHERE is_active = 1";
    }

    // Récupère tous les menus actifs
    public function findAll() {
        $stmt = $this->pdo->prepare($this->baseQuery());
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


    // Récupère les menus selon les filtres
    public function findWithFilters($filters) {
        $sql = $this->baseQuery();
        $params = [];

        if (!empty($filters['theme_id'])) {
            $sql .= " AND theme_id = :theme_id";
            $params[':theme_id'] = $filters['theme_id'];
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND price_per_person >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND price_per_person <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        if (!empty($filters['min_guests'])) {
            $sql .= " AND min_guests <= :min_guests";
            $params[':min_guests'] = $filters['min_guests'];
        }

        if (!empty($filters['diet'])) {
            $sql .= "AND id IN (SELECT menu_id FROM composition_menu cm JOIN dishes d ON cm.dish_id = d.id WHERE d.diet = :diet)";
            $params[':diet'] = $filters['diet'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}