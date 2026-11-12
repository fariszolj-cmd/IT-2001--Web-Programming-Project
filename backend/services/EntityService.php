<?php

require_once __DIR__ . '/../dao/BaseDao.php';

abstract class EntityService
{
    protected BaseDao $dao;
    private array $writableFields;
    private array $requiredFields;

    public function __construct(BaseDao $dao, array $writableFields, array $requiredFields)
    {
        $this->dao = $dao;
        $this->writableFields = $writableFields;
        $this->requiredFields = $requiredFields;
    }

    public function findAll(): array
    {
        return $this->dao->findAll();
    }

    public function findById(int $id): ?array
    {
        return $this->dao->findById($id);
    }

    public function create(array $data): array
    {
        return $this->dao->create($this->validate($data, true));
    }

    public function update(int $id, array $data): ?array
    {
        return $this->dao->update($id, $this->validate($data, false));
    }

    public function delete(int $id): bool
    {
        return $this->dao->delete($id);
    }

    private function validate(array $data, bool $creating): array
    {
        if ($data === []) {
            throw new InvalidArgumentException('At least one field is required.');
        }

        foreach ($data as $field => $value) {
            if (!is_string($field) || !in_array($field, $this->writableFields, true)) {
                throw new InvalidArgumentException('Unknown or non-writable field.');
            }
            $data[$field] = $this->validateField($field, $value);
        }

        if ($creating) {
            foreach ($this->requiredFields as $field) {
                if (!array_key_exists($field, $data)) {
                    throw new InvalidArgumentException('Missing required field: ' . $field . '.');
                }
            }
        }

        return $data;
    }

    abstract protected function validateField(string $field, $value);
}
