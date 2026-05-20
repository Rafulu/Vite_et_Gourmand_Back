<?php

class AuthController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function login($email, $password) {
        $email = SecurityHelper::sanitize($email);

        $userModel = new UserModel($this->pdo);
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return ['error' => 'Une erreur est survenue, vérifiez vos informations'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['error' => 'Une erreur est survenue, vérifiez vos informations'];
        }

        //Stockage des informations en session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['email'] = $user['email'];

        return ['success' => true, 'session_id' => session_id(), 'role_id' => $user['role_id']];
    }

    public function register($data) {
        // Nettoyer les données
        $data['email'] = SecurityHelper::sanitize($data['email']);
        $data['last_name'] = SecurityHelper::sanitize($data['last_name']);
        $data['first_name'] = SecurityHelper::sanitize($data['first_name']);
        $data['phone'] = SecurityHelper::sanitize($data['phone']);

        // Valider email
        if (!SecurityHelper::validateEmail($data['email'])) {
            return ['error' => 'Une erreur est survenue, vérifiez vos informations'];
        }

        // Valider mot de passe
        if (!SecurityHelper::validatePassword($data['password'])) {
            return ['error' => 'Le mot de passe doit contenir au moins 14 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial'];
        }

        //traitement inscription
        $userModel = new UserModel($this->pdo);

        //vérifier si l'email existe déjà
        $existingUser = $userModel->findByEmail($data['email']);
        if ($existingUser) {
            return ['error' => 'Un problème est survenu, veuillez vous connecter ou réinitialiser votre mot de passe'];
        }

        // Hasher le mot de passe
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // Créer l'Utilisateur
        $userModel->create($data);
        $userId = $this->pdo->lastInsertId();

        return ['success' => true, 'user_id' => $userId];
    }

    public function forgotPassword($email) {
        //traitement réinitialisation mdp
        $userModel = new UserModel($this->pdo);

        //Vérifier si l'email existe
        $user = $userModel->findByemail($email);

        if (!$user) {
            return ['success' => true];
        }

        // Génerer un token unique pour reinitialisation mdp
        $token = bin2hex(random_bytes(32));

        // Stocker  le token en BDD
        $tokenModel = new TokenModel($this->pdo);
        $tokenModel->create([
            'user_id' => $user['id'],
            'value' => $token,
            'expires_at' => date( 'Y-m-d H:i:s', strtotime('+1 hour'))
        ]);

        // Envoyer l'email

        return ['success' => true];
    }

    public function updateProfile($id, $data) {
        $data['first_name'] = SecurityHelper::sanitize($data['first_name']);
        $data['last_name']  = SecurityHelper::sanitize($data['last_name']);
        $data['email']      = SecurityHelper::sanitize($data['email']);
        $data['phone']      = SecurityHelper::sanitize($data['phone']);

        if (!SecurityHelper::validateEmail($data['email'])) {
            return ['error' => 'Email invalide'];
        }

        $userModel = new UserModel($this->pdo);
        $userModel->update($id, $data);

        $_SESSION['first_name'] = $data['first_name'];
        $_SESSION['email']      = $data['email'];

        return ['success' => true];
    }
}