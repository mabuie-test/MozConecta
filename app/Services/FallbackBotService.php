<?php
declare(strict_types=1);

namespace App\Services;

final class FallbackBotService
{
    public function safeResponse(string $input, array $intent): array
    {
        if (!empty($intent['needs_human'])) {
            return [
                'type' => 'handoff_human',
                'text' => 'Vou encaminhar para um atendente humano para te ajudar com prioridade.',
            ];
        }

        if (!empty($intent['is_greeting'])) {
            return [
                'type' => 'fallback_message',
                'text' => 'Olá! Posso ajudar com planos, preços, suporte e estado do seu pedido.',
            ];
        }

        return [
            'type' => 'fallback_message',
            'text' => 'No momento não consegui processar com IA. Posso encaminhar para um atendente humano.',
        ];
    }
}
