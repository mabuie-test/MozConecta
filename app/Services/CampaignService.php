<?php
declare(strict_types=1);

namespace App\Services;

final class CampaignService
{
    public function schedule(array $campaign): array
    {
        return ['status' => 'scheduled'] + $campaign;
    }
}
