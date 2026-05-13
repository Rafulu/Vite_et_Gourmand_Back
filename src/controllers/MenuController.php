<?php

class MenuController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère tous les menus actifs
    public function getAll() {
        $menuModel = new MenuModel($this->pdo);
        return $menuModel->findAll();
    }

    // Récupère un menu par son id
    public function getById($id) {
        $menuModel = new MenuModel($this->pdo);
        $menu = $menuModel->findById($id);
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
}