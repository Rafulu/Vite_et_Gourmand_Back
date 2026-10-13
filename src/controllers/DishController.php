<?php

class DishController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère tous les plats
    public function getAll($activeOnly = true) {
        $dishModel = new DishModel($this->pdo);
        return $dishModel->findAll($activeOnly);
    }

    // Récupère un plat par son id
    public function getById($id, $activeOnly = true) {
        $dishModel = new DishModel($this->pdo);
        $dish = $dishModel->findById($id, $activeOnly);
        if (!$dish) {
            return ['error' => 'Plat non trouvé'];
        }
        return $dish;
    }

    // Récupère tous les allergènes
    public function getAllergens() {
        $dishModel = new DishModel($this->pdo);
        return $dishModel->findAllAllergens();
    }

    // Récupère les ids d'allergènes d'un plat
    public function getAllergenIds($id) {
        $dishModel = new DishModel($this->pdo);
        return $dishModel->findAllergenIds($id);
    }

    // Créer un plat
    public function create($data) {
        $dishModel = new DishModel($this->pdo);
        $id = $dishModel->create($data);
        $dishModel->syncAllergens($id, $data['allergen_ids'] ?? []);
        return ['success' => true, 'id' => $id];
    }

    // Modifier un plat
    public function update($id, $data) {
        $dishModel = new DishModel($this->pdo);
        $dishModel->update($id, $data);
        $dishModel->syncAllergens($id, $data['allergen_ids'] ?? []);
        return ['success' => true];
    }

    // Désactiver un plat
    public function disable($id) {
        $dishModel = new DishModel($this->pdo);
        $dishModel->disable($id);
        return ['success' => true];
    }

    // Réactiver un plat
    public function enable($id) {
        $dishModel = new DishModel($this->pdo);
        $dishModel->enable($id);
        return ['success' => true];
    }
}