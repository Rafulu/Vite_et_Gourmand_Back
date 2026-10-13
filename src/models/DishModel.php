<?php

class DishModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère les plats (actifs ou tous)
    public function findAll($activeOnly = true) {
        $where = $activeOnly ? "WHERE d.is_active = 1" : "";
        $stmt = $this->pdo->prepare("
            SELECT d.*, GROUP_CONCAT(a.name SEPARATOR ', ') AS allergens
            FROM dishes d
            LEFT JOIN allergen_dish ad ON d.id = ad.dish_id
            LEFT JOIN allergens a ON ad.allergen_id = a.id
            $where
            GROUP BY d.id
            ORDER BY d.is_active DESC, d.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère un plat par son id
    public function findById($id, $activeOnly = true) {
        $activeFilter = $activeOnly ? "AND is_active = 1" : "";
        $stmt = $this->pdo->prepare("
            SELECT * FROM dishes WHERE id = :id $activeFilter
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Liste de tous les allergènes
    public function findAllAllergens() {
        $stmt = $this->pdo->prepare("SELECT * FROM allergens ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ids des allergènes d'un plat
    public function findAllergenIds($dish_id) {
        $stmt = $this->pdo->prepare("SELECT allergen_id FROM allergen_dish WHERE dish_id = :dish_id");
        $stmt->execute([':dish_id' => $dish_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Lier les allergènes à un plat (remplace l'existant)
    public function syncAllergens($dish_id, array $allergen_ids) {
        $this->pdo->prepare("DELETE FROM allergen_dish WHERE dish_id = :dish_id")
                  ->execute([':dish_id' => $dish_id]);
        $stmt = $this->pdo->prepare("INSERT INTO allergen_dish (allergen_id, dish_id) VALUES (:allergen_id, :dish_id)");
        foreach ($allergen_ids as $allergen_id) {
            $stmt->execute([':allergen_id' => $allergen_id, ':dish_id' => $dish_id]);
        }
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

    // Réactiver un plat
    public function enable($id) {
        $stmt = $this->pdo->prepare("UPDATE dishes SET is_active = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
    
}