<?php

require_once __DIR__ . '/BaseDao.php';

class ProductDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'products', 'product_id', ['category_id', 'name', 'price']);
    }
}