<?php ob_start(); ?>
<h1>Perfil</h1>
<?php if (!empty($success)): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
<?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<div class="cards">
  <article class="card">
    <h3>Dados pessoais</h3>
    <form method="post" action="/profile">
      <label>Nome</label>
      <input name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
      <label>Apelido</label>
      <input name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
      <label>Telefone</label>
      <input name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
      <button type="submit">Guardar perfil</button>
    </form>
  </article>
  <article class="card">
    <h3>Alterar senha</h3>
    <form method="post" action="/profile/change-password">
      <label>Senha actual</label>
      <input name="current_password" type="password" required>
      <label>Nova senha</label>
      <input name="new_password" type="password" minlength="8" required>
      <button type="submit">Actualizar senha</button>
    </form>
  </article>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
