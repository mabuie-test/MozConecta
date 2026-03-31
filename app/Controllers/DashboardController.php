<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Support\Request;

final class DashboardController extends BaseController
{
    public function index(Request $request): void
    {
        $this->view('dashboard/index', ['title' => 'Painel']);
    }
}
