<?php ob_start(); ?>
<h1>Admin Global (Fase 1)</h1>
<p>Área inicial para monitoramento e operações globais do SaaS.</p>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
