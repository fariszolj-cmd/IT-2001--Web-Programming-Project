<?php

abstract class BaseDao
{
    protected $pdo;
    protected $table;
    protected $primaryKey;
    protected $columns;

    protected function __construct(PDO $pdo, string $table, string $primaryKey, array $columns)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->columns = $columns;
    }

    public function findAll(): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM ' . $this->table . ' ORDER BY ' . $this->primaryKey
        );
        $statement->execute();
        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM ' . $this->table . ' WHERE ' . $this->primaryKey . ' = :id'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(array $data): array
    {
        $data = $this->validateData($data);
        $fields = array_keys($data);
        $placeholders = [];
        $parameters = [];

        foreach ($fields as $index => $field) {
            $placeholder = 'value_' . $index;
            $placeholders[] = ':' . $placeholder;
            $parameters[$placeholder] = $data[$field];
        }

        $sql = 'INSERT INTO ' . $this->table
            . ' (' . implode(', ', $fields) . ') VALUES ('
            . implode(', ', $placeholders) . ')';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function update(int $id, array $data): ?array
    {
        $data = $this->validateData($data);
        $assignments = [];
        $parameters = ['id' => $id];

        foreach ($data as $index => $value) {
            $placeholder = 'value_' . $index;
            $assignments[] = $index . ' = :' . $placeholder;
            $parameters[$placeholder] = $value;
        }

        $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $assignments)
            . ' WHERE ' . $this->primaryKey . ' = :id';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM ' . $this->table . ' WHERE ' . $this->primaryKey . ' = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function validateData(array $data): array
    {
        if ($data === []) {
            throw new InvalidArgumentException('At least one field is required.');
        }

        foreach ($data as $field => $value) {
            if (!is_string($field) || !in_array($field, $this->columns, true)) {
                throw new InvalidArgumentException('Unknown or non-writable field.');
            }
            if (!is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException('Field values must be scalar or null.');
            }
        }

        return $data;
    }
}