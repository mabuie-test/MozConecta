<?php ob_start(); ?>
<h1>Assinatura necessária</h1>
<p>O seu trial/assinatura não permite esta operação neste momento.</p>
<p><a class="btn" href="/dashboard">Voltar ao painel</a></p>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
