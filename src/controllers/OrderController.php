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
        $status = SecurityHelper::sanitize($data['status']);
        $reason = isset($data['cancellation_reason']) ? SecurityHelper::sanitize($data['cancellation_reason']) : null;
        $contact = isset($data['contact_channel']) ? SecurityHelper::sanitize($data['contact_channel']) : null;
        $authorizedBy = isset($data['authorized_by']) ? (int)$data['authorized_by'] : null;

        $role = $_SESSION['role_id'];

        // Vérif droits annulation
        if ($status === 'ANNULEE' && !in_array($role, [1, 2, 6])) {
            return ['error' => 'Non autorisé'];
        }
        if ($status === 'ANNULEE' && $role === 6 && empty($reason)) {
            return ['error' => 'Motif obligatoire'];
        }

        $orderModel = new OrderModel($this->pdo);
        $orderModel->updateStatus($id, $status, $_SESSION['user_id'], $reason, $contact);
        
        // Mail fin de commande
        if ($status === 'TERMINEE') {
            $orderData = $orderModel->findById($id);
            $userModel = new UserModel($this->pdo);
            $client = $userModel->findById($orderData['user_id']);
            MailHelper::sendOrderCompleted($client['email'], $client['first_name'], (int)$id);
        }

        // Mail retour matériel
        if ($status === 'ATTENTE_MATERIEL') {
            $orderData = $orderModel->findById($id);
            $userModel = new UserModel($this->pdo);
            $client = $userModel->findById($orderData['user_id']);
            MailHelper::sendMaterialReturn($client['email'], $client['first_name'], $orderData['order_number']);
        }
        
        if ($status === 'ANNULEE') {
            $orderData = $orderModel->findById($id);
            if ($orderData) {
                $stmtPrep = $this->pdo->prepare("
                    SELECT COALESCE(SUM(d.preparation_time), 0) as total_prep_time
                    FROM composition_menu cm
                    JOIN dishes d ON cm.dish_id = d.id
                    WHERE cm.menu_id = :menu_id AND d.is_active = 1
                ");
                $stmtPrep->execute([':menu_id' => $orderData['menu_id']]);
                $prep_time = (int)$stmtPrep->fetchColumn();

                $stmtCap = $this->pdo->query("SELECT value FROM settings WHERE setting_key = 'daily_capacity_minutes' LIMIT 1");
                $daily = (int)($stmtCap->fetchColumn() ?: 840);

                $total_needed = $prep_time * $orderData['guest_count'];
                $per_day      = (int)ceil($total_needed / 4);

                $delivery = new DateTime($orderData['delivery_date']);
                for ($i = 4; $i >= 1; $i--) {
                    $day = clone $delivery;
                    $day->modify("-{$i} days");
                    $dayStr = $day->format('Y-m-d');

                    $stmtUpdate = $this->pdo->prepare("
                        UPDATE production_planning 
                        SET used_capacity = GREATEST(0, used_capacity - :used) 
                        WHERE date = :date
                    ");
                    $stmtUpdate->execute([':used' => $per_day, ':date' => $dayStr]);
                }
            }
        }
        return ['success' => true];
    }

    public function assign($id, $data) {
        $cook_id = !empty($data['cook_id']) ? (int)$data['cook_id'] : null;
        $driver_id = !empty($data['driver_id']) ? (int)$data['driver_id'] : null;

        $orderModel = new OrderModel($this->pdo);
        $orderModel->updateAssignment($id, $cook_id, $driver_id);

        return ['success' => true];
    }

    public function selfAssign($id, $role) {
        $orderModel = new OrderModel($this->pdo);
        $orderData = $orderModel->findById($id);

        if ($role === 'cook' && $orderData['cook_id'] !== null) {
            return ['error' => 'Cuisinier déjà attribué'];
        }
        if ($role === 'driver' && $orderData['driver_id'] !== null) {
            return ['error' => 'Livreur déjà attribué'];
        }

        $cook_id = $role === 'cook' ? $_SESSION['user_id'] : $orderData['cook_id'];
        $driver_id = $role === 'driver' ? $_SESSION['user_id'] : $orderData['driver_id'];

        $orderModel->updateAssignment($id, $cook_id, $driver_id);

        return ['success' => true];
    }

    public function addComment($id, $comment) {
        $comment = SecurityHelper::sanitize($comment);
        $orderModel = new OrderModel($this->pdo);
        $orderModel->updateComment($id, $comment);
        return ['success' => true];
    }
}