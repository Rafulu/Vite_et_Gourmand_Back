<?php

class SecurityHelper {

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function hasRole($role_id) {
        return isset($_SESSION['role_id']) && $_SESSION['role_id'] == $role_id;
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
    }

    public static function requireRole($role) {
        self::requireLogin();
        $roles = is_array($roles) ? $roles : [$roles];
        if (!in_array($_SESSION['role_id'], $role)) {
            header('Location: /');
            exit();
        }
    }

    // Vérification mise en forme password
    public static function validatePassword($password) {
        if (strlen($password) < 14) {
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }
        return true;
    }

    // Nettoyage des données d'un formulaire
    public static function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    // Création Token CSRF
    public static function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    // Vérification du token reçu dans la session
    public static function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
        http_response_code(403);
        die('Token CSRF invalide');
        }
    }

    // Vérification Format email
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}