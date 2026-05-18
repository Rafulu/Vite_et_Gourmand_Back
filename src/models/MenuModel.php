<?php

class MenuModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Requête de base réutilisable
    private function baseQuery() {
        return "
            SELECT m.*, t.name as theme_name,
            GROUP_CONCAT(DISTINCT a.name) as allergens,
            GROUP_CONCAT(DISTINCT a.icon) as allergen_icons,
            GROUP_CONCAT(DISTINCT d.diet) as diets
            FROM menus m
            LEFT JOIN themes t ON m.theme_id = t.id
            LEFT JOIN composition_menu cm ON m.id = cm.menu_id
            LEFT JOIN dishes d ON cm.dish_id = d.id
            LEFT JOIN allergen_dish ad ON d.id = ad.dish_id
            LEFT JOIN allergens a ON ad.allergen_id = a.id
            WHERE m.is_active = 1
            GROUP BY m.id
        ";
    }

    // Récupère tous les menus actifs
    public function findAll() {
        $stmt = $this->pdo->prepare($this->baseQuery());
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère un menu par son id
    public function findById($id) {
        $sql = $this->baseQuery() . " AND m.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Récupère les menus selon les filtres
    public function findWithFilters($filters) {
        $sql = $this->baseQuery();
        $params = [];
        $having = [];

        if (!empty($filters['theme_id'])) {
            $having[]= "m.theme_id = :theme_id";
            $params[':theme_id'] = $filters['theme_id'];
        }

        if (!empty($filters['min_price'])) {
            $having[] = "m.price_per_person >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $having[] = "m.price_per_person <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        if (!empty($filters['min_guests'])) {
            $having[] = "m.min_guests <= :min_guests";
            $params[':min_guests'] = $filters['min_guests'];
        }

        if (!empty($filters['diet'])) {
            $sql .= " AND m.id IN (SELECT cm.menu_id FROM composition_menu cm JOIN dishes d ON cm.dish_id = d.id WHERE d.diet = :diet)";
            $params[':diet'] = $filters['diet'];
        }

        if (!empty($having)) {
            $sql .= " HAVING " . implode( " AND ", $having);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère les plats d'un menu
    public function findDishesByMenuId($menu_id) {
        $stmt = $this->pdo->prepare("
            SELECT d.* FROM dishes d
            JOIN composition_menu cm ON d.id = cm.dish_id
            WHERE cm.menu_id = :menu_id
            AND d.is_active = 1
        ");
        $stmt->execute([':menu_id' => $menu_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}