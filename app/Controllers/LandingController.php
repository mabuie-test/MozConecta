<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Support\Request;

final class LandingController extends BaseController
{
    public function index(Request $request): void
    {
        $this->view('landing/index', ['title' => 'MozConecta SaaS']);
    }

    public function billingRequired(Request $request): void
    {
        $this->view('errors/billing-required', ['title' => 'Assinatura necessária']);
    }
}
