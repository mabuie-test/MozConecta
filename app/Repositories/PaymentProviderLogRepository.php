<?php
declare(strict_types=1);

namespace App\Repositories;

final class PaymentProviderLogRepository extends BaseRepository
{
    public function log(array $data): void
    {
        $this->execute('INSERT INTO payment_provider_logs (tenant_id,payment_id,provider_name,endpoint,http_method,request_headers,request_payload,response_status_code,response_payload,success,error_message,latency_ms,created_at) VALUES (:tenant_id,:payment_id,:provider_name,:endpoint,:http_method,:request_headers,:request_payload,:response_status_code,:response_payload,:success,:error_message,:latency_ms,NOW())', [
            'tenant_id' => $data['tenant_id'] ?? null,
            'payment_id' => $data['payment_id'] ?? null,
            'provider_name' => $data['provider_name'],
            'endpoint' => $data['endpoint'],
            'http_method' => $data['http_method'],
            'request_headers' => json_encode($data['request_headers'] ?? [], JSON_UNESCAPED_UNICODE),
            'request_payload' => json_encode($data['request_payload'] ?? [], JSON_UNESCAPED_UNICODE),
            'response_status_code' => $data['response_status_code'] ?? null,
            'response_payload' => json_encode($data['response_payload'] ?? [], JSON_UNESCAPED_UNICODE),
            'success' => !empty($data['success']) ? 1 : 0,
            'error_message' => $data['error_message'] ?? null,
            'latency_ms' => $data['latency_ms'] ?? null,
        ]);
    }
}
