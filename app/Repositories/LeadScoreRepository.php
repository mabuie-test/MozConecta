<?php
declare(strict_types=1);

namespace App\Repositories;

final class LeadScoreRepository extends BaseRepository
{
    public function upsert(int $tenantId, int $contactId, int $score, string $reason): void
    {
        $this->execute('INSERT INTO lead_scores (tenant_id,contact_id,score,reason,last_calculated_at,created_at,updated_at) VALUES (:tenant_id,:contact_id,:score,:reason,NOW(),NOW(),NOW()) ON DUPLICATE KEY UPDATE score=VALUES(score), reason=VALUES(reason), last_calculated_at=NOW(), updated_at=NOW()', [
            'tenant_id' => $tenantId,
            'contact_id' => $contactId,
            'score' => $score,
            'reason' => $reason,
        ]);
    }
}
