<?php

class DishController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère tous les plats
    public function getAll() {
        $dishModel = new DishModel($this->pdo);
        return $dishModel->findAll();
    }

    // Récupère un plat par son id
    public function getById($id) {
        $dishModel = new DishModel($this->pdo);
        $dish = $dishModel->findById($id);
        if (!$dish) {
            return ['error' => 'Plat non trouvé'];
        }
        return $dish;
    }

    // Créer un plat
    public function create($data) {
        $dishModel = new DishModel($this->pdo);
        $id = $dishModel->create($data);
        return ['success' => true, 'id' => $id];
    }

    // Modifier un plat
    public function update($id, $data) {
        $dishModel = new DishModel($this->pdo);
        $dishModel->update($id, $data);
        return ['success' => true];
    }

    // Désactiver un plat
    public function disable($id) {
        $dishModel = new DishModel($this->pdo);
        $dishModel->disable($id);
        return ['success' => true];
    }
}