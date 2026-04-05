<?php
declare(strict_types=1);

namespace App\Services;

final class PromptBuilderService
{
    public function buildSystemPrompt(array $profile): string
    {
        $faq = $this->stringifyJsonField($profile['faq_json'] ?? null);
        $products = $this->stringifyJsonField($profile['products_services_json'] ?? null);
        $policies = $this->stringifyJsonField($profile['policies_json'] ?? null);
        $goals = $this->stringifyJsonField($profile['business_goals_json'] ?? null);

        return trim(implode("\n", [
            'Nome do assistente: ' . ($profile['assistant_name'] ?? 'Assistente MozConecta'),
            'Persona: ' . ($profile['persona'] ?? 'Assistente comercial para WhatsApp.'),
            'Idioma: ' . ($profile['language_code'] ?? 'pt-PT'),
            'Tom: ' . ($profile['tone'] ?? 'profissional'),
            'Regras de negócio: ' . ($profile['business_rules'] ?? 'Responda com objetividade e foco em conversão.'),
            'FAQ: ' . $faq,
            'Produtos e serviços: ' . $products,
            'Políticas: ' . $policies,
            'Objetivos comerciais: ' . $goals,
            'Se não souber, não invente; ofereça encaminhamento para humano.',
        ]));
    }

    public function buildMessages(array $profile, array $memory, string $userInput): array
    {
        $messages = [[
            'role' => 'system',
            'content' => $this->buildSystemPrompt($profile),
        ]];

        foreach ($memory as $item) {
            $messages[] = [
                'role' => $item['role'],
                'content' => $item['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userInput];
        return $messages;
    }

    private function stringifyJsonField(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return implode('; ', array_map(static fn ($k, $v): string => $k . ': ' . (is_scalar($v) ? (string)$v : json_encode($v)), array_keys($decoded), $decoded));
            }
            return $value;
        }

        if (is_array($value)) {
            return implode('; ', array_map(static fn ($k, $v): string => $k . ': ' . (is_scalar($v) ? (string)$v : json_encode($v)), array_keys($value), $value));
        }

        return (string)$value;
    }
}
