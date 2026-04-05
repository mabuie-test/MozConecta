<?php
declare(strict_types=1);

namespace App\Repositories;

final class PaymentTransactionRepository extends BaseRepository
{
    public function create(array $data): void
    {
        $this->execute('INSERT INTO payment_transactions (payment_id,provider_name,provider_reference,debito_reference,external_transaction_id,provider_txn_id,idempotency_key,event_type,payload_json,request_payload,response_payload,payment_status,status_checked_at,failure_reason,created_at,updated_at) VALUES (:payment_id,:provider_name,:provider_reference,:debito_reference,:external_transaction_id,:provider_txn_id,:idempotency_key,:event_type,:payload_json,:request_payload,:response_payload,:payment_status,NOW(),:failure_reason,NOW(),NOW())', [
            'payment_id' => $data['payment_id'],
            'provider_name' => $data['provider_name'] ?? null,
            'provider_reference' => $data['provider_reference'] ?? null,
            'debito_reference' => $data['debito_reference'] ?? null,
            'external_transaction_id' => $data['external_transaction_id'] ?? null,
            'provider_txn_id' => $data['provider_txn_id'] ?? ($data['debito_reference'] ?? uniqid('txn_', true)),
            'idempotency_key' => $data['idempotency_key'] ?? bin2hex(random_bytes(16)),
            'event_type' => $data['event_type'] ?? 'checkout',
            'payload_json' => json_encode($data['payload_json'] ?? []),
            'request_payload' => json_encode($data['request_payload'] ?? []),
            'response_payload' => json_encode($data['response_payload'] ?? []),
            'payment_status' => $data['payment_status'] ?? 'pending',
            'failure_reason' => $data['failure_reason'] ?? null,
        ]);
    }
}
