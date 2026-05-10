<?php

class TokenModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO tokens (user_id, value, expires_at)
            VALUES (:user_id, :value, :expires_at)
        ");
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':value' => $data['value'],
            ':expires_at' => $data['expires_at']
        ]);
    }

    public function findByValue($value) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tokens WHERE value = :value AND is_used = 0 AND expires_at > NOW()
        ");
        $stmt->execute([':value' => $value]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}