<?php
declare(strict_types=1);

namespace App\Repositories;

final class CampaignContactRepository extends BaseRepository
{
    public function bulkCreate(int $tenantId, int $campaignId, array $contactIds): void
    {
        foreach ($contactIds as $contactId) {
            $this->execute('INSERT IGNORE INTO campaign_contacts (tenant_id,campaign_id,contact_id,status,created_at,updated_at) VALUES (:tenant_id,:campaign_id,:contact_id,\'pending\',NOW(),NOW())', [
                'tenant_id' => $tenantId,
                'campaign_id' => $campaignId,
                'contact_id' => (int)$contactId,
            ]);
        }
    }

    public function pendingBatch(int $tenantId, int $campaignId, int $batchSize): array
    {
        $stmt = $this->pdo->prepare('SELECT cc.*, c.phone, c.display_name FROM campaign_contacts cc INNER JOIN contacts c ON c.id=cc.contact_id WHERE cc.tenant_id=:tenant_id AND cc.campaign_id=:campaign_id AND cc.status=\'pending\' ORDER BY cc.id ASC LIMIT :lim');
        $stmt->bindValue(':tenant_id', $tenantId, \PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $batchSize, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markSent(int $id): void
    {
        $this->execute('UPDATE campaign_contacts SET status=\'sent\', sent_at=NOW(), updated_at=NOW() WHERE id=:id', ['id' => $id]);
    }

    public function markFailed(int $id, string $error): void
    {
        $this->execute('UPDATE campaign_contacts SET status=\'failed\', error_message=:error, updated_at=NOW() WHERE id=:id', ['id' => $id, 'error' => $error]);
    }

    public function report(int $tenantId, int $campaignId): array
    {
        $stmt = $this->pdo->prepare('SELECT status, COUNT(*) AS total FROM campaign_contacts WHERE tenant_id=:tenant_id AND campaign_id=:campaign_id GROUP BY status');
        $stmt->execute(['tenant_id' => $tenantId, 'campaign_id' => $campaignId]);
        return $stmt->fetchAll();
    }
}
