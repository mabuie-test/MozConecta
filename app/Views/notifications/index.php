<?php ob_start(); ?>
<h1>Notificações internas</h1>
<div class="card">
  <table>
    <thead><tr><th>Tipo</th><th>Título</th><th>Mensagem</th><th>Quando</th><th>Ação</th></tr></thead>
    <tbody>
      <?php foreach ($notifications as $n): ?>
      <tr>
        <td><span class="badge"><?= htmlspecialchars((string)$n['type']) ?></span></td>
        <td><?= htmlspecialchars((string)$n['title']) ?></td>
        <td><?= htmlspecialchars((string)$n['body']) ?></td>
        <td><?= htmlspecialchars((string)$n['created_at']) ?></td>
        <td>
          <form method="post" action="/notifications/read" class="inline-form">
            <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
            <button type="submit">Marcar lida</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
