<?php ob_start(); ?>
<h1>Assinatura</h1>
<div class="card">
  <p><strong>Plano:</strong> <?= htmlspecialchars((string)($subscription['plan_name'] ?? '—')) ?></p>
  <p><strong>Status:</strong> <?= htmlspecialchars((string)($subscription['status_code'] ?? '—')) ?></p>
  <p><strong>Período:</strong> <?= htmlspecialchars((string)($subscription['current_period_starts_at'] ?? '—')) ?> até <?= htmlspecialchars((string)($subscription['current_period_ends_at'] ?? '—')) ?></p>
</div>
<p><a class="btn" href="/billing/plans">Upgrade/Downgrade</a></p>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
