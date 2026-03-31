<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AdminService;
use App\Support\Request;
use App\Support\Response;

final class AdminController
{
    public function __construct(private readonly AdminService $adminService)
    {
    }

    public function index(Request $request): void
    {
        if (($_SESSION['role'] ?? '') !== 'owner') {
            Response::json(['error' => 'forbidden'], 403);
            return;
        }
        Response::view('admin/index', ['stats' => $this->adminService->globalStats()]);
    }
}
