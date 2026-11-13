<?php

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/../dao/CartDao.php';

class CartService extends EntityService
{
    public function __construct(PDO $pdo)
    {
        parent::__construct(new CartDao($pdo), ['user_id'], ['user_id']);
    }

    protected function validateField(string $field, $value)
    {
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        if ($validated === false || $validated < 1) {
            throw new InvalidArgumentException('User id must be a positive integer.');
        }
        return $validated;
    }
}
