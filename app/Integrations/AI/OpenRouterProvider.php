<?php
declare(strict_types=1);

namespace App\Integrations\AI;

final class OpenRouterProvider implements AIProviderInterface
{
    public function chat(array $messages, array $options = []): array
    {
        return ['provider' => 'openrouter', 'text' => 'Resposta IA indisponível no modo stub.'];
    }
}
