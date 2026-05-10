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

    public static function requireRole($role_id) {
        self::requireLogin();
        if (!self::hasRole($role_id)) {
            header('Location: /');
            exit();
        }
    }
}