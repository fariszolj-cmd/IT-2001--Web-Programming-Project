<?php

require_once __DIR__ . '/BaseDao.php';

class CategoryDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'categories', 'category_id', ['name']);
    }
}