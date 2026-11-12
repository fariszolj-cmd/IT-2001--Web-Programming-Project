<?php

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/../dao/OrderItemDao.php';

class OrderItemService extends EntityService
{
    public function __construct(PDO $pdo)
    {
        parent::__construct(
            new OrderItemDao($pdo),
            ['order_id', 'product_id', 'quantity'],
            ['order_id', 'product_id', 'quantity']
        );
    }

    protected function validateField(string $field, $value)
    {
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        if ($validated === false || $validated < 1) {
            throw new InvalidArgumentException(ucfirst(str_replace('_', ' ', $field)) . ' must be a positive integer.');
        }
        return $validated;
    }
}
