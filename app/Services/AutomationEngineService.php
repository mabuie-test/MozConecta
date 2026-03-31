<?php
declare(strict_types=1);

namespace App\Services;

final class AutomationEngineService
{
    public function decide(array $context): string
    {
        if (($context['subscription_ok'] ?? false) === false) return 'blocked';
        if (!empty($context['active_flow'])) return 'flow';
        if (!empty($context['keyword_match'])) return 'keyword_rule';
        if (!empty($context['business_rule'])) return 'business_rule';
        if (!empty($context['needs_ai'])) return 'ai';
        return 'handoff_human';
    }
}
