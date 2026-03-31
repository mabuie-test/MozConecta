<?php ob_start(); ?>
<h1>Painel Administrativo Global</h1>
<ul>
  <li>Tenants: <?= (int)($stats['tenants'] ?? 0) ?></li>
  <li>Assinaturas Ativas: <?= (int)($stats['active_subscriptions'] ?? 0) ?></li>
  <li>Instâncias WhatsApp: <?= (int)($stats['instances'] ?? 0) ?></li>
</ul>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
