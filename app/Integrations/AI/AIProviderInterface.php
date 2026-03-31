<?php
declare(strict_types=1);

namespace App\Integrations\AI;

interface AIProviderInterface
{
    public function chat(array $messages, array $options = []): array;
}
