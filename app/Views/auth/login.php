<?php ob_start(); ?>
<h1>Login</h1>
<?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" action="/login">
  <input name="email" type="email" placeholder="Email" required>
  <input name="password" type="password" placeholder="Senha" required>
  <button type="submit">Entrar</button>
</form>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
