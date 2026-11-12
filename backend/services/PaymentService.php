<?php

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/../dao/PaymentDao.php';

class PaymentService extends EntityService
{
    private const STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    public function __construct(PDO $pdo)
    {
        parent::__construct(new PaymentDao($pdo), ['order_id', 'status'], ['order_id', 'status']);
    }

    protected function validateField(string $field, $value)
    {
        if ($field === 'order_id') {
            $validated = filter_var($value, FILTER_VALIDATE_INT);
            if ($validated === false || $validated < 1) {
                throw new InvalidArgumentException('Order id must be a positive integer.');
            }
            return $validated;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('Payment status must be a string.');
        }
        $value = strtolower(trim($value));
        if (!in_array($value, self::STATUSES, true)) {
            throw new InvalidArgumentException('Payment status must be pending, paid, failed, or refunded.');
        }
        return $value;
    }
}
