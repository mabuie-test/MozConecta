<?php ob_start(); ?>
<h1>Bem-vindo, <?= htmlspecialchars($user_name ?? 'Utilizador') ?></h1>
<p class="muted">O seu onboarding foi concluído e o trial está activo.</p>
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
    <h3>Próximos passos</h3>
    <p>Conectar instância WhatsApp, configurar IA e montar funil.</p>
  </article>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
