<?php
declare(strict_types=1);

namespace App\Integrations\AI;

interface AIProviderInterface
{
    public function providerName(): string;

    /**
     * @param array<int,array{role:string,content:string}> $messages
     */
    public function chat(array $messages, array $options = []): array;
}
