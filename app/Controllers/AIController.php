<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Repositories\AssistantProfileRepository;
use App\Services\HybridDecisionService;
use App\Support\Request;

final class AIController extends BaseController
{
    public function __construct(
        private readonly AssistantProfileRepository $profiles,
        private readonly HybridDecisionService $hybrid,
    ) {
    }

    public function settings(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $profile = $this->profiles->findByTenant($tenantId);

        $this->view('ai/settings', [
            'title' => 'Configuração do Assistente IA',
            'profile' => $profile,
        ]);
    }

    public function saveSettings(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];

        $this->profiles->upsert($tenantId, [
            'assistant_name' => (string)$request->input('assistant_name', 'Assistente MozConecta'),
            'persona' => (string)$request->input('persona', ''),
            'language_code' => (string)$request->input('language_code', 'pt-PT'),
            'tone' => (string)$request->input('tone', 'profissional'),
            'business_rules' => (string)$request->input('business_rules', ''),
            'faq_json' => $this->parseLines((string)$request->input('faq', '')),
            'products_services_json' => $this->parseLines((string)$request->input('products_services', '')),
            'policies_json' => $this->parseLines((string)$request->input('policies', '')),
            'business_goals_json' => $this->parseLines((string)$request->input('business_goals', '')),
            'primary_provider' => (string)$request->input('primary_provider', 'openrouter'),
            'fallback_provider' => (string)$request->input('fallback_provider', 'gemini'),
        ]);

        $this->redirect('/ai/settings');
    }

    public function testHybrid(Request $request): void
    {
        $tenantId = (int)$_SESSION['tenant_id'];
        $conversationId = (int)$request->input('conversation_id');
        $contactId = (int)$request->input('contact_id');
        $input = (string)$request->input('input', '');

        $result = $this->hybrid->handleInbound($tenantId, $conversationId, $contactId, $input, []);
        $this->json(['ok' => true, 'result' => $result]);
    }

    private function parseLines(string $raw): array
    {
        $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw) ?: []));
        $out = [];
        foreach ($lines as $i => $line) {
            $out['item_' . ($i + 1)] = $line;
        }
        return $out;
    }
}
