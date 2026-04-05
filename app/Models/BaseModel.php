<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

abstract class BaseModel
{
    protected string $table;

    public function __construct(protected readonly PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
