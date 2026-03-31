<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\DashboardRepository;

final class AdminService
{
    public function __construct(private readonly DashboardRepository $repository)
    {
    }

    public function globalStats(): array
    {
        return $this->repository->globalStats();
    }
}
