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
require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../config/mongodb.php';

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

// Helpers: Fonctions utilitaires d'aide
require_once '../src/helpers/SecurityHelper.php';
require_once '../src/helpers/MongoDBHelper.php';
require_once '../src/helpers/MailHelper.php';



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

if (preg_match('/^\/menus\/capacity$/', $url) && $method === 'GET') {
    header('Content-Type: application/json');

    $menu_id = filter_var($_GET['menu_id'] ?? '', FILTER_VALIDATE_INT);
    $date    = SecurityHelper::sanitize($_GET['date'] ?? '');

    if (!$menu_id || !$date || strtotime($date) <= time()) {
        echo json_encode(['error' => 'Paramètres invalides']);
        exit();
    }

    // Temps de préparation du menu (somme des plats)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(d.preparation_time), 0) as total_prep_time
        FROM composition_menu cm
        JOIN dishes d ON cm.dish_id = d.id
        WHERE cm.menu_id = :menu_id AND d.is_active = 1
    ");
    $stmt->execute([':menu_id' => $menu_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $prep_time = (int)$row['total_prep_time'];

    if ($prep_time <= 0) {
        echo json_encode(['error' => 'Temps de préparation non défini']);
        exit();
    }

    // Capacité journalière depuis settings
    $stmtS = $pdo->query("SELECT value FROM settings WHERE setting_key = 'daily_capacity_minutes' LIMIT 1");
    $daily = (int)($stmtS->fetchColumn() ?: 840);

    // Calcul sur les 4 jours avant la date de livraison
    $total_available = 0;
    $delivery        = new DateTime($date);

    for ($i = 4; $i >= 1; $i--) {
        $day = clone $delivery;
        $day->modify("-{$i} days");
        $dayStr = $day->format('Y-m-d');

        $stmtP = $pdo->prepare("SELECT max_capacity, used_capacity FROM production_planning WHERE date = :date");
        $stmtP->execute([':date' => $dayStr]);
        $planning = $stmtP->fetch(PDO::FETCH_ASSOC);

        if ($planning) {
            $total_available += max(0, $planning['max_capacity'] - $planning['used_capacity']);
        } else {
            $total_available += $daily;
        }
    }

    // Calcul parts
    $parts = (int)floor($total_available / $prep_time);

    // Réponse
    $menu_min = 0;
    $stmtM = $pdo->prepare("SELECT min_guests FROM menus WHERE id = :id");
    $stmtM->execute([':id' => $menu_id]);
    $menu_min = (int)($stmtM->fetchColumn() ?: 0);

    if ($parts < $menu_min) {
        echo json_encode(['status' => 'unavailable', 'message' => 'Indisponible']);
    } elseif ($parts > 100) {
        echo json_encode(['status' => 'available', 'message' => 'Plus de 100 places disponibles']);
    } else {
        echo json_encode(['status' => 'available', 'message' => $parts . ' places disponibles']);
    }
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

    $stmt = $pdo->prepare("
        SELECT c.* FROM conditions c
        JOIN condition_menu cm ON c.id = cm.condition_id
        WHERE cm.menu_id = :menu_id
    ");
    $stmt->execute([':menu_id' => $id]);
    $conditions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->query("SELECT * FROM resources ORDER BY type, name");
    $resources = $stmt2->fetchAll(PDO::FETCH_ASSOC);

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

// Routes dynamiques pour le détail d'une commande coté employé
if (preg_match('/^\/employee\/orders\/(\d+)$/', $url, $matches)) {
    SecurityHelper::requireRole([1, 2, 3, 4, 6]);
    $id = $matches[1];
    $orderData = $order->getById($id);
    if (!$orderData) {
        http_response_code(404);
        echo "Commande non trouvée";
        exit();
    }
    $userModel = new UserModel($pdo);
    $cooks = array_merge($userModel->findByRole(3), $userModel->findByRole(6));
    $drivers = array_merge($userModel->findByRole(4), $userModel->findByRole(6));
    $managers = array_merge($userModel->findByRole(1), $userModel->findByRole(2));
    require_once '../src/views/employee/order-detail.php';
    exit();
}

// Changement de statut
if (preg_match('/^\/employee\/orders\/(\d+)\/status$/', $url, $matches)) {
    SecurityHelper::requireRole([1, 2, 3, 4, 6]);
    SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $id = $matches[1];
    $result = $order->updateStatus($id, $_POST);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch') {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
    if (isset($result['success'])) {
        header('Location: /employee/orders/' . $id);
    } else {
        header('Location: /employee/orders/' . $id . '?error=' . urlencode($result['error']));
    }
    exit();
}

// Attribution cuisinier/livreur
if (preg_match('/^\/employee\/orders\/(\d+)\/assign$/', $url, $matches)) {
    SecurityHelper::requireRole([1, 2, 6]);
    SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $id = $matches[1];
    $result = $order->assign($id, $_POST);
    header('Location: /employee/orders/' . $id);
    exit();
}

// Auto-attribution
if (preg_match('/^\/employee\/orders\/(\d+)\/self-assign$/', $url, $matches)) {
    SecurityHelper::requireRole([3, 4, 6]);
    SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $id = $matches[1];
    $result = $order->selfAssign($id, $_POST['role']);
    header('Location: /employee/orders/' . $id);
    exit();
}

// Commentaire interne
if (preg_match('/^\/employee\/orders\/(\d+)\/comment$/', $url, $matches)) {
    SecurityHelper::requireRole([1, 2, 3, 4, 6]);
    SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $id = $matches[1];
    $result = $order->addComment($id, $_POST['comment']);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
    header('Location: /employee/orders/' . $id);
    exit();
}

// Gestion formulaire Avis
if (preg_match('/^\/reviews\/submit\/(\d+)$/', $url, $matches)) {
    SecurityHelper::requireLogin();
    $order_id = $matches[1];
    $review->handleSubmit($order_id);
    exit();
}

// Gestion Avis client
if (preg_match('/^\/employee\/reviews\/(\d+)\/(validate|reject)$/', $url, $matches)) {
    SecurityHelper::requireRole([1, 2, 6]);
    SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $id = $matches[1];
    $action = $matches[2];
    $review->$action($id);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
    header('Location: /employee/reviews');
    exit();
}

// Route dynamiques gestion employés
if (preg_match('/^\/admin\/employees\/(\d+)\/toggle$/', $url, $matches)) {
    SecurityHelper::requireRole([1]);
    SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $id = $matches[1];
    $userModel = new UserModel($pdo);
    $userModel->toggleBlock($id);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
    header('Location: /admin/employees');
    exit();
}

// Stats JSON - fetch
if ($url === '/admin/stats/data' && $method === 'GET') {
    SecurityHelper::requireRole(1);
    $days      = filter_var($_GET['period'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    $menu      = SecurityHelper::sanitize($_GET['menu'] ?? '');
    $dateStart = SecurityHelper::sanitize($_GET['date_start'] ?? '');
    $dateEnd   = SecurityHelper::sanitize($_GET['date_end'] ?? '');
    $mongoHelper = new MongoDBHelper();
    $mongoHelper->syncFromMariaDB($pdo);
    $stats = $mongoHelper->getStatsByMenu($days, $menu, $dateStart, $dateEnd);
    header('Content-Type: application/json');
    echo json_encode($stats);
    exit();
}

switch($url) {

    //Pages publiques
    case '/':
        $reviewModel = new ReviewModel($pdo);
        $validatedReviews = $reviewModel->findValidated();
        require_once '../src/views/home.php';
        break;

    // Page non trouvée
    default:
        http_response_code(404);
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
            $menu_price     = filter_var($_POST['menu_price'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;
            $delivery_price = filter_var($_POST['delivery_price'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;
            $option_price   = filter_var($_POST['option_price'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;
            $guest_count         = filter_var($_POST['guest_count'] ?? '', FILTER_VALIDATE_INT);
            $discount            = (!empty($_POST['discount']) && $_POST['discount'] == 1) ? 1 : 0;
            $total_price         = filter_var($_POST['total_price'] ?? '', FILTER_VALIDATE_FLOAT);
            $detail              = SecurityHelper::sanitize($_POST['detail'] ?? '');
            $accept_conditions   = isset($_POST['accept_conditions']);
            $accept_cgv = isset($_POST['accept_cgv']);

            if (!$menu_id)                                              $errors[] = 'Menu invalide.';
            if (!$delivery_address_id)                                  $errors[] = 'Adresse de livraison invalide.';
            if (!$delivery_date || strtotime($delivery_date) <= time()) $errors[] = 'Date de livraison invalide.';
            if (!$guest_count || $guest_count < 1)                      $errors[] = 'Nombre de personnes invalide.';
            if (!$total_price || $total_price <= 0)                     $errors[] = 'Prix total invalide.';
            if (!$accept_conditions)                                    $errors[] = 'Vous devez accepter les conditions.';
            if (!$accept_cgv)                                           $errors[] = 'Vous devez accepter les CGV.';

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
                    $addresses = (new AddressModel($pdo))->findByUserId($_SESSION['user_id']);
                    $stmt2 = $pdo->query("SELECT * FROM resources ORDER BY type, name");
                    $resources = $stmt2 ? $stmt2->fetchAll(PDO::FETCH_ASSOC) : [];
                    $stmtC = $pdo->prepare("SELECT c.* FROM conditions c JOIN condition_menu cm ON c.id = cm.condition_id WHERE cm.menu_id = :menu_id");
                    $stmtC->execute([':menu_id' => $menu_id]);
                    $conditions = $stmtC->fetchAll(PDO::FETCH_ASSOC);
                    require_once '../src/views/client/order-form.php';
                    exit();
                }

                $data = [
                    'user_id'             => $_SESSION['user_id'],
                    'menu_id'             => $menu_id,
                    'delivery_address_id' => $delivery_address_id,
                    'billing_address_id'  => $billing_address_id,
                    'delivery_date'       => $delivery_date,
                    'menu_price'          => $menu_price,
                    'delivery_price'      => $delivery_price,
                    'option_price'        => $option_price,
                    'equipement_loan'     => $equipement_loan,
                    'equipement_return'   => 0,
                    'guest_count'         => $guest_count,
                    'discount'            => $discount,
                    'total_price'         => $total_price,
                    'detail'              => $detail,
                    'order_number'        => 'VG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                ];

                $result = $order->create($data);
                if (isset($result['success'])) {
                    $pdo->commit();
                    // Bloquer la capacité de production sur 4 jours
                    $stmtPrep = $pdo->prepare("
                    SELECT COALESCE(SUM(d.preparation_time), 0) as total_prep_time
                    FROM composition_menu cm
                    JOIN dishes d ON cm.dish_id = d.id
                    WHERE cm.menu_id = :menu_id AND d.is_active = 1
                ");
                $stmtPrep->execute([':menu_id' => $menu_id]);
                $prep_time = (int)$stmtPrep->fetchColumn();

                $stmtCap = $pdo->query("SELECT value FROM settings WHERE setting_key = 'daily_capacity_minutes' LIMIT 1");
                $daily = (int)($stmtCap->fetchColumn() ?: 840);

                $total_needed = $prep_time * $guest_count;
                $per_day      = (int)ceil($total_needed / 4);

                $delivery = new DateTime($delivery_date);
                for ($i = 4; $i >= 1; $i--) {
                    $day = clone $delivery;
                    $day->modify("-{$i} days");
                    $dayStr = $day->format('Y-m-d');

                    $stmtCheck = $pdo->prepare("SELECT id, used_capacity FROM production_planning WHERE date = :date");
                    $stmtCheck->execute([':date' => $dayStr]);
                    $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                    $stmtUpdate = $pdo->prepare("UPDATE production_planning SET used_capacity = used_capacity + :used WHERE date = :date");
                    $stmtUpdate->execute([':used' => $per_day, ':date' => $dayStr]);
                    } else {    
                    $stmtInsert = $pdo->prepare("INSERT INTO production_planning (date, max_capacity, used_capacity) VALUES (:date, :max, :used)");
                    $stmtInsert->execute([':date' => $dayStr, ':max' => $daily, ':used' => $per_day]);
                    }
                }
                    header('Location: /orders/' . $result['id']);
                    exit();
                }
            
                $pdo->rollBack();
                $error = $result['error'] ?? 'Une erreur est survenue';
                $menu  = (new MenuModel($pdo))->findById($menu_id);
                $addresses = (new AddressModel($pdo))->findByUserId($_SESSION['user_id']);
                $stmt2 = $pdo->query("SELECT * FROM resources ORDER BY type, name");
                $resources = $stmt2 ? $stmt2->fetchAll(PDO::FETCH_ASSOC) : [];
                $stmtC = $pdo->prepare("SELECT c.* FROM conditions c JOIN condition_menu cm ON c.id = cm.condition_id WHERE cm.menu_id = :menu_id");
                $stmtC->execute([':menu_id' => $menu_id]);
                $conditions = $stmtC->fetchAll(PDO::FETCH_ASSOC);
                require_once '../src/views/client/order-form.php';

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = $e->getMessage();
                $menu  = (new MenuModel($pdo))->findById($menu_id);
                $addresses = (new AddressModel($pdo))->findByUserId($_SESSION['user_id']);
                $stmt2 = $pdo->query("SELECT * FROM resources ORDER BY type, name");
                $resources = $stmt2 ? $stmt2->fetchAll(PDO::FETCH_ASSOC) : [];
                $conditions = [];
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

                    // Mail de Bienvenue
                    MailHelper::sendWelcome($result['email'], $result['first_name']);

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
        if ($method === 'GET') {
            require_once '../src/views/contact.php';
        }
        if ($method === 'POST') {
            SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
            $subject = SecurityHelper::sanitize($_POST['subject'] ?? '');
            $message = SecurityHelper::sanitize($_POST['message'] ?? '');
            $email   = SecurityHelper::sanitize($_POST['email'] ?? '');

            if (!SecurityHelper::validateEmail($email) || strlen($subject) < 3 || strlen($message) < 10) {
                $error = 'Veuillez vérifier les champs du formulaire.';
                require_once '../src/views/contact.php';
                break;
            }

            // TODO: PHPMailer - envoyer mail à l'entreprise
            $success = true;
            require_once '../src/views/contact.php';
        }
        break;
    
    // Espace Utilisateur connecté
    case '/account':
        SecurityHelper::requireLogin();
        $role = $_SESSION['role_id'];
        if ($role === 1) {
            header('Location: /admin');
            exit();
        }
        if (in_array($role, [2, 3, 4, 6])) {
            header('Location: /employee');
            exit();
        }
        $userModel = new UserModel($pdo);
        $user = $userModel->findById($_SESSION['user_id']);
        require_once '../src/views/client/account.php';
        break;
    
    case '/account/edit':
    SecurityHelper::requireLogin();
    if ($method === 'GET') {
        $userModel = new UserModel($pdo);
        $user = $userModel->findById($_SESSION['user_id']);
        require_once '../src/views/client/account-edit.php';
    }
    if ($method === 'POST') {
        SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
        $result = $auth->updateProfile($_SESSION['user_id'], $_POST);
        if (isset($result['success'])) {
            header('Location: /account');
            exit();
        }
        $error = $result['error'] ?? null;
        $user = $_POST;
        require_once '../src/views/client/account-edit.php';
    }
    break;    
    
    case '/my-orders':
        SecurityHelper::requireLogin();
        $orders = $order->getMyOrders();
        require_once '../src/views/client/my-orders.php';
        break;

    case '/my-reviews':
        SecurityHelper::requireLogin();
        $reviews = $review->getMyReviews();
        $eligibleOrders = $review->getEligibleOrders();
        require_once '../src/views/client/my-reviews.php';
        break;

    // Espace Employé
    case '/employee':
        SecurityHelper::requireRole([1, 2, 3, 4, 6]);
        require_once '../src/views/employee/dashboard-employee.php';
        break;

    case '/employee/orders':
        SecurityHelper::requireRole([1, 2, 3, 4, 6]);
        $orders = $order->getALL();
        $userModel = new UserModel($pdo);
        $cooks = array_merge(
            $userModel->findByRole(3),
            $userModel->findByRole(6)
        );
        $drivers = array_merge(
            $userModel->findByRole(4),
            $userModel->findByRole(6)
        );
        $managers = array_merge(
            $userModel->findByRole(1),
            $userModel->findByRole(2)
        );
        require_once '../src/views/employee/orders-management.php';
        break;

    case '/employee/reviews':
        SecurityHelper::requireRole([1, 2, 6]);
        $reviews = $review->getPending();
        require_once '../src/views/employee/reviews-management.php';
        break;

    // Esapce admin
    case '/admin':
        SecurityHelper::requireRole(1);
        require_once '../src/views/admin/dashboard-admin.php';
        break;
    
    case '/admin/employees':
        SecurityHelper::requireRole([1]);
        $userModel = new UserModel($pdo);
        $employees = $userModel->findAllEmployees();
        require_once '../src/views/admin/employees-management.php';
    break;
        

    case '/admin/employees/create':
        SecurityHelper::requireRole([1]);
        if ($method === 'GET') {
            $userModel = new UserModel($pdo);
            $roles = $userModel->findAllRoles();
            require_once '../src/views/admin/employee-create.php';
        }
        if ($method === 'POST') {
            SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
            $result = $auth->createEmployee($_POST);
            if (isset($result['success'])) {
                $_SESSION['temp_password'] = $result['temp_password'];
                $_SESSION['temp_email'] = $_POST['email'];
                header('Location: /admin/employees?success=1');
            } else {
                $error = $result['error'];
                $userModel = new UserModel($pdo);
                $roles = $userModel->findAllRoles();
                require_once '../src/views/admin/employee-create.php';
            }
            exit();
        }
        break;

    case '/admin/stats':
         SecurityHelper::requireLogin();
        SecurityHelper::requireRole(1);
        $mongoHelper = new MongoDBHelper();
        $mongoHelper->syncFromMariaDB($pdo);
        $stats = $mongoHelper->getStatsByMenu();
        require_once '../src/views/admin/stats.php';
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
}