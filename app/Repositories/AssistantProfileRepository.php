<?php
declare(strict_types=1);

namespace App\Repositories;

final class AssistantProfileRepository extends BaseRepository
{
    public function findByTenant(int $tenantId): ?array
    {
        return $this->fetchOne('SELECT * FROM assistant_profiles WHERE tenant_id=:tenant_id LIMIT 1', ['tenant_id' => $tenantId]);
    }

    public function upsert(int $tenantId, array $data): void
    {
        $this->execute('INSERT INTO assistant_profiles (tenant_id,assistant_name,persona,language_code,tone,business_rules,faq_json,products_services_json,policies_json,business_goals_json,primary_provider,fallback_provider,created_at,updated_at)
             VALUES (:tenant_id,:assistant_name,:persona,:language_code,:tone,:business_rules,:faq_json,:products_services_json,:policies_json,:business_goals_json,:primary_provider,:fallback_provider,NOW(),NOW())
             ON DUPLICATE KEY UPDATE assistant_name=VALUES(assistant_name), persona=VALUES(persona), language_code=VALUES(language_code), tone=VALUES(tone), business_rules=VALUES(business_rules), faq_json=VALUES(faq_json), products_services_json=VALUES(products_services_json), policies_json=VALUES(policies_json), business_goals_json=VALUES(business_goals_json), primary_provider=VALUES(primary_provider), fallback_provider=VALUES(fallback_provider), updated_at=NOW()', [
            'tenant_id' => $tenantId,
            'assistant_name' => $data['assistant_name'],
            'persona' => $data['persona'] ?? null,
            'language_code' => $data['language_code'] ?? 'pt-PT',
            'tone' => $data['tone'] ?? 'profissional',
            'business_rules' => $data['business_rules'] ?? null,
            'faq_json' => json_encode($data['faq_json'] ?? []),
            'products_services_json' => json_encode($data['products_services_json'] ?? []),
            'policies_json' => json_encode($data['policies_json'] ?? []),
            'business_goals_json' => json_encode($data['business_goals_json'] ?? []),
            'primary_provider' => $data['primary_provider'] ?? 'openrouter',
            'fallback_provider' => $data['fallback_provider'] ?? 'gemini',
        ]);
    }
}
