<?php
declare(strict_types=1);

namespace App\Integrations\AI;

use RuntimeException;

final class GeminiProvider implements AIProviderInterface
{
    public function providerName(): string
    {
        return 'gemini';
    }

    public function chat(array $messages, array $options = []): array
    {
        $apiKey = (string)env('GEMINI_API_KEY', '');
        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY não configurada.');
        }

        $model = (string)($options['model'] ?? env('GEMINI_MODEL', 'gemini-1.5-flash'));
        $combined = [];
        foreach ($messages as $m) {
            $combined[] = strtoupper((string)$m['role']) . ': ' . (string)$m['content'];
        }
        $prompt = implode("\n", $combined);

        $url = rtrim((string)env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/')
            . '/models/' . rawurlencode($model) . ':generateContent?key=' . urlencode($apiKey);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => (float)($options['temperature'] ?? 0.3),
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => (int)env('AI_TIMEOUT', 25),
        ]);

        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $error !== '') {
            throw new RuntimeException('Falha Gemini: ' . $error);
        }

        $json = json_decode((string)$raw, true);
        if ($httpCode >= 400 || !is_array($json)) {
            throw new RuntimeException('Resposta inválida Gemini.');
        }

        $text = (string)($json['candidates'][0]['content']['parts'][0]['text'] ?? '');
        return [
            'provider' => $this->providerName(),
            'model' => $model,
            'text' => $text,
            'raw' => $json,
        ];
    }
}
