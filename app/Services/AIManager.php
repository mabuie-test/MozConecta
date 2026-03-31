<?php
declare(strict_types=1);

namespace App\Services;

use App\Integrations\AI\AIProviderInterface;

final class AIManager
{
    public function __construct(private readonly AIProviderInterface $provider)
    {
    }

    public function respond(array $messages, array $context = []): array
    {
        return $this->provider->chat($messages, $context);
    }
}
