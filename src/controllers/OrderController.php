<?php

class OrderController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère les commandes du client connecté
    public function getMyOrders() {
        $orderModel = new OrderModel($this->pdo);
        return $orderModel->findByUserId($_SESSION['user_id']);
    }

    // Récupère toutes les commandes (employé)
    public function getAll() {
        $orderModel = new OrderModel($this->pdo);
        return $orderModel->findAll();
    }

    // Récupère une commande par son id
    public function getById($id) {
        $orderModel = new OrderModel($this->pdo);
        $order = $orderModel->findById($id);
        if (!$order) {
            return ['error' => 'Commande non trouvée'];
        }
        return $order;
    }

    // Créer une commande
    public function create($data) {
        $data['user_id'] = $_SESSION['user_id'];
        $orderModel = new OrderModel($this->pdo);
        $id = $orderModel->create($data);
        return ['success' => true, 'id' => $id];
    }

    // Mettre à jour le statut
    public function updateStatus($id, $data) {
        $orderModel = new OrderModel($this->pdo);
        $orderModel->updateStatus(
            $id,
            $data['status'],
            $_SESSION['user_id'],
            $data['cancellation_reason'] ?? null,
            $data['contact_channel'] ?? null
        );
        return ['success' => true];
    }
}