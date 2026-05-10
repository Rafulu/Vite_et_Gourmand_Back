<?php

session_start();

//Empêcher l'accès direct aux infos PHP
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Config
require_once '../config/database.php';

// Models
require_once '../src/models/UserModel.php';
require_once '../src/models/TokenModel.php';

// Controllers
require_once '../src/controllers/AuthController.php';

// Helpers
require_once '../src/helpers/SecurityHelper.php';


$auth = new AuthController();

switch($uri) {

    //Pages publiques
    case '/':
        break;
    case '/menus':
        break;
    case '/login':
        if ($method === 'GET') {
            // afficher le formulaire
            require_once '../src/views/client/login.php';
        }
        if ($method === 'POST') {
            // traiter la connexion
            $email = $_POST['email'];
            $password = $_POST['password'];
            $result = $auth->login($email, $password);
            if (isset($result['success'])) {
                if ($_SESSION['role_id'] == 1){
                    header('Location: /admin');
                } elseif ($_SESSION['role_id'] == 2) {
                    header('Location: /employee');
                } else {
                    header('Location: /account');
                }
                exit();
            }
            if (isset($result['error'])) {
                $error = $result['error'];
            }
        }
        break;
    case '/register':
         if ($method === 'GET') {
        require_once '../src/views/client/register.php';
    }
    if ($method === 'POST') {
        $data = [
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'last_name' => $_POST['last_name'],
            'first_name' => $_POST['first_name'],
            'phone' => $_POST['phone'],
            'role_id' => 5
        ];
        $result = $auth->register($data);
        if (isset($result['success'])) {
            header('Location: /login');
            exit();
        }
        if (isset($result['error'])) {
            $error = $result['error'];
        }
    }
        break;
    case '/forgot-password':
        if ($method === 'GET') {
        require_once '../src/views/client/forgot-password.php'; 
    }
    if ($method === 'POST') {
        $email = $_POST['email'];
        $result = $auth->forgotPassword($email);
        if (isset($result['success'])) {
            $success = ['Un email vous a été envoyé si ce compte existe'];
        }
    }
        break;

    case '/contact':
        break;
    
    // Espace Client - Utilisateur connecté
    case '/account':
        SecurityHelper::requireLogin();
        require_once '../src/views/client/account.php'
        break;

    case '/orders':
        break;
    
    // Espace Employé
    case '/employee':
        SecurityHelper::requireRole(2);
        require_once '..src/views/employee/dashboard.php'
        break;

    case '/employee/orders':
        break;
    
    // Esapce admin
    case '/admin':
        SecurityHelper::requireRole(1);
        require_once '..src/views/admin/dashboard.php'
        break;
    case '/admin/employees':
        break;

    case '/admin/stats':
        break;
    
    // Page non trouvée
    default:
        http_response_code(404);
        break;
}