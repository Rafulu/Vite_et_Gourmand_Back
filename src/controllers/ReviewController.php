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
}