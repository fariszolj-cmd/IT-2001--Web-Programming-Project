<?php

require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/CategoryService.php';
require_once __DIR__ . '/ProductService.php';
require_once __DIR__ . '/CartService.php';
require_once __DIR__ . '/OrderService.php';
require_once __DIR__ . '/PaymentService.php';
require_once __DIR__ . '/OrderItemService.php';

class ApiService
{
    private const ENTITY_NAMES = [
        'users',
        'categories',
        'products',
        'carts',
        'orders',
        'payments',
        'order_items',
    ];

    private array $services;

    public function __construct(PDO $pdo)
    {
        $this->services = [
            'users' => new UserService($pdo),
            'categories' => new CategoryService($pdo),
            'products' => new ProductService($pdo),
            'carts' => new CartService($pdo),
            'orders' => new OrderService($pdo),
            'payments' => new PaymentService($pdo),
            'order_items' => new OrderItemService($pdo),
        ];
    }

    public function handle(string $entity, string $method, ?int $id, array $data): array
    {
        if (!isset($this->services[$entity])) {
            return ['status' => 404, 'body' => ['error' => 'Resource not found.']];
        }

        $service = $this->services[$entity];
        if ($method === 'GET') {
            if ($id === null) {
                return ['status' => 200, 'body' => $service->findAll()];
            }
            $record = $service->findById($id);
            return $record === null
                ? ['status' => 404, 'body' => ['error' => 'Record not found.']]
                : ['status' => 200, 'body' => $record];
        }
        if ($method === 'POST' && $id === null) {
            return ['status' => 201, 'body' => $service->create($data)];
        }
        if (($method === 'PUT' || $method === 'PATCH') && $id !== null) {
            $record = $service->update($id, $data);
            return $record === null
                ? ['status' => 404, 'body' => ['error' => 'Record not found.']]
                : ['status' => 200, 'body' => $record];
        }
        if ($method === 'DELETE' && $id !== null) {
            return $service->delete($id)
                ? ['status' => 204, 'body' => null]
                : ['status' => 404, 'body' => ['error' => 'Record not found.']];
        }

        return ['status' => 400, 'body' => ['error' => 'Invalid method and resource URL combination.']];
    }

    public static function supportsEntity(string $entity): bool
    {
        return in_array($entity, self::ENTITY_NAMES, true);
    }
}
