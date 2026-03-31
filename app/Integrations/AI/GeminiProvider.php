<?php
declare(strict_types=1);

namespace App\Integrations\AI;

final class GeminiProvider implements AIProviderInterface
{
    public function chat(array $messages, array $options = []): array
    {
        return ['provider' => 'gemini', 'text' => 'Resposta IA indisponível no modo stub.'];
    }
}
