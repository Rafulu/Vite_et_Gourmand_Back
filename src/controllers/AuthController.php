<?php

class AuthController {
    
    public function login($email, $password) {
        $email = SecurityHelper::sanitize($email);

        $userModel = new UserModel($this->pdo);
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return ['error' => 'Email ou mot de passe incorrect'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['error' => 'Email ou mot de passe incorrect'];
        }

        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];

        return ['sucess' => true];
    }

    public function register($data) {
        // Nettoyer les données
        $data['email'] = SecurityHelper::sanitize($data['email']);
        $data['last_name'] = SecurityHelper::sanitize($data['last_name']);
        $data['first_name'] = SecurityHelper::sanitize($data['first_name']);
        $data['phone'] = SecurityHelper::sanitize($data['phone']);

        // Valider email
        if (!SecurityHelper::validateEmail($data['email'])) {
            return ['error' => 'Email invalide'];
        }

        // Valider mot de passe
        if (!SecurityHelper::validatePassword($data['password'])) {
            return ['error' => 'Mot de passe invalide'];
        }

        //traitement inscription
        $userModel = new UserModel($this->pdo);

        //vérifier si l'email existe déjà
        $existingUser = $userModel->findByEmail($data['email']);
        if ($existingUser) {
            return ['error' => 'Une erreur est survenue, vérifiez vos informations'];
        }

        // Hasher le mot de passe
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // Créer l'Utilisateur
        $userModel->create($data);

        return ['success' => true];
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
}