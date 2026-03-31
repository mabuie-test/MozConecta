<?php
declare(strict_types=1);

namespace App\Repositories;

final class PlanRepository extends BaseRepository
{
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM plans WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function allActive(): array
    {
        return $this->pdo->query("SELECT * FROM plans WHERE status='active' ORDER BY display_order")->fetchAll();
    }
}
