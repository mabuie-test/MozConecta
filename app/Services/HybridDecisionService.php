<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AIPromptRepository;
use App\Repositories\AssistantProfileRepository;
use App\Repositories\ConversationMessageRepository;
use App\Repositories\ConversationRepository;
use App\Repositories\TagRepository;

final class HybridDecisionService
{
    public function __construct(
        private readonly AIUsageService $usage,
        private readonly PromptBuilderService $promptBuilder,
        private readonly IntentClassifierService $classifier,
        private readonly ConversationMemoryService $memory,
        private readonly AIManager $aiManager,
        private readonly FallbackBotService $fallbackBot,
        private readonly AssistantProfileRepository $profiles,
        private readonly AIPromptRepository $prompts,
        private readonly ConversationMessageRepository $messages,
        private readonly ConversationRepository $conversations,
        private readonly TagRepository $tags,
    ) {
    }

    public function handleInbound(int $tenantId, int $conversationId, int $contactId, string $input, array $flowResult = []): array
    {
        // 1) limites
        $canUseAI = $this->usage->canUseAI($tenantId);

        // 2) fluxo
        if (($flowResult['status'] ?? null) === 'processed') {
            return ['decision' => 'flow'];
        }

        // 3) palavra-chave
        $keywordReply = $this->keywordRule($input);
        if ($keywordReply !== null) {
            $this->sendAndStore($tenantId, $conversationId, $contactId, $keywordReply, 'keyword_rule');
            return ['decision' => 'keyword_rule', 'text' => $keywordReply];
        }

        // 4) regra
        $ruleReply = $this->businessRule($input);
        if ($ruleReply !== null) {
            $this->sendAndStore($tenantId, $conversationId, $contactId, $ruleReply, 'business_rule');
            return ['decision' => 'business_rule', 'text' => $ruleReply];
        }

        $intent = $this->classifier->classify($input);

        // 8) encaminhar humano antecipado
        if (!empty($intent['needs_human'])) {
            $fallback = $this->fallbackBot->safeResponse($input, $intent);
            $this->sendAndStore($tenantId, $conversationId, $contactId, $fallback['text'], $fallback['type']);
            return ['decision' => $fallback['type'], 'text' => $fallback['text']];
        }

        // 5) IA necessária
        if (!$canUseAI || empty($intent['needs_ai'])) {
            $fallback = $this->fallbackBot->safeResponse($input, $intent);
            $this->sendAndStore($tenantId, $conversationId, $contactId, $fallback['text'], $fallback['type']);
            return ['decision' => 'fallback', 'text' => $fallback['text']];
        }

        $profile = $this->profiles->findByTenant($tenantId) ?? [
            'assistant_name' => 'Assistente MozConecta',
            'persona' => 'Assistente comercial para WhatsApp',
            'language_code' => 'pt-PT',
            'tone' => 'profissional',
            'business_rules' => null,
            'faq_json' => '{}',
            'products_services_json' => '{}',
            'policies_json' => '{}',
            'business_goals_json' => '{}',
            'primary_provider' => (string)env('AI_DEFAULT_PROVIDER', 'openrouter'),
            'fallback_provider' => 'gemini',
        ];

        $memory = $this->memory->recentMessages($tenantId, $conversationId);
        $messages = $this->promptBuilder->buildMessages($profile, $memory, $input);

        try {
            // 6/7 provider principal + fallback
            $response = $this->aiManager->respondWithFallback(
                $messages,
                (string)($profile['primary_provider'] ?? 'openrouter'),
                (string)($profile['fallback_provider'] ?? 'gemini')
            );

            $text = trim((string)($response['text'] ?? ''));
            if ($text === '') {
                $fallback = $this->fallbackBot->safeResponse($input, $intent);
                $text = $fallback['text'];
                $status = 'fallback';
            } else {
                $status = (string)($response['status'] ?? 'success');
            }

            $this->prompts->log([
                'tenant_id' => $tenantId,
                'conversation_id' => $conversationId,
                'contact_id' => $contactId,
                'provider_name' => (string)($response['provider'] ?? 'unknown'),
                'model_name' => (string)($response['model'] ?? ''),
                'prompt_text' => json_encode($messages, JSON_UNESCAPED_UNICODE),
                'response_text' => $text,
                'status' => in_array($status, ['success', 'fallback'], true) ? $status : 'failed',
            ]);

            $this->usage->record($tenantId, $conversationId, $contactId, (string)($response['provider'] ?? 'unknown'), 'message', 1, ['status' => $status]);
            $this->sendAndStore($tenantId, $conversationId, $contactId, $text, 'ai_response');

            return ['decision' => 'ai', 'status' => $status, 'text' => $text];
        } catch (\Throwable $exception) {
            $fallback = $this->fallbackBot->safeResponse($input, $intent);
            $this->sendAndStore($tenantId, $conversationId, $contactId, $fallback['text'], 'fallback_error');
            $this->prompts->log([
                'tenant_id' => $tenantId,
                'conversation_id' => $conversationId,
                'contact_id' => $contactId,
                'provider_name' => 'none',
                'model_name' => null,
                'prompt_text' => $input,
                'response_text' => $fallback['text'],
                'status' => 'failed',
            ]);

            return ['decision' => 'fallback_error', 'text' => $fallback['text'], 'error' => $exception->getMessage()];
        }
    }

    private function keywordRule(string $input): ?string
    {
        $text = mb_strtolower($input);
        if (str_contains($text, 'horário') || str_contains($text, 'horario')) {
            return 'Nosso horário de atendimento é das 08:00 às 18:00.';
        }
        if (str_contains($text, 'planos') || str_contains($text, 'preço') || str_contains($text, 'preco')) {
            return 'Temos planos Inicial, Essencial, Crescimento, Profissional e Enterprise. Posso enviar detalhes?';
        }
        return null;
    }

    private function businessRule(string $input): ?string
    {
        $text = mb_strtolower($input);
        if (str_contains($text, 'cancelar')) {
            return 'Para cancelamento, confirme o NUIT/telefone da conta para validarmos com segurança.';
        }
        return null;
    }

    private function sendAndStore(int $tenantId, int $conversationId, int $contactId, string $text, string $source): void
    {
        $this->messages->add([
            'tenant_id' => $tenantId,
            'conversation_id' => $conversationId,
            'contact_id' => $contactId,
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => $text,
            'payload_json' => ['source' => $source],
            'sent_by_user_id' => null,
        ]);
        $this->conversations->touchLastMessage($tenantId, $conversationId);
    }
}
