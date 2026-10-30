<?php

require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/CategoryDao.php';
require_once __DIR__ . '/../dao/CartDao.php';
require_once __DIR__ . '/../dao/OrderDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/PaymentDao.php';
require_once __DIR__ . '/../dao/OrderItemDao.php';

class ApiService
{
    private $daos;

    public function __construct(PDO $pdo)
    {
        $this->daos = [
            'users' => new UserDao($pdo),
            'categories' => new CategoryDao($pdo),
            'carts' => new CartDao($pdo),
            'orders' => new OrderDao($pdo),
            'products' => new ProductDao($pdo),
            'payments' => new PaymentDao($pdo),
            'order_items' => new OrderItemDao($pdo),
        ];
    }

    public function handle(string $entity, string $method, ?int $id, array $data): array
    {
        if (!isset($this->daos[$entity])) {
            return ['status' => 404, 'body' => ['error' => 'Resource not found.']];
        }

        $dao = $this->daos[$entity];
        if ($method === 'GET') {
            if ($id === null) {
                return ['status' => 200, 'body' => $dao->findAll()];
            }

            $record = $dao->findById($id);
            return $record === null
                ? ['status' => 404, 'body' => ['error' => 'Record not found.']]
                : ['status' => 200, 'body' => $record];
        }

        if ($method === 'POST' && $id === null) {
            return ['status' => 201, 'body' => $dao->create($data)];
        }

        if (($method === 'PUT' || $method === 'PATCH') && $id !== null) {
            $record = $dao->update($id, $data);
            return $record === null
                ? ['status' => 404, 'body' => ['error' => 'Record not found.']]
                : ['status' => 200, 'body' => $record];
        }

        if ($method === 'DELETE' && $id !== null) {
            return $dao->delete($id)
                ? ['status' => 204, 'body' => null]
                : ['status' => 404, 'body' => ['error' => 'Record not found.']];
        }

        return ['status' => 405, 'body' => ['error' => 'Method not allowed for this resource URL.']];
    }
}