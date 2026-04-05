<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Integrations\WhatsApp\WebhookInboundService;
use App\Support\Request;

final class WhatsAppWebhookController extends BaseController
{
    public function __construct(private readonly WebhookInboundService $service)
    {
    }

    public function inbound(Request $request): void
    {
        $secret = (string)$request->input('secret', $request->server('HTTP_X_WEBHOOK_SECRET', ''));
        $this->service->handle($request->all(), $secret);
        $this->json(['ok' => true]);
    }
}
