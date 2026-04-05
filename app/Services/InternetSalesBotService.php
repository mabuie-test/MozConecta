<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ContactRepository;
use App\Repositories\InternetOrderRepository;
use App\Repositories\InternetPackageRepository;
use App\Repositories\TagRepository;

final class InternetSalesBotService
{
    public function __construct(
        private readonly InternetPackageRepository $packages,
        private readonly InternetOrderRepository $orders,
        private readonly ContactRepository $contacts,
        private readonly TagRepository $tags,
        private readonly NotificationService $notifications,
    ) {
    }

    public function listPackages(int $tenantId): array
    {
        return $this->packages->listByTenant($tenantId);
    }

    public function createPackage(int $tenantId, array $payload): int
    {
        return $this->packages->create([
            'tenant_id' => $tenantId,
            'name' => $payload['name'],
            'description' => $payload['description'] ?? null,
            'price' => (float)$payload['price'],
            'validity_days' => (int)($payload['validity_days'] ?? 30),
            'sales_message' => $payload['sales_message'] ?? null,
            'is_active' => !empty($payload['is_active']),
        ]);
    }

    public function createOrder(int $tenantId, array $payload): int
    {
        $orderId = $this->orders->create([
            'tenant_id' => $tenantId,
            'contact_id' => (int)$payload['contact_id'],
            'package_id' => (int)$payload['package_id'],
            'conversation_id' => (int)($payload['conversation_id'] ?? 0) ?: null,
            'customer_name' => $payload['customer_name'] ?? null,
            'customer_phone' => $payload['customer_phone'],
            'installation_address' => $payload['installation_address'] ?? null,
            'operator_notes' => $payload['operator_notes'] ?? null,
            'status' => 'new',
        ]);

        $tagId = $this->tags->findOrCreate($tenantId, 'internet_order');
        $current = $this->tags->tagsForContact((int)$payload['contact_id']);
        $ids = array_map(static fn (array $row): int => (int)$row['id'], $current);
        if (!in_array($tagId, $ids, true)) {
            $ids[] = $tagId;
        }
        $this->tags->syncContactTags((int)$payload['contact_id'], $ids);

        $this->notifications->push($tenantId, 'internet_order_new', 'Novo pedido de internet', 'Pedido #' . $orderId . ' aguardando operador.');
        return $orderId;
    }

    public function listOrders(int $tenantId): array
    {
        return $this->orders->listByTenant($tenantId);
    }

    public function updateOrderStatus(int $tenantId, int $orderId, string $status): void
    {
        $this->orders->updateStatus($tenantId, $orderId, $status);
    }
}
