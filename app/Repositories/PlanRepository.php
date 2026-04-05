<?php
declare(strict_types=1);

namespace App\Repositories;

final class PlanRepository extends BaseRepository
{
    public function listActive(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM plans WHERE status='active' AND deleted_at IS NULL ORDER BY display_order ASC");
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne('SELECT * FROM plans WHERE slug = :slug AND deleted_at IS NULL LIMIT 1', ['slug' => $slug]);
    }
}
