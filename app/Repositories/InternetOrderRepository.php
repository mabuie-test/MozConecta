<?php
declare(strict_types=1);

namespace App\Repositories;

final class InternetOrderRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $this->execute('INSERT INTO internet_orders (tenant_id,contact_id,package_id,conversation_id,customer_name,customer_phone,installation_address,operator_notes,status,created_at,updated_at) VALUES (:tenant_id,:contact_id,:package_id,:conversation_id,:customer_name,:customer_phone,:installation_address,:operator_notes,:status,NOW(),NOW())', [
            'tenant_id' => $data['tenant_id'],
            'contact_id' => $data['contact_id'],
            'package_id' => $data['package_id'],
            'conversation_id' => $data['conversation_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_phone' => $data['customer_phone'],
            'installation_address' => $data['installation_address'] ?? null,
            'operator_notes' => $data['operator_notes'] ?? null,
            'status' => $data['status'] ?? 'new',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function listByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT io.*, c.display_name AS contact_name, ip.name AS package_name FROM internet_orders io INNER JOIN contacts c ON c.id=io.contact_id INNER JOIN internet_packages ip ON ip.id=io.package_id WHERE io.tenant_id=:tenant_id ORDER BY io.id DESC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $tenantId, int $orderId, string $status): void
    {
        $this->execute('UPDATE internet_orders SET status=:status, updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:id', [
            'status' => $status,
            'tenant_id' => $tenantId,
            'id' => $orderId,
        ]);
    }
}
