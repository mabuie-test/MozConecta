<?php
declare(strict_types=1);

namespace App\Integrations\AI;

use RuntimeException;

final class OpenRouterProvider implements AIProviderInterface
{
    public function providerName(): string
    {
        return 'openrouter';
    }

    public function chat(array $messages, array $options = []): array
    {
        $apiKey = (string)env('OPENROUTER_API_KEY', '');
        if ($apiKey === '') {
            throw new RuntimeException('OPENROUTER_API_KEY não configurada.');
        }

        $model = (string)($options['model'] ?? env('OPENROUTER_MODEL', 'openai/gpt-4o-mini'));
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => (float)($options['temperature'] ?? 0.3),
        ];

        $ch = curl_init((string)env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1') . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'HTTP-Referer: ' . (string)env('APP_URL', 'http://localhost'),
                'X-Title: ' . (string)env('APP_NAME', 'MozConecta'),
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => (int)env('AI_TIMEOUT', 25),
        ]);

        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $error !== '') {
            throw new RuntimeException('Falha OpenRouter: ' . $error);
        }
        $json = json_decode((string)$raw, true);
        if ($httpCode >= 400 || !is_array($json)) {
            throw new RuntimeException('Resposta inválida OpenRouter.');
        }

        $text = (string)($json['choices'][0]['message']['content'] ?? '');
        return [
            'provider' => $this->providerName(),
            'model' => $model,
            'text' => $text,
            'raw' => $json,
        ];
    }
}
