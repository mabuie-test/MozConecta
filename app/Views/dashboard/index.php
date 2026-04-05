<?php ob_start(); ?>
<h1>Bem-vindo, <?= htmlspecialchars($user_name ?? 'Utilizador') ?></h1>
<p class="muted">Resumo operacional da operação comercial.</p>
<div class="cards">
  <article class="card">
    <h3>Plano actual</h3>
    <p><?= htmlspecialchars($subscription['plan_name'] ?? '—') ?></p>
    <span class="badge"><?= htmlspecialchars($subscription['status_code'] ?? '—') ?></span>
  </article>
  <article class="card">
    <h3>Trial</h3>
    <p>Início: <?= htmlspecialchars($subscription['trial_starts_at'] ?? '—') ?></p>
    <p>Fim: <?= htmlspecialchars($subscription['trial_ends_at'] ?? '—') ?></p>
  </article>
  <article class="card">
    <h3>Campanhas</h3>
    <p>Ativas/total: <?= (int)($counts['campaigns'] ?? 0) ?></p>
    <p>Tarefas pendentes: <?= (int)($counts['tasks_pending'] ?? 0) ?></p>
  </article>
</div>

<div class="cards">
  <article class="card">
    <h3>Leads e Inbox</h3>
    <p>Contactos: <?= (int)($counts['contacts'] ?? 0) ?></p>
    <p>Conversas: <?= (int)($counts['conversations'] ?? 0) ?></p>
  </article>
  <article class="card">
    <h3>Consumo</h3>
    <p>Mensagens: <?= (int)($usage['messages_used'] ?? 0) ?></p>
    <p>IA: <?= (int)($usage['ai_used'] ?? 0) ?></p>
  </article>
  <article class="card">
    <h3>Alertas recentes</h3>
    <ul>
      <?php foreach (array_slice($notifications ?? [], 0, 5) as $n): ?>
        <li><strong><?= htmlspecialchars((string)$n['title']) ?>:</strong> <?= htmlspecialchars((string)$n['body']) ?></li>
      <?php endforeach; ?>
    </ul>
  </article>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
