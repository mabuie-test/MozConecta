<?php
declare(strict_types=1);

namespace App\Services;

use App\Integrations\WhatsApp\MessageOutboundDispatcher;
use App\Repositories\CampaignContactRepository;
use App\Repositories\CampaignRepository;
use App\Repositories\ContactRepository;
use App\Repositories\TagRepository;
use App\Repositories\WhatsAppInstanceRepository;

final class CampaignService
{
    public function __construct(
        private readonly CampaignRepository $campaigns,
        private readonly CampaignContactRepository $campaignContacts,
        private readonly ContactRepository $contacts,
        private readonly TagRepository $tags,
        private readonly MessageOutboundDispatcher $dispatcher,
        private readonly WhatsAppInstanceRepository $instances,
        private readonly NotificationService $notifications,
    ) {
    }

    public function createCampaign(int $tenantId, array $payload): int
    {
        $id = $this->campaigns->create([
            'tenant_id' => $tenantId,
            'name' => $payload['name'],
            'type' => $payload['type'] ?? 'broadcast',
            'message_template' => $payload['message_template'],
            'segment_type' => $payload['segment_type'] ?? 'all',
            'segment_value' => $payload['segment_value'] ?? null,
            'status' => $payload['scheduled_at'] ? 'scheduled' : 'draft',
            'batch_size' => (int)($payload['batch_size'] ?? 50),
            'scheduled_at' => $payload['scheduled_at'] ?: null,
        ]);

        $contactIds = $this->segmentContacts($tenantId, (string)$payload['segment_type'], (string)($payload['segment_value'] ?? ''));
        $this->campaignContacts->bulkCreate($tenantId, $id, $contactIds);
        $this->campaigns->setTotalRecipients($tenantId, $id, count($contactIds));

        return $id;
    }

    public function listCampaigns(int $tenantId): array
    {
        return $this->campaigns->listByTenant($tenantId);
    }

    public function pause(int $tenantId, int $campaignId): void
    {
        $this->campaigns->updateStatus($tenantId, $campaignId, 'paused');
    }

    public function resume(int $tenantId, int $campaignId): void
    {
        $this->campaigns->updateStatus($tenantId, $campaignId, 'running');
    }

    public function cancel(int $tenantId, int $campaignId): void
    {
        $this->campaigns->updateStatus($tenantId, $campaignId, 'cancelled');
    }

    public function runBatch(int $tenantId, int $campaignId): array
    {
        $campaign = $this->campaigns->findById($tenantId, $campaignId);
        if (!$campaign || in_array($campaign['status'], ['cancelled', 'completed'], true)) {
            return ['sent' => 0, 'failed' => 0];
        }

        $this->campaigns->updateStatus($tenantId, $campaignId, 'running');
        $batch = $this->campaignContacts->pendingBatch($tenantId, $campaignId, (int)$campaign['batch_size']);
        $instance = $this->instances->listByTenant($tenantId)[0] ?? null;

        $sent = 0;
        $failed = 0;
        foreach ($batch as $row) {
            if (!$instance) {
                $this->campaignContacts->markFailed((int)$row['id'], 'Nenhuma instância WhatsApp conectada');
                $failed++;
                continue;
            }
            try {
                $message = str_replace(['{{nome}}', '{{telefone}}'], [(string)$row['display_name'], (string)$row['phone']], (string)$campaign['message_template']);
                $this->dispatcher->dispatch($tenantId, (int)$instance['id'], ['to' => $row['phone'], 'body' => $message]);
                $this->campaignContacts->markSent((int)$row['id']);
                $this->campaigns->incrementStats($tenantId, $campaignId, 'sent_count');
                $sent++;
            } catch (\Throwable $e) {
                $this->campaignContacts->markFailed((int)$row['id'], $e->getMessage());
                $this->campaigns->incrementStats($tenantId, $campaignId, 'failed_count');
                $failed++;
            }
        }

        $remaining = $this->campaignContacts->pendingBatch($tenantId, $campaignId, 1);
        if (count($remaining) === 0) {
            $this->campaigns->updateStatus($tenantId, $campaignId, 'completed');
            $this->notifications->push($tenantId, 'campaign_completed', 'Campanha concluída', 'A campanha #' . $campaignId . ' terminou.');
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    public function report(int $tenantId, int $campaignId): array
    {
        return $this->campaignContacts->report($tenantId, $campaignId);
    }

    private function segmentContacts(int $tenantId, string $segmentType, string $segmentValue): array
    {
        $contacts = $this->contacts->list($tenantId);

        $filtered = array_filter($contacts, function (array $c) use ($segmentType, $segmentValue): bool {
            return match ($segmentType) {
                'stage' => (string)($c['funnel_stage_id'] ?? '') === $segmentValue,
                'cold' => empty($c['last_interaction_at']) || strtotime((string)$c['last_interaction_at']) < strtotime('-30 days'),
                'lost' => in_array(mb_strtolower((string)($c['stage_name'] ?? '')), ['perdido', 'lost'], true),
                'post_sale' => in_array(mb_strtolower((string)($c['stage_name'] ?? '')), ['pós-venda', 'pos-venda'], true),
                'tags' => $this->hasTag((int)$c['id'], $segmentValue),
                default => true,
            };
        });

        return array_map(static fn (array $c): int => (int)$c['id'], $filtered);
    }

    private function hasTag(int $contactId, string $tagName): bool
    {
        if ($tagName === '') {
            return false;
        }
        $tags = $this->tags->tagsForContact($contactId);
        foreach ($tags as $tag) {
            if (mb_strtolower((string)$tag['name']) === mb_strtolower($tagName)) {
                return true;
            }
        }
        return false;
    }
}
