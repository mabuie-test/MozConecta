<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

final class AdminService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function globalDashboard(): array
    {
        $q = fn(string $sql): int => (int)$this->pdo->query($sql)->fetchColumn();

        return [
            'clients' => $q('SELECT COUNT(*) FROM tenants WHERE deleted_at IS NULL'),
            'plans' => $q('SELECT COUNT(*) FROM plans WHERE deleted_at IS NULL'),
            'subscriptions_active' => $q('SELECT COUNT(*) FROM subscriptions s INNER JOIN subscription_statuses ss ON ss.id=s.status_id WHERE ss.code IN ("active","trial_active") AND s.deleted_at IS NULL'),
            'payments_today' => $q('SELECT COUNT(*) FROM payments WHERE DATE(created_at)=CURRENT_DATE()'),
            'ai_usage_month' => $q('SELECT COALESCE(SUM(units_used),0) FROM ai_usage_logs WHERE created_at >= DATE_FORMAT(NOW(), "%Y-%m-01 00:00:00")'),
            'instances' => $q('SELECT COUNT(*) FROM whatsapp_instances WHERE deleted_at IS NULL'),
            'integration_failures' => $q('SELECT COUNT(*) FROM payment_provider_logs WHERE success=0'),
            'webhooks_today' => $q('SELECT COUNT(*) FROM whatsapp_instance_events WHERE event_type LIKE "message_%" AND DATE(created_at)=CURRENT_DATE()'),
            'audit_events_today' => $q('SELECT COUNT(*) FROM audit_logs WHERE DATE(created_at)=CURRENT_DATE()'),
            'support_tickets_open' => $q('SELECT COUNT(*) FROM support_tickets WHERE status IN ("open","in_progress") AND deleted_at IS NULL'),
        ];
    }

    public function cmsSettings(): array
    {
        $stmt = $this->pdo->query('SELECT key_name, value_text FROM settings WHERE tenant_id IS NULL AND key_name LIKE "cms.%"');
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['key_name']] = $r['value_text'];
        }
        return $out;
    }

    public function saveCmsSetting(string $key, string $value): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO settings (tenant_id,key_name,value_type,value_text,is_public,created_at,updated_at) VALUES (NULL,:key_name,\'string\',:value_text,1,NOW(),NOW()) ON DUPLICATE KEY UPDATE value_text=VALUES(value_text), updated_at=NOW()');
        $stmt->execute(['key_name' => $key, 'value_text' => $value]);
    }

    public function recentAudit(int $limit = 20): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM audit_logs ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
