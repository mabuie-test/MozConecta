<?php ob_start(); ?>
<h1>Recuperar senha</h1>
<?php if (!empty($success)): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
<form method="post" action="/forgot-password">
  <label>Email</label>
  <input name="email" type="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
  <button type="submit">Enviar instruções</button>
</form>
<?php if (!empty($debug_token)): ?>
  <p class="muted">Modo dev token: <code><?= htmlspecialchars($debug_token) ?></code></p>
  <p><a href="/reset-password?email=<?= urlencode((string)$email) ?>&token=<?= urlencode((string)$debug_token) ?>">Abrir redefinição</a></p>
<?php endif; ?>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
