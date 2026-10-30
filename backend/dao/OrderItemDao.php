<?php

require_once __DIR__ . '/BaseDao.php';

class OrderItemDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'order_items', 'item_id', ['order_id', 'product_id', 'quantity']);
    }
}