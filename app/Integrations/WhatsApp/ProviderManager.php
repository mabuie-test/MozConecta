<?php
declare(strict_types=1);

namespace App\Integrations\WhatsApp;

final class ProviderManager
{
    public function __construct(private readonly GenericApiProvider $genericProvider)
    {
    }

    public function for(?string $providerName = null): WhatsAppProviderInterface
    {
        // expansão futura para múltiplos adapters (e.g. Evolution, Gupshup, etc.)
        return $this->genericProvider;
    }
}
