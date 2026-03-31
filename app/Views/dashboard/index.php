<?php ob_start(); ?>
<h1>Dashboard Cliente</h1>
<div class="grid">
  <div class="card"><strong>Plano</strong><br><?= htmlspecialchars($data['subscription']['plan_name'] ?? '—') ?></div>
  <div class="card"><strong>Status</strong><br><?= htmlspecialchars($data['subscription']['status'] ?? '—') ?></div>
  <div class="card"><strong>Leads</strong><br><?= (int)($data['counts']['contacts'] ?? 0) ?></div>
  <div class="card"><strong>Conversas</strong><br><?= (int)($data['counts']['conversations'] ?? 0) ?></div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
