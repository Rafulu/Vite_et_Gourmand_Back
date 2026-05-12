<?php
class UserModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function findByemail($email) {
        //Cherchr par email
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
    public function create($data) {
        //créer un nouveau user
        $stmt = $this->pdo->prepare ("
            INSERT INTO users (email, password, last_name, first_name, phone, role_id, create_at)
            VALUES (:email, :password, :last_name, :first_name, :phone, :role_id, NOW())
        ");
        $stmt->execute([
            ':email' => $data['email'],
            ':password' => $data['password'],
            ':last_name' => $data['last_name'],
            ':first_name' => $data['first_name'],
            ':phone' => $data['phone'],
            ':role_id' => $data['role_id']
        ]); 
    }
}