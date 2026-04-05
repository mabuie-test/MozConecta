<?php
declare(strict_types=1);

namespace App\Repositories;

final class PaymentProviderLogRepository extends BaseRepository
{
    public function log(array $data): void
    {
        $this->execute('INSERT INTO payment_provider_logs (tenant_id,payment_id,provider_name,endpoint,method,request_payload,response_payload,response_status,success,error_message,created_at) VALUES (:tenant_id,:payment_id,:provider_name,:endpoint,:method,:request_payload,:response_payload,:response_status,:success,:error_message,NOW())', [
            'tenant_id' => $data['tenant_id'] ?? null,
            'payment_id' => $data['payment_id'] ?? null,
            'provider_name' => $data['provider_name'],
            'endpoint' => $data['endpoint'],
            'method' => $data['method'],
            'request_payload' => json_encode($data['request_payload'] ?? []),
            'response_payload' => json_encode($data['response_payload'] ?? []),
            'response_status' => $data['response_status'] ?? null,
            'success' => !empty($data['success']) ? 1 : 0,
            'error_message' => $data['error_message'] ?? null,
        ]);
    }
}
