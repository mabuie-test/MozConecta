<?php ob_start(); ?>
<h1>Tarefas & Follow-up</h1>
<div class="card">
  <form method="get" action="/tasks">
    <label>Filtro</label>
    <select name="bucket">
      <option value="all" <?= $bucket === 'all' ? 'selected' : '' ?>>Todas</option>
      <option value="pending" <?= $bucket === 'pending' ? 'selected' : '' ?>>Pendentes</option>
      <option value="overdue" <?= $bucket === 'overdue' ? 'selected' : '' ?>>Vencidas</option>
    </select>
    <button type="submit">Aplicar</button>
  </form>
</div>

<div class="card">
  <h3>Nova tarefa</h3>
  <form method="post" action="/tasks/create">
    <label>Título</label><input name="title" required>
    <label>Descrição</label><input name="description">
    <label>ID utilizador responsável</label><input type="number" min="1" name="assigned_user_id">
    <label>Prazo (YYYY-MM-DD HH:MM:SS)</label><input name="due_at" placeholder="2026-04-10 12:00:00">
    <button type="submit">Criar tarefa</button>
  </form>
</div>

<div class="card">
  <table>
    <thead><tr><th>Tarefa</th><th>Responsável</th><th>Status</th><th>Prazo</th><th>Ações</th></tr></thead>
    <tbody>
      <?php foreach ($tasks as $task): ?>
      <tr>
        <td>
          <strong><?= htmlspecialchars((string)$task['title']) ?></strong><br>
          <span class="muted"><?= htmlspecialchars((string)($task['description'] ?? '')) ?></span>
        </td>
        <td><?= htmlspecialchars(trim((string)($task['assigned_first_name'] ?? '') . ' ' . (string)($task['assigned_last_name'] ?? '')) ?: '-') ?></td>
        <td><span class="badge"><?= htmlspecialchars((string)$task['status']) ?></span></td>
        <td><?= htmlspecialchars((string)($task['due_at'] ?? '-')) ?></td>
        <td>
          <form method="post" action="/tasks/status" class="inline-form">
            <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
            <select name="status">
              <option value="pending">pending</option>
              <option value="in_progress">in_progress</option>
              <option value="done">done</option>
              <option value="cancelled">cancelled</option>
              <option value="overdue">overdue</option>
            </select>
            <button type="submit">Atualizar</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
