<?php

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/../dao/CategoryDao.php';

class CategoryService extends EntityService
{
    public function __construct(PDO $pdo)
    {
        parent::__construct(new CategoryDao($pdo), ['name'], ['name']);
    }

    protected function validateField(string $field, $value)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Category name must be a string.');
        }
        $value = trim($value);
        if ($value === '' || strlen($value) > 120) {
            throw new InvalidArgumentException('Category name must be between 1 and 120 characters.');
        }

        return $value;
    }
}
