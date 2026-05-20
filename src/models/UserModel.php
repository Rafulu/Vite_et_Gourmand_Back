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

    public function createEmployee($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password, role_id, is_blocked, created_at)
            VALUES (:first_name, :last_name, :email, :password, :role_id, 0, NOW())
        ");
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':email'      => $data['email'],
            ':password'   => $data['password'],
            ':role_id'    => $data['role_id']
        ]);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByRole($role_id) {
        $stmt = $this->pdo->prepare("
            SELECT id, first_name, last_name FROM users WHERE role_id = :role_id
        ");
        $stmt->execute([':role_id' => $role_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone
            WHERE id = :id
        ");
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':email'      => $data['email'],
            ':phone'      => $data['phone'],
            ':id'         => $id
        ]);
    }

    public function findAllEmployees() {
        $stmt = $this->pdo->prepare("
            SELECT u.*, r.name as role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.role_id IN (2, 3, 4, 6)
            ORDER BY u.last_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleBlock($id) {
        $stmt = $this->pdo->prepare("
            UPDATE users SET is_blocked = IF(is_blocked = 1, 0, 1) WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }

    public function findAllRoles() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM roles WHERE id IN (2, 3, 4, 6)
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}