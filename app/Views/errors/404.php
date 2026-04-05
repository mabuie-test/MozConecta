<?php ob_start(); ?>
<h1>404</h1>
<p>Rota não encontrada.</p>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
