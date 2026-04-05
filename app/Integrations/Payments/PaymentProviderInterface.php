<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

interface PaymentProviderInterface
{
    public function providerName(): string;
    public function createCharge(array $payload): array;
    public function checkStatus(string $debitoReference): array;
}
