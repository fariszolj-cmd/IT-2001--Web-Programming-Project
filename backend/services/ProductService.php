<?php

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/../dao/ProductDao.php';

class ProductService extends EntityService
{
    public function __construct(PDO $pdo)
    {
        parent::__construct(new ProductDao($pdo), ['category_id', 'name', 'price'], ['category_id', 'name', 'price']);
    }

    protected function validateField(string $field, $value)
    {
        if ($field === 'category_id') {
            return self::positiveInteger($value, 'Category id');
        }
        if ($field === 'name') {
            if (!is_string($value)) {
                throw new InvalidArgumentException('Product name must be a string.');
            }
            $value = trim($value);
            if ($value === '' || strlen($value) > 180) {
                throw new InvalidArgumentException('Product name must be between 1 and 180 characters.');
            }
            return $value;
        }
        if (!is_numeric($value) || !is_finite((float) $value) || (float) $value < 0 || (float) $value > 99999999.99) {
            throw new InvalidArgumentException('Price must be a non-negative number within the supported range.');
        }

        return number_format((float) $value, 2, '.', '');
    }

    private static function positiveInteger($value, string $label): int
    {
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        if ($validated === false || $validated < 1) {
            throw new InvalidArgumentException($label . ' must be a positive integer.');
        }
        return $validated;
    }
}
