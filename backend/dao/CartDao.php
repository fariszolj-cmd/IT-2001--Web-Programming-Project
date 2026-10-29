<?php

require_once __DIR__ . '/BaseDao.php';

class CartDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'carts', 'cart_id', ['user_id']);
    }
}