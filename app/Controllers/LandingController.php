<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;

final class LandingController
{
    public function index(Request $request): void
    {
        Response::view('landing/index', ['title' => 'MozConecta SaaS']);
    }
}
