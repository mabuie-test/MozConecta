<?php
declare(strict_types=1);

namespace App\Services;

final class IntentClassifierService
{
    public function classify(string $input): array
    {
        $text = mb_strtolower(trim($input));

        $isGreeting = preg_match('/\b(ol[aá]|oi|bom dia|boa tarde|boa noite)\b/u', $text) === 1;
        $isFaqIntent = preg_match('/\b(pre[cç]o|plano|hor[aá]rio|endere[cç]o|suporte|cancelar)\b/u', $text) === 1;
        $isComplex = mb_strlen($text) > 120 || str_contains($text, '?');
        $needsHuman = preg_match('/\b(reclama[cç][aã]o|processo|advogado|urgente|falar com humano|atendente)\b/u', $text) === 1;

        return [
            'is_greeting' => $isGreeting,
            'is_faq_intent' => $isFaqIntent,
            'is_complex' => $isComplex,
            'needs_human' => $needsHuman,
            'needs_ai' => !$needsHuman && ($isComplex || $isFaqIntent),
        ];
    }
}
