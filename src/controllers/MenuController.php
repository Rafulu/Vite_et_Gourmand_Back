<?php

class MenuController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll($activeOnly = true) {
        $menuModel = new MenuModel($this->pdo);
        return $menuModel->findAll($activeOnly);
    }

    public function getById($id, $activeOnly = true) {
        $menuModel = new MenuModel($this->pdo);
        $menu = $menuModel->findById($id, $activeOnly);
        if (!$menu) {
            return ['error' => 'Menu non trouvé'];
        }
        return $menu;
    }

    // Récupère les menus selon les filtres
    public function getWithFilters($filters) {
        $menuModel = new MenuModel($this->pdo);
        return $menuModel->findWithFilters($filters);
    }

    //Création d'un menu
    public function create($data) {
        $menuModel = new MenuModel($this->pdo);
        $menu_id = $menuModel->create($data);
        if (!empty($data['dish_ids'])) {
            $menuModel->syncDishes($menu_id, $data['dish_ids']);
        }
        return $menu_id;
    }

    //Mise à jour d'un menu
    public function update($id, $data) {
        $menuModel = new MenuModel($this->pdo);
        $menuModel->update($id, $data);
        $dish_ids = $data['dish_ids'] ?? [];
        $menuModel->syncDishes($id, $dish_ids);
    }

    //Désactivation d'un menu
    public function disable($id) {
        $menuModel = new MenuModel($this->pdo);
        $menuModel->disable($id);
    }

    //Suppression d'un Menu
    public function delete($id) {
        $menuModel = new MenuModel($this->pdo);
        $menuModel->delete($id);
    }

    public function getDishesByMenuId($menu_id) {
        $menuModel = new MenuModel($this->pdo);
        return $menuModel->findDishesByMenuId($menu_id);
    }
}