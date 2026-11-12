<?php

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/../dao/OrderDao.php';

class OrderService extends EntityService
{
    public function __construct(PDO $pdo)
    {
        parent::__construct(new OrderDao($pdo), ['user_id', 'order_date'], ['user_id', 'order_date']);
    }

    protected function validateField(string $field, $value)
    {
        if ($field === 'user_id') {
            $validated = filter_var($value, FILTER_VALIDATE_INT);
            if ($validated === false || $validated < 1) {
                throw new InvalidArgumentException('User id must be a positive integer.');
            }
            return $validated;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('Order date must use YYYY-MM-DD format.');
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException('Order date must use YYYY-MM-DD format.');
        }
        return $value;
    }
}
