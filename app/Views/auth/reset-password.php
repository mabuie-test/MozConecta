<?php ob_start(); ?>
<h1>Redefinir senha</h1>
<?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" action="/reset-password">
  <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
  <label>Email</label>
  <input name="email" type="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
  <label>Nova senha</label>
  <input name="password" type="password" minlength="8" required>
  <button type="submit">Actualizar senha</button>
</form>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
