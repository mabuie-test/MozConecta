<?php
declare(strict_types=1);

namespace App\Jobs;

final class ExpireTrialsJob
{
    public function handle(): void
    {
        // execute UPDATE subscriptions SET status='trial_expired' ...
    }
}
