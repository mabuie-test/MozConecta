<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository;
use App\Repositories\ContactRepository;
use App\Repositories\ConversationMessageRepository;
use App\Repositories\FunnelRepository;
use App\Repositories\LeadScoreRepository;
use App\Repositories\TagRepository;

final class CRMService
{
    public function __construct(
        private readonly ContactRepository $contacts,
        private readonly TagRepository $tags,
        private readonly FunnelRepository $funnels,
        private readonly LeadScoreRepository $leadScores,
        private readonly ConversationMessageRepository $messages,
        private readonly AuditLogRepository $auditLogs,
    ) {
    }

    public function ensureDefaultPipeline(int $tenantId): array
    {
        $funnel = $this->funnels->findDefaultByTenant($tenantId);
        if (!$funnel) {
            $funnelId = $this->funnels->createDefault($tenantId);
            $stages = [
                ['novo lead', 'novo-lead'],
                ['contacto iniciado', 'contacto-iniciado'],
                ['interessado', 'interessado'],
                ['negociação', 'negociacao'],
                ['pagamento pendente', 'pagamento-pendente'],
                ['convertido', 'convertido'],
                ['pós-venda', 'pos-venda'],
            ];
            foreach ($stages as $index => [$name, $slug]) {
                $this->funnels->addStage($tenantId, $funnelId, $name, $slug, $index + 1, $slug === 'convertido');
            }
        }

        return $this->funnels->listStages($tenantId);
    }

    public function listContacts(int $tenantId, array $filters = []): array
    {
        return $this->contacts->list($tenantId, $filters);
    }

    public function createContact(int $tenantId, int $actorUserId, array $payload): int
    {
        $stages = $this->ensureDefaultPipeline($tenantId);
        $firstStageId = (int)($stages[0]['id'] ?? 0);

        $id = $this->contacts->create([
            'tenant_id' => $tenantId,
            'first_name' => $payload['first_name'] ?: null,
            'last_name' => $payload['last_name'] ?: null,
            'display_name' => trim((string)$payload['display_name']),
            'phone' => trim((string)$payload['phone']),
            'email' => $payload['email'] ?: null,
            'lead_origin' => $payload['lead_origin'] ?: null,
            'funnel_stage_id' => (int)($payload['funnel_stage_id'] ?: $firstStageId) ?: null,
            'assigned_user_id' => (int)($payload['assigned_user_id'] ?: 0) ?: null,
            'priority' => $payload['priority'] ?: 'medium',
            'potential_value' => $payload['potential_value'] !== '' ? (float)$payload['potential_value'] : null,
            'notes' => $payload['notes'] ?: null,
        ]);

        $this->syncTags($tenantId, $id, (string)($payload['tags'] ?? ''));
        $this->recalculateLeadScore($tenantId, $id);
        $this->auditLogs->add($tenantId, $actorUserId, 'contact_created', 'contact', $id, ['display_name' => $payload['display_name']]);

        return $id;
    }

    public function updateContact(int $tenantId, int $actorUserId, int $contactId, array $payload): void
    {
        $current = $this->contacts->findById($tenantId, $contactId);
        if (!$current) {
            return;
        }

        $this->contacts->update($tenantId, $contactId, [
            'display_name' => trim((string)$payload['display_name']),
            'phone' => trim((string)$payload['phone']),
            'email' => $payload['email'] ?: null,
            'lead_origin' => $payload['lead_origin'] ?: null,
            'funnel_stage_id' => (int)($payload['funnel_stage_id'] ?: 0) ?: null,
            'assigned_user_id' => (int)($payload['assigned_user_id'] ?: 0) ?: null,
            'priority' => $payload['priority'] ?: 'medium',
            'potential_value' => $payload['potential_value'] !== '' ? (float)$payload['potential_value'] : null,
            'notes' => $payload['notes'] ?: null,
        ]);

        $this->syncTags($tenantId, $contactId, (string)($payload['tags'] ?? ''));
        $this->recalculateLeadScore($tenantId, $contactId);
        $this->auditLogs->add($tenantId, $actorUserId, 'contact_updated', 'contact', $contactId, ['display_name' => $payload['display_name']]);
    }

    public function moveLeadStage(int $tenantId, int $actorUserId, int $contactId, int $stageId): void
    {
        $stage = $this->funnels->findStage($tenantId, $stageId);
        if (!$stage) {
            return;
        }
        $this->contacts->moveStage($tenantId, $contactId, $stageId);
        $this->recalculateLeadScore($tenantId, $contactId);
        $this->auditLogs->add($tenantId, $actorUserId, 'lead_stage_changed', 'contact', $contactId, [
            'new_stage' => $stage['slug'],
        ]);
    }

    public function pipelineBoard(int $tenantId): array
    {
        $stages = $this->ensureDefaultPipeline($tenantId);
        $contacts = $this->contacts->list($tenantId);
        $bucket = [];
        foreach ($stages as $stage) {
            $bucket[(int)$stage['id']] = [];
        }
        foreach ($contacts as $contact) {
            $bucket[(int)($contact['funnel_stage_id'] ?? 0)][] = $contact;
        }

        return ['stages' => $stages, 'contacts_by_stage' => $bucket];
    }

    public function tags(int $tenantId): array
    {
        return $this->tags->listByTenant($tenantId);
    }

    public function tagsForContact(int $contactId): array
    {
        return $this->tags->tagsForContact($contactId);
    }

    public function recalculateLeadScore(int $tenantId, int $contactId): int
    {
        $inbound = $this->messages->countInboundByContact($tenantId, $contactId);
        $outbound = $this->messages->countOutboundByContact($tenantId, $contactId);
        $contact = $this->contacts->findById($tenantId, $contactId);
        if (!$contact) {
            return 0;
        }

        $score = 0;
        $score += min(40, $inbound * 5);
        $score += min(25, $outbound * 3);
        $score += in_array(($contact['priority'] ?? 'medium'), ['high', 'urgent'], true) ? 15 : 5;
        $score += !empty($contact['potential_value']) && (float)$contact['potential_value'] >= 1000 ? 20 : 5;
        $score = max(0, min(100, $score));

        $this->leadScores->upsert($tenantId, $contactId, $score, 'engajamento+prioridade+valor potencial');
        return $score;
    }

    private function syncTags(int $tenantId, int $contactId, string $rawTags): void
    {
        $parts = array_filter(array_map(static fn (string $t): string => trim($t), explode(',', $rawTags)));
        if ($parts === []) {
            $this->tags->syncContactTags($contactId, []);
            return;
        }

        $ids = [];
        foreach ($parts as $name) {
            $ids[] = $this->tags->findOrCreate($tenantId, mb_strtolower($name));
        }
        $this->tags->syncContactTags($contactId, $ids);
    }
}
