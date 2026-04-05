<?php
declare(strict_types=1);

namespace App\Services;

use App\Integrations\AI\GeminiProvider;
use App\Integrations\AI\OpenRouterProvider;
use RuntimeException;

final class AIManager
{
    public function __construct(
        private readonly OpenRouterProvider $openRouter,
        private readonly GeminiProvider $gemini,
    ) {
    }

    public function respondWithFallback(array $messages, string $primaryProvider, string $fallbackProvider, array $options = []): array
    {
        $first = $this->byName($primaryProvider);
        $second = $this->byName($fallbackProvider);

        try {
            $result = $first->chat($messages, $options);
            $result['status'] = 'success';
            return $result;
        } catch (\Throwable $primaryException) {
            try {
                $result = $second->chat($messages, $options);
                $result['status'] = 'fallback';
                $result['fallback_reason'] = $primaryException->getMessage();
                return $result;
            } catch (\Throwable $fallbackException) {
                throw new RuntimeException('Falha nos providers IA: ' . $fallbackException->getMessage());
            }
        }
    }

    private function byName(string $name): object
    {
        return match (mb_strtolower($name)) {
            'gemini' => $this->gemini,
            'openrouter' => $this->openRouter,
            default => $this->openRouter,
        };
    }
}
