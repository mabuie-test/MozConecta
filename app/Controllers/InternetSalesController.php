<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\InternetSalesBotService;
use App\Support\Request;

final class InternetSalesController extends BaseController
{
    public function __construct(private readonly InternetSalesBotService $internet)
    {
    }

    public function index(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->view('internet/index', [
            'title' => 'Bot de Venda de Internet',
            'packages' => $this->internet->listPackages($tenantId),
            'orders' => $this->internet->listOrders($tenantId),
        ]);
    }

    public function createPackage(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->internet->createPackage($tenantId, [
            'name' => (string)$request->input('name', ''),
            'description' => (string)$request->input('description', ''),
            'price' => (string)$request->input('price', '0'),
            'validity_days' => (string)$request->input('validity_days', '30'),
            'sales_message' => (string)$request->input('sales_message', ''),
            'is_active' => (bool)$request->input('is_active', true),
        ]);
        $this->redirect('/internet');
    }

    public function createOrder(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->internet->createOrder($tenantId, [
            'contact_id' => (string)$request->input('contact_id', ''),
            'package_id' => (string)$request->input('package_id', ''),
            'conversation_id' => (string)$request->input('conversation_id', ''),
            'customer_name' => (string)$request->input('customer_name', ''),
            'customer_phone' => (string)$request->input('customer_phone', ''),
            'installation_address' => (string)$request->input('installation_address', ''),
            'operator_notes' => (string)$request->input('operator_notes', ''),
        ]);
        $this->redirect('/internet');
    }

    public function updateOrderStatus(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->internet->updateOrderStatus($tenantId, (int)$request->input('id'), (string)$request->input('status', 'new'));
        $this->redirect('/internet');
    }
}
