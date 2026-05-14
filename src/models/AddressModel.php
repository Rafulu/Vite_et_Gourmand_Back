<?php

class AddressModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère toutes les adresses d'un utilisateur
    public function findByUserId($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM addresses WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Créer une adresse
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO addresses (user_id, name, number, street, complement, postal_code, city, country)
            VALUES (:user_id, :name, :number, :street, :complement, :postal_code, :city, :country)
        ");
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':name' => $data['name'],
            ':number' => $data['number'] ?? null,
            ':street' => $data['street'],
            ':complement' => $data['complement'] ?? null,
            ':postal_code' => $data['postal_code'],
            ':city' => $data['city'],
            ':country' => $data['country'] ?? 'France'
        ]);
        return $this->pdo->lastInsertId();
    }

    // Modifier une adresse
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE addresses SET name = :name, number = :number, street = :street, 
            complement = :complement, postal_code = :postal_code, city = :city, country = :country
            WHERE id = :id
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':number' => $data['number'] ?? null,
            ':street' => $data['street'],
            ':complement' => $data['complement'] ?? null,
            ':postal_code' => $data['postal_code'],
            ':city' => $data['city'],
            ':country' => $data['country'] ?? 'France',
            ':id' => $id
        ]);
    }

    // Supprimer une adresse
    public function delete($id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM addresses WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }
}