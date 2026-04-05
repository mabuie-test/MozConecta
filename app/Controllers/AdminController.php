<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\AdminService;
use App\Support\Request;

final class AdminController extends BaseController
{
    public function __construct(private readonly AdminService $admin)
    {
    }

    public function index(Request $request): void
    {
        $this->view('admin/index', [
            'title' => 'Admin Global',
            'stats' => $this->admin->globalDashboard(),
            'cms' => $this->admin->cmsSettings(),
            'audit' => $this->admin->recentAudit(20),
        ]);
    }

    public function saveCms(Request $request): void
    {
        $this->admin->saveCmsSetting('cms.landing.headline', (string)$request->input('cms_headline', ''));
        $this->admin->saveCmsSetting('cms.landing.subheadline', (string)$request->input('cms_subheadline', ''));
        $this->redirect('/admin');
    }
}
