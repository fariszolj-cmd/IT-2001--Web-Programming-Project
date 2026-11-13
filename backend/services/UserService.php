<?php

require_once __DIR__ . '/EntityService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends EntityService
{
    public function __construct(PDO $pdo)
    {
        parent::__construct(new UserDao($pdo), ['name', 'email'], ['name', 'email']);
    }

    protected function validateField(string $field, $value)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException($field . ' must be a string.');
        }
        $value = trim($value);

        if ($field === 'name' && ($value === '' || strlen($value) > 120)) {
            throw new InvalidArgumentException('Name must be between 1 and 120 characters.');
        }
        if ($field === 'email' && (strlen($value) > 190 || filter_var($value, FILTER_VALIDATE_EMAIL) === false)) {
            throw new InvalidArgumentException('A valid email address of at most 190 characters is required.');
        }

        return $value;
    }
}
