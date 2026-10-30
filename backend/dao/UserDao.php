<?php

require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'users', 'user_id', ['name', 'email']);
    }
}