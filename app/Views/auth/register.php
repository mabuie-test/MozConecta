<?php ob_start(); ?>
<h1>Criar conta</h1>
<form method="post" action="/register">
  <input name="company" placeholder="Empresa" required>
  <input name="name" placeholder="Seu nome" required>
  <input name="email" type="email" placeholder="Email" required>
  <input name="password" type="password" placeholder="Senha" required>
  <button type="submit">Ativar trial 24h</button>
</form>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
