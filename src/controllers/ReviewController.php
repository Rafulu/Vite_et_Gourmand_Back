<?php

class ReviewController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupère tous les avis
    public function getAll() {
        $reviewModel = new ReviewModel($this->pdo);
        return $reviewModel->findAll();
    }

    // Créer un avis
    public function create($data) {
        $data['user_id'] = $_SESSION['user_id'];
        $reviewModel = new ReviewModel($this->pdo);
        $id = $reviewModel->create($data);
        return ['success' => true, 'id' => $id];
    }

    // Récupérer les avis par User
    public function getMyReviews() {
        $reviewModel = new ReviewModel($this->pdo);
        return $reviewModel->findByUserId($_SESSION['user_id']);
    }

    public function getPending() {
        $reviewModel = new ReviewModel($this->pdo);
        return $reviewModel->findPending();
    }

    public function validate($id) {
        $reviewModel = new ReviewModel($this->pdo);
        $reviewModel->validate($id);
        return ['success' => true];
    }

    public function reject($id) {
        $reviewModel = new ReviewModel($this->pdo);
        $reviewModel->reject($id);
        return ['success' => true];
    }

    public function handleSubmit($order_id) {
        $reviewModel = new ReviewModel($this->pdo);
        $user_id = $_SESSION['user_id'];

        $order = $reviewModel->findEligibleOrder($order_id, $user_id);
        if (!$order) {
            header('Location: /my-reviews');
            exit();
        }

        $existing = $reviewModel->findByOrderId($order_id);
        if ($existing) {
            header('Location: /my-reviews');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
            $note = (int)$_POST['note'];
            $comment = SecurityHelper::sanitize($_POST['comment'] ?? '');

            if ($note < 1 || $note > 5) {
                $error = 'La note doit être entre 1 et 5.';
            } else {
                $reviewModel->create([
                    'user_id'  => $user_id,
                    'order_id' => $order_id,
                    'note'     => $note,
                    'comment'  => $comment,
                ]);
                header('Location: /my-reviews');
                exit();
            }
        }

        require_once '../src/views/client/submit-review.php';
    }

    public function getEligibleOrders() {
        $reviewModel = new ReviewModel($this->pdo);
        $user_id = $_SESSION['user_id'];
        $orders = $reviewModel->findEligibleOrdersWithoutReview($user_id);
        return $orders;
    }
}