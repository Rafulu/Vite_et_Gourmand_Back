<?php

class AddressController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère les adresses du client connecté
    public function getMyAddresses() {
        $addressModel = new AddressModel($this->pdo);
        return $addressModel->findByUserId($_SESSION['user_id']);
    }

    // Créer une adresse
    public function create($data) {
        $data['user_id'] = $_SESSION['user_id'];
        $addressModel = new AddressModel($this->pdo);
        $id = $addressModel->create($data);
        return ['success' => true, 'id' => $id];
    }

    // Modifier une adresse
    public function update($id, $data) {
        $addressModel = new AddressModel($this->pdo);
        $addressModel->update($id, $data);
        return ['success' => true];
    }

    // Supprimer une adresse
    public function delete($id) {
        $addressModel = new AddressModel($this->pdo);
        $addressModel->delete($id);
        return ['success' => true];
    }
}