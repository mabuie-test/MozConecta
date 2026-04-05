<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Integrations\WhatsApp\PairingService;
use App\Integrations\WhatsApp\SessionSyncService;
use App\Integrations\WhatsApp\WhatsAppInstanceService;
use App\Repositories\WhatsAppInstanceEventRepository;
use App\Repositories\WhatsAppPairingSessionRepository;
use App\Support\Request;

final class WhatsAppInstanceController extends BaseController
{
    public function __construct(
        private readonly WhatsAppInstanceService $instances,
        private readonly PairingService $pairing,
        private readonly SessionSyncService $sync,
        private readonly WhatsAppInstanceEventRepository $events,
        private readonly WhatsAppPairingSessionRepository $pairings,
    ) {
    }

    public function index(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->view('whatsapp/index', [
            'title' => 'Instâncias WhatsApp',
            'instances' => $this->instances->listByTenant($tenantId),
        ]);
    }

    public function create(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $secret = bin2hex(random_bytes(16));
        $this->instances->create($tenantId, [
            'name' => (string)$request->input('name'),
            'phone_number' => (string)$request->input('phone_number'),
            'provider_name' => (string)$request->input('provider_name', env('WHATSAPP_PROVIDER_DEFAULT', 'generic_api')),
            'pairing_mode' => (string)$request->input('pairing_mode', 'qr'),
            'webhook_secret' => $secret,
        ]);

        $this->redirect('/whatsapp/instances');
    }

    public function edit(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $id = (int)$request->input('id');
        $this->instances->update($tenantId, $id, [
            'name' => (string)$request->input('name'),
            'phone_number' => (string)$request->input('phone_number'),
            'pairing_mode' => (string)$request->input('pairing_mode', 'qr'),
        ]);

        $this->redirect('/whatsapp/instances');
    }

    public function startPairing(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $instanceId = (int)$request->input('id');
        $this->pairing->start($tenantId, $instanceId);
        $this->redirect('/whatsapp/instances/show?id=' . $instanceId);
    }

    public function show(Request $request): void
    {
        $id = (int)$request->input('id');
        $instance = $this->instances->find($id);
        $this->view('whatsapp/show', [
            'title' => 'Detalhes da instância',
            'instance' => $instance,
            'events' => $this->events->listByInstance($id),
            'pairings' => $this->pairings->listByInstance($id),
        ]);
    }

    public function reconnect(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->instances->reconnect($tenantId, (int)$request->input('id'));
        $this->redirect('/whatsapp/instances');
    }

    public function disconnect(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->instances->disconnect($tenantId, (int)$request->input('id'));
        $this->redirect('/whatsapp/instances');
    }

    public function delete(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->instances->delete($tenantId, (int)$request->input('id'));
        $this->redirect('/whatsapp/instances');
    }

    public function sync(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $this->sync->syncTenant($tenantId);
        $this->redirect('/whatsapp/instances');
    }
}
