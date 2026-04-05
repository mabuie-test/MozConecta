<?php
declare(strict_types=1);

namespace App\Services;

use App\Integrations\Payments\PaymentProviderInterface;

final class PaymentService
{
    public function __construct(private readonly PaymentProviderInterface $provider)
    {
    }

    public function checkout(array $payload): array
    {
        return $this->provider->createCharge($payload);
    }
}
