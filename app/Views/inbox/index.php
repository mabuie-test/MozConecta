<?php ob_start(); ?>
<h1>Inbox Multiatendente</h1>
<div class="card">
  <form method="get" action="/inbox">
    <label>Buscar</label><input name="search" value="<?= htmlspecialchars((string)($filters['search'] ?? '')) ?>" placeholder="Nome ou telefone">
    <label>Status</label>
    <select name="status">
      <option value="">Todos</option>
      <?php foreach (['open'=>'Aberta','pending'=>'Pendente','resolved'=>'Resolvida','closed'=>'Fechada'] as $k => $label): ?>
        <option value="<?= $k ?>" <?= (($filters['status'] ?? '') === $k) ? 'selected' : '' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Filtrar</button>
  </form>
</div>
<div class="card">
  <table>
    <thead><tr><th>Conversa</th><th>Status</th><th>Responsável</th><th>Última atividade</th><th>Ações</th></tr></thead>
    <tbody>
      <?php foreach ($conversations as $conversation): ?>
      <tr>
        <td>
          <strong><?= htmlspecialchars((string)$conversation['contact_name']) ?></strong><br>
          <span class="muted"><?= htmlspecialchars((string)$conversation['contact_phone']) ?></span>
        </td>
        <td><span class="badge"><?= htmlspecialchars((string)$conversation['status']) ?></span></td>
        <td><?= htmlspecialchars(trim((string)($conversation['assigned_first_name'] ?? '') . ' ' . (string)($conversation['assigned_last_name'] ?? '')) ?: '-') ?></td>
        <td><?= htmlspecialchars((string)($conversation['last_message_at'] ?? '-')) ?></td>
        <td><a href="/inbox/show?id=<?= (int)$conversation['id'] ?>">Abrir timeline</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
