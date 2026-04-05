<?php ob_start(); ?>
<?php
$profile = $profile ?? [];
$faq = is_string($profile['faq_json'] ?? null) ? implode("\n", array_values(json_decode((string)$profile['faq_json'], true) ?: [])) : '';
$products = is_string($profile['products_services_json'] ?? null) ? implode("\n", array_values(json_decode((string)$profile['products_services_json'], true) ?: [])) : '';
$policies = is_string($profile['policies_json'] ?? null) ? implode("\n", array_values(json_decode((string)$profile['policies_json'], true) ?: [])) : '';
$goals = is_string($profile['business_goals_json'] ?? null) ? implode("\n", array_values(json_decode((string)$profile['business_goals_json'], true) ?: [])) : '';
?>
<h1>Assistente IA por Tenant</h1>
<div class="card">
  <form method="post" action="/ai/settings/save">
    <label>Nome do assistente</label><input name="assistant_name" value="<?= htmlspecialchars((string)($profile['assistant_name'] ?? 'Assistente MozConecta')) ?>">
    <label>Persona</label><input name="persona" value="<?= htmlspecialchars((string)($profile['persona'] ?? '')) ?>">
    <label>Idioma</label><input name="language_code" value="<?= htmlspecialchars((string)($profile['language_code'] ?? 'pt-PT')) ?>">
    <label>Tom</label><input name="tone" value="<?= htmlspecialchars((string)($profile['tone'] ?? 'profissional')) ?>">
    <label>Regras do negócio</label><input name="business_rules" value="<?= htmlspecialchars((string)($profile['business_rules'] ?? '')) ?>">
    <label>FAQ (1 por linha)</label><textarea name="faq" rows="4" style="width:100%"><?= htmlspecialchars($faq) ?></textarea>
    <label>Produtos/serviços (1 por linha)</label><textarea name="products_services" rows="4" style="width:100%"><?= htmlspecialchars($products) ?></textarea>
    <label>Políticas (1 por linha)</label><textarea name="policies" rows="4" style="width:100%"><?= htmlspecialchars($policies) ?></textarea>
    <label>Objetivos comerciais (1 por linha)</label><textarea name="business_goals" rows="4" style="width:100%"><?= htmlspecialchars($goals) ?></textarea>
    <label>Provider principal</label>
    <select name="primary_provider">
      <option value="openrouter" <?= (($profile['primary_provider'] ?? 'openrouter') === 'openrouter') ? 'selected' : '' ?>>openrouter</option>
      <option value="gemini" <?= (($profile['primary_provider'] ?? '') === 'gemini') ? 'selected' : '' ?>>gemini</option>
    </select>
    <label>Provider fallback</label>
    <select name="fallback_provider">
      <option value="gemini" <?= (($profile['fallback_provider'] ?? 'gemini') === 'gemini') ? 'selected' : '' ?>>gemini</option>
      <option value="openrouter" <?= (($profile['fallback_provider'] ?? '') === 'openrouter') ? 'selected' : '' ?>>openrouter</option>
    </select>
    <button type="submit">Salvar configuração</button>
  </form>
</div>

<div class="card">
  <h3>Teste do motor híbrido</h3>
  <form method="post" action="/ai/test-hybrid">
    <label>Conversation ID</label><input type="number" name="conversation_id" required>
    <label>Contact ID</label><input type="number" name="contact_id" required>
    <label>Mensagem inbound</label><input name="input" required>
    <button type="submit">Executar teste</button>
  </form>
  <p class="muted">Retorno em JSON para depuração rápida do pipeline híbrido.</p>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
