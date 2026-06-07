<?php

class MenuModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Requête de base réutilisable
    private function baseQuery($activeOnly = true) {
        $where = $activeOnly ? "WHERE m.is_active = 1" : "WHERE 1=1";
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
            $where
            GROUP BY m.id
        ";
    }

    public function findAll($activeOnly = true) {
        $stmt = $this->pdo->prepare($this->baseQuery($activeOnly) . " ORDER BY m.is_active DESC, m.id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère un menu par son id
    public function findById($id, $activeOnly = true) {
        $activeFilter = $activeOnly ? "AND m.is_active = 1" : "";
        $sql = "
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
            WHERE m.id = :id $activeFilter
            GROUP BY m.id
        ";
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
            SELECT d.*, GROUP_CONCAT(a.name SEPARATOR ', ') AS allergens
            FROM dishes d
            JOIN composition_menu cm ON d.id = cm.dish_id
            LEFT JOIN allergen_dish ad ON d.id = ad.dish_id
            LEFT JOIN allergens a ON ad.allergen_id = a.id
            WHERE cm.menu_id = :menu_id
            AND d.is_active = 1
            GROUP BY d.id
        ");
        $stmt->execute([':menu_id' => $menu_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Créer un menu
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO menus (title, description, theme_id, min_guests, price_per_person, stock, conditions, is_active)
            VALUES (:title, :description, :theme_id, :min_guests, :price_per_person, :stock, :conditions, 1)
        ");
        $stmt->execute([
            ':title'            => $data['title'],
            ':description'      => $data['description'],
            ':theme_id'         => $data['theme_id'],
            ':min_guests'       => $data['min_guests'],
            ':price_per_person' => $data['price_per_person'],
            ':stock'            => $data['stock'],
            ':conditions'       => $data['conditions'],
        ]);
        return $this->pdo->lastInsertId();
    }

    // Modifier un menu
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE menus SET title = :title, description = :description, theme_id = :theme_id,
            min_guests = :min_guests, price_per_person = :price_per_person, stock = :stock, conditions = :conditions
            WHERE id = :id
        ");
        $stmt->execute([
            ':title'            => $data['title'],
            ':description'      => $data['description'],
            ':theme_id'         => $data['theme_id'],
            ':min_guests'       => $data['min_guests'],
            ':price_per_person' => $data['price_per_person'],
            ':stock'            => $data['stock'],
            ':conditions'       => $data['conditions'],
            ':id'               => $id,
        ]);
    }

    // Désactiver un menu
    public function disable($id) {
        $stmt = $this->pdo->prepare("UPDATE menus SET is_active = 0 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    // Lier les plats à un menu (remplace la composition existante)
    public function syncDishes($menu_id, array $dish_ids) {
        $this->pdo->prepare("DELETE FROM composition_menu WHERE menu_id = :menu_id")
                  ->execute([':menu_id' => $menu_id]);
        $stmt = $this->pdo->prepare("INSERT INTO composition_menu (menu_id, dish_id) VALUES (:menu_id, :dish_id)");
        foreach ($dish_ids as $dish_id) {
            $stmt->execute([':menu_id' => $menu_id, ':dish_id' => $dish_id]);
        }
    }
}