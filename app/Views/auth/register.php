<?php ob_start(); ?>
<h1>Criar conta e iniciar trial 24h</h1>
<p class="muted">Registo cria tenant, owner e activa trial automaticamente.</p>
<?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" action="/register">
  <label>Empresa</label>
  <input name="company" value="<?= htmlspecialchars($old['company'] ?? '') ?>" required>
  <label>Nome</label>
  <input name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required>
  <label>Apelido</label>
  <input name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>">
  <label>Email</label>
  <input name="email" type="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
  <label>Telefone</label>
  <input name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" required>
  <label>Senha</label>
  <input name="password" type="password" minlength="8" required>
  <button type="submit">Activar trial</button>
</form>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
