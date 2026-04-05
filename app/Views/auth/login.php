<?php ob_start(); ?>
<h1>Entrar</h1>
<p class="muted">Aceda à sua central comercial.</p>
<?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" action="/login">
  <label>Email</label>
  <input name="email" type="email" required>
  <label>Senha</label>
  <input name="password" type="password" required>
  <button type="submit">Entrar</button>
</form>
<p><a href="/forgot-password">Esqueceu a senha?</a></p>
<p>Não tem conta? <a href="/register">Criar conta</a></p>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
