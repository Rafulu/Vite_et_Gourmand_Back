<?php

class MongoDBHelper {

    private $collection;

    public function __construct() {
        try {
            $db = getMongoDB();
            $this->collection = $db->selectCollection('order_stats');
        } catch (Exception $e) {
            error_log('MongoDB connection error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function syncFromMariaDB(PDO $pdo): void {
        try {
            $stmt = $pdo->prepare("
                SELECT o.id, o.menu_id, m.name AS menu_name, o.guest_count, o.total_price, o.status, o.order_date
                FROM orders o
                JOIN menus m ON o.menu_id = m.id
            ");
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($orders as $order) {
                $this->collection->updateOne(
                    ['order_id' => (int)$order['id']],
                    ['$set' => [
                        'order_id'    => (int)$order['id'],
                        'menu_id'     => (int)$order['menu_id'],
                        'menu_name'   => $order['menu_name'],
                        'guest_count' => (int)$order['guest_count'],
                        'total_price' => (float)$order['total_price'],
                        'status'      => $order['status'],
                        'order_date'  => $order['order_date'],
                    ]],
                    ['upsert' => true]
                );
            }
        } catch (Exception $e) {
            error_log('MongoDB sync error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getStatsByMenu(): array {
        try {
            return $this->collection->aggregate([
                ['$group' => [
                    '_id'          => '$menu_name',
                    'nb_commandes' => ['$sum' => 1],
                    'ca_total'     => ['$sum' => '$total_price'],
                ]],
                ['$sort' => ['nb_commandes' => -1]],
            ])->toArray();
        } catch (Exception $e) {
            error_log('MongoDB aggregate error: ' . $e->getMessage());
            throw $e;
        }
    }
}