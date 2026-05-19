<?php
ob_start();

//Empêcher l'accès direct aux infos PHP
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

//Header CORS - autorise les requêtes cross-origin pour l'API REST (Autorise le front et le back à communiquer)
//header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

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
require_once '../src/models/ReviewModel.php';
require_once '../src/models/AddressModel.php';

// Controllers : Logique métier de l'application
require_once '../src/controllers/AuthController.php';
require_once '../src/controllers/MenuController.php';
require_once '../src/controllers/DishController.php';
require_once '../src/controllers/OrderController.php';
require_once '../src/controllers/ReviewController.php';
require_once '../src/controllers/AddressController.php';

// Helpers: Fonctions utilitaires de sécurité
require_once '../src/helpers/SecurityHelper.php';


// Création des objets en fonction de leur classes
$auth = new AuthController($pdo);
$menu = new MenuController($pdo);
$dish = new DishController($pdo);
$order = new OrderController($pdo);
$review = new ReviewController($pdo);
$address = new AddressController($pdo);


// Gestion des routes dynamiques pour les menus 
if (preg_match('/^\/menus\/(\d+)$/', $url, $matches)) {
    $id = $matches[1];
    $menuModel = new MenuModel($pdo);
    $menuData = $menuModel->findById($id);
    if (!$menuData) {
        http_response_code(404);
        echo "Menu non trouvé";
        exit();
    }
    $menu = $menuData;
    $dishes = $menuModel->findDishesByMenuId($id);
    require_once '../src/views/menu-detail.php';
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

// Gestion des routes dynamiques pour les orders
if (preg_match('/^\/order\/(\d+)$/', $url, $matches)) {
    SecurityHelper::requireLogin();
    $id = $matches[1];
    $menuModel = new MenuModel($pdo);
    $menu = $menuModel->findById($id);
    $addressModel = new AddressModel($pdo);
    $addresses = $addressModel->findByUserId($_SESSION['user_id']);
    $conditions = $pdo->prepare("
        SELECT c.* FROM conditions c
        JOIN condition_menu cm ON c.id = cm.condition_id
        WHERE cm.menu_id = :menu_id
    ");
    $conditions->execute([':menu_id' => $id]);
    $conditions = $conditions->fetchAll(PDO::FETCH_ASSOC);
    require_once '../src/views/client/order-form.php';
    exit();
}

// Gestion des routes dynamiques pour les confirmation d'order
if (preg_match('/^\/orders\/(\d+)$/', $url, $matches)) {
    SecurityHelper::requireLogin();
    $id = $matches[1];
    $orderData = $order->getById($id);
    if (isset($orderData['error'])) {
        http_response_code(404);
        echo "Commande non trouvée";
        exit();
    }
    // Vérifier que la commande appartient à l'utilisateur connecté
    if ($orderData['user_id'] != $_SESSION['user_id']) {
        http_response_code(403);
        echo "Accès non autorisé";
        exit();
    }
    $menuData = (new MenuModel($pdo))->findById($orderData['menu_id']);
    require_once '../src/views/client/order-confirmation.php';
    exit();
}

// Routes dynamiques adresses
if (preg_match('/^\/addresses\/(\d+)$/', $url, $matches)) {
    SecurityHelper::requireLogin();
    $id = $matches[1];
    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($address->update($id, $data));
    }
    if ($method === 'DELETE') {
        echo json_encode($address->delete($id));
    }
    exit();
}

switch($url) {

    //Pages publiques
    case '/':
        break;

    case '/menus':
        if ($method === 'GET') {
            $filters = [];
            $menus = $menu->getWithFilters($filters);
            $themes = $pdo->query("SELECT * FROM themes")->fetchALL(PDO::FETCH_ASSOC);
            require_once '../src/views/menus.php';
        }
        if ($method === 'POST') {
            $filters = json_decode(file_get_contents('php://input'), true) ?? [];
            $menus = $menu->getWithFilters($filters);
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode($menus);
            exit();
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
            SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

            $errors = [];

            $menu_id             = filter_var($_POST['menu_id'] ?? '', FILTER_VALIDATE_INT);
            $delivery_address_id = $_POST['delivery_address_id'] ?? '';
            $billing_address_id  = $_POST['billing_address_id'] ?? '';
            $delivery_date       = SecurityHelper::sanitize($_POST['delivery_date'] ?? '');
            $guest_count         = filter_var($_POST['guest_count'] ?? '', FILTER_VALIDATE_INT);
            $total_price         = filter_var($_POST['total_price'] ?? '', FILTER_VALIDATE_FLOAT);
            $detail              = SecurityHelper::sanitize($_POST['detail'] ?? '');
            $accept_conditions   = isset($_POST['accept_conditions']);

            if (!$menu_id)                                              $errors[] = 'Menu invalide.';
            if (!$delivery_address_id)                                  $errors[] = 'Adresse de livraison invalide.';
            if (!$delivery_date || strtotime($delivery_date) <= time()) $errors[] = 'Date de livraison invalide.';
            if (!$guest_count || $guest_count < 1)                      $errors[] = 'Nombre de personnes invalide.';
            if (!$total_price || $total_price <= 0)                     $errors[] = 'Prix total invalide.';
            if (!$accept_conditions)                                    $errors[] = 'Vous devez accepter les conditions.';

            $addresses = (new AddressModel($pdo))->findByUserId($_SESSION['user_id']);
            $validIds  = array_column($addresses, 'id');

            $pdo->beginTransaction();

            try {
                // Nouvelle adresse de livraison
                if ($delivery_address_id === 'new') {
                    $newAddr = [
                        'name'        => SecurityHelper::sanitize($_POST['new_delivery_name'] ?? ''),
                        'number'      => SecurityHelper::sanitize($_POST['new_delivery_number'] ?? ''),
                        'street'      => SecurityHelper::sanitize($_POST['new_delivery_street'] ?? ''),
                        'postal_code' => SecurityHelper::sanitize($_POST['new_delivery_postal'] ?? ''),
                        'city'        => SecurityHelper::sanitize($_POST['new_delivery_city'] ?? ''),
                        'country'     => 'France',
                    ];
                    if (!$newAddr['name'] || !$newAddr['number'] || !$newAddr['street'] || !$newAddr['postal_code'] || !$newAddr['city']) {
                        $errors[] = 'Tous les champs de la nouvelle adresse de livraison sont obligatoires.';
                    } else {
                        $newAddrResult       = $address->create($newAddr);
                        $delivery_address_id = $newAddrResult['id'] ?? null;
                        if (!$delivery_address_id) $errors[] = 'Erreur lors de la création de l\'adresse de livraison.';
                    }
                } else {
                    $delivery_address_id = filter_var($delivery_address_id, FILTER_VALIDATE_INT);
                    if (!in_array($delivery_address_id, $validIds)) $errors[] = 'Adresse de livraison non autorisée.';
                }

                // Nouvelle adresse de facturation
                if ($billing_address_id === 'new') {
                    $newBilling = [
                        'name'        => SecurityHelper::sanitize($_POST['new_billing_name'] ?? ''),
                        'number'      => SecurityHelper::sanitize($_POST['new_billing_number'] ?? ''),
                        'street'      => SecurityHelper::sanitize($_POST['new_billing_street'] ?? ''),
                        'postal_code' => SecurityHelper::sanitize($_POST['new_billing_postal'] ?? ''),
                        'city'        => SecurityHelper::sanitize($_POST['new_billing_city'] ?? ''),
                        'country'     => 'France',
                    ];
                    if (!$newBilling['name'] || !$newBilling['number'] || !$newBilling['street'] || !$newBilling['postal_code'] || !$newBilling['city']) {
                        $errors[] = 'Tous les champs de la nouvelle adresse de facturation sont obligatoires.';
                    } else {
                        $newBillingResult   = $address->create($newBilling);
                        $billing_address_id = $newBillingResult['id'] ?? null;
                        if (!$billing_address_id) $errors[] = 'Erreur lors de la création de l\'adresse de facturation.';
                    }
                } elseif ($billing_address_id) {
                    $billing_address_id = filter_var($billing_address_id, FILTER_VALIDATE_INT);
                    if (!in_array($billing_address_id, $validIds)) $errors[] = 'Adresse de facturation non autorisée.';
                } else {
                    $billing_address_id = $delivery_address_id;
                }

                if (!empty($errors)) {
                    $pdo->rollBack();
                    $error = implode('<br>', $errors);
                    $menu  = (new MenuModel($pdo))->findById($menu_id);
                    require_once '../src/views/client/order-form.php';
                    exit();
                }

                $data = [
                    'user_id'             => $_SESSION['user_id'],
                    'menu_id'             => $menu_id,
                    'delivery_address_id' => $delivery_address_id,
                    'billing_address_id'  => $billing_address_id,
                    'delivery_date'       => $delivery_date,
                    'guest_count'         => $guest_count,
                    'total_price'         => $total_price,
                    'detail'              => $detail,
                    'order_number'        => 'VG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                ];

                $result = $order->create($data);
                if (isset($result['success'])) {
                    $pdo->commit();
                    header('Location: /orders/' . $result['id']);
                    exit();
                }
            
                $pdo->rollBack();
                $error = $result['error'] ?? 'Une erreur est survenue';
                $menu  = (new MenuModel($pdo))->findById($menu_id);
                require_once '../src/views/client/order-form.php';

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Une erreur est survenue, veuillez réessayer.';
                $menu  = (new MenuModel($pdo))->findById($menu_id);
                require_once '../src/views/client/order-form.php';
            }
        }
        break;
    
    case '/reviews':
        if ($method === 'GET') {
            echo json_encode($review->getAll());
        }
        if ($method === 'POST') {
            SecurityHelper::requireLogin();
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($review->create($data));
        }
        break;

    case '/addresses':
        SecurityHelper::requireLogin();
        if ($method === 'GET') {
            echo json_encode($address->getMyAddresses());
        }
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($address->create($data));
        }
        break;
        
    case '/login':
        if ($method === 'GET') {
            require_once '../src/views/client/login.php';
        }
        if ($method === 'POST') {
            $result = $auth->login($_POST['email'], $_POST['password']);
            if (isset($result['success'])) {
                header('Location: /account');
                exit();
            }
            $error = $result['error'] ?? null;
            require_once '../src/views/client/login.php';
        }
        break;

    case '/register':
        if ($method === 'GET') {
            require_once '../src/views/client/register.php';
        }
        if ($method === 'POST') {
            $data = $_POST;
            $data['role_id'] = 5;

            // Début de la transaction (groupe d'opérations SQL lié entre elle. Succès commun ou échec commun. Pas de succès partiel si échec partiel)
            $pdo->beginTransaction();

            try {
                $result = $auth->register($data);
                if (isset($result['success'])) {
                    $_SESSION['user_id'] = $result['user_id'];
                    $addressData = [
                        'name' => 'Domicile',
                        'number' => $_POST['number'] ?? null,
                        'street' => $_POST['street'],
                        'complement' => $_POST['complement'] ?? null,
                        'postal_code' => $_POST['postal_code'],
                        'city' => $_POST['city'],
                        'country' => 'France'
                    ];
                    $address->create($addressData);
                    $pdo->commit();
                    header('Location: /login');
                    exit();
                }
                $pdo->rollBack();
                $error = $result['error'] ?? null;
                require_once '../src/views/client/register.php';
            } catch (Exeption $e) {
                $pdo->rollback();
                $error = 'Une erreur est survenue, veuillez réessayer';
                require_once '../src/views/client/register.php';
            }
        }    
        break;
        
    case '/forgot-password':
        if ($method === 'GET') {
            echo json_encode(['message' => 'Formulaire réinitialisation mot de passe']);
        }
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $auth->forgotPassword($data['email']);
            echo json_encode($result);
        }
        break;
        
    case '/contact':
        break;
    
    // Espace Client - Utilisateur connecté
    case '/account':
        SecurityHelper::requireLogin();
        require_once '../src/views/client/account.php';
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
    
    case '/logout':
        // Vider toutes les données de session
        $_SESSION = [];
        // Détruire le cookie de session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        // Détruire la session
        session_destroy();
        header('Location: /');
        exit();
        break;   
    
    // Page non trouvée
    default:
        http_response_code(404);
        break;
}