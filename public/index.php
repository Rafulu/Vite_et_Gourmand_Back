<?php

session_start();

//Empêcher l'accès direct aux infos PHP
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

//Header CORS - autorise les requêtes cross-origin pour l'API REST (Autorise le front et le back à communiquer)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');


//Récupère le chemin de l'URL pour savoir où aller
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

//Récupère la méthode HTTP pour savoir quoi faire
$method = $_SERVER['REQUEST_METHOD'];


// Config : Configuration de la connexion à la BDD
require_once '../config/database.php';

// Models : Gestion des données de la BDD
require_once '../src/models/UserModel.php';
require_once '../src/models/TokenModel.php';
require_once '../src/models/MenuModel.php';
require_once '../src/models/DishModel.php';
require_once '../src/models/OrderModel.php';

// Controllers : Logique métier de l'application
require_once '../src/controllers/AuthController.php';
require_once '../src/controllers/MenuController.php';
require_once '../src/controllers/DishController.php';
require_once '../src/controllers/OrderController.php';

// Helpers: Fonctions utilitaires de sécurité
require_once '../src/helpers/SecurityHelper.php';


// Création des objets en fonction de leur classes
$auth = new AuthController($pdo);
$menu = new MenuController($pdo);
$dish = new DishController($pdo);
$dish = new OrderController($pdo);

// Gestion des routes dynamiques pour les menus 
if (preg_match('/^\/menus\/(\d+)$/', $url, $matches)) {
    $id = $matches[1];
    $result = $menu->getById($id);
    echo json_encode($result);
    exit();
}

// Gestion des routes dynamiques pour les plats
if (preg_match('/^\/dishes\/(\d+)$/', $url, $matches)) {
    $id = $matches[1];
    if ($method === 'GET') {
        echo json_encode($dish->getById($id));
    }
    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($dish->update($id, $data));
    }
    if ($method === 'DELETE') {
        echo json_encode($dish->disable($id));
    }
    exit();
}

// Routes dynamiques commandes
if (preg_match('/^\/orders\/(\d+)$/', $url, $matches)) {
    SecurityHelper::requireLogin();
    $id = $matches[1];
    if ($method === 'GET') {
        echo json_encode($order->getById($id));
    }
    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($order->updateStatus($id, $data));
    }
    exit();
}

switch($url) {

    //Pages publiques
    case '/':
        break;
    case '/menus':
        if ($method === 'GET') {
            $filters = json_decode(file_get_contents('php://input'), true) ?? [];
            $menus = $menu->getWithFilters($filters);
            echo json_encode($menus);
        }
        break;

    case '/dishes':
        if ($method === 'GET') {
            echo json_encode($dish->getAll());
        }
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($dish->create($data));
        }
        break;
    
    case '/orders':
        SecurityHelper::requireLogin();
        if ($method === 'GET') {
            echo json_encode($order->getMyOrders());
        }
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($order->create($data));
        }
        break;

    case '/login':
        if ($method === 'GET') {
            // afficher le formulaire
            require_once '../src/views/client/login.php';
        }
        if ($method === 'POST') {
            // traiter la connexion
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data['email'];
            $password = $data['password'];
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
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $data['role_id'] = 5;
            $result = $auth->register($data);
            echo json_encode($result);
        }
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            //var_dump($data);
            //die();
            $data['role_id'] = 5;
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
         echo json_encode(['message' => 'Espace client', 'user_id' => $_SESSION['user_id']]);
        break;

    case '/orders':
        break;
    
    // Espace Employé
    case '/employee':
        SecurityHelper::requireRole(2);
        require_once '../src/views/employee/dashboard.php';
        break;

    case '/employee/orders':
        break;
    
    // Esapce admin
    case '/admin':
        SecurityHelper::requireRole(1);
        require_once '../src/views/admin/dashboard.php';
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