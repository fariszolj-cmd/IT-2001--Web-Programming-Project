<?php

require_once __DIR__ . '/BaseDao.php';

class PaymentDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'payments', 'payment_id', ['order_id', 'status']);
    }
}