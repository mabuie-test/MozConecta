<?php
declare(strict_types=1);

namespace App\Repositories;

final class TagRepository extends BaseRepository
{
    public function listByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tags WHERE tenant_id=:tenant_id ORDER BY name ASC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function findOrCreate(int $tenantId, string $name): int
    {
        $row = $this->fetchOne('SELECT id FROM tags WHERE tenant_id=:tenant_id AND name=:name LIMIT 1', [
            'tenant_id' => $tenantId,
            'name' => $name,
        ]);
        if ($row) {
            return (int)$row['id'];
        }

        $this->execute('INSERT INTO tags (tenant_id,name,created_at,updated_at) VALUES (:tenant_id,:name,NOW(),NOW())', [
            'tenant_id' => $tenantId,
            'name' => $name,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function syncContactTags(int $contactId, array $tagIds): void
    {
        $this->execute('DELETE FROM contact_tags WHERE contact_id=:contact_id', ['contact_id' => $contactId]);
        foreach ($tagIds as $tagId) {
            $this->execute('INSERT INTO contact_tags (contact_id,tag_id,created_at) VALUES (:contact_id,:tag_id,NOW())', [
                'contact_id' => $contactId,
                'tag_id' => (int)$tagId,
            ]);
        }
    }

    public function tagsForContact(int $contactId): array
    {
        $stmt = $this->pdo->prepare('SELECT t.* FROM tags t INNER JOIN contact_tags ct ON ct.tag_id=t.id WHERE ct.contact_id=:contact_id ORDER BY t.name ASC');
        $stmt->execute(['contact_id' => $contactId]);
        return $stmt->fetchAll();
    }
}
