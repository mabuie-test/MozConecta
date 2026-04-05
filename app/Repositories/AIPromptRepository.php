<?php
declare(strict_types=1);

namespace App\Repositories;

final class AIPromptRepository extends BaseRepository
{
    public function log(array $data): int
    {
        $this->execute('INSERT INTO ai_prompts (tenant_id,conversation_id,contact_id,provider_name,model_name,prompt_text,response_text,status,created_at) VALUES (:tenant_id,:conversation_id,:contact_id,:provider_name,:model_name,:prompt_text,:response_text,:status,NOW())', [
            'tenant_id' => $data['tenant_id'],
            'conversation_id' => $data['conversation_id'] ?? null,
            'contact_id' => $data['contact_id'] ?? null,
            'provider_name' => $data['provider_name'],
            'model_name' => $data['model_name'] ?? null,
            'prompt_text' => $data['prompt_text'],
            'response_text' => $data['response_text'] ?? null,
            'status' => $data['status'] ?? 'success',
        ]);

        return (int)$this->pdo->lastInsertId();
    }
}
