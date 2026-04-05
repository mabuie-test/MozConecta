<?php ob_start(); ?>
<?php $conversation = $details['conversation'] ?? null; $messages = $details['messages'] ?? []; ?>
<h1>Timeline da Conversa</h1>
<?php if (!$conversation): ?>
  <div class="error">Conversa não encontrada.</div>
<?php else: ?>
<div class="card">
  <p><strong>Contacto:</strong> <?= htmlspecialchars((string)$conversation['contact_name']) ?> (<?= htmlspecialchars((string)$conversation['contact_phone']) ?>)</p>
  <p><strong>Status:</strong> <span class="badge"><?= htmlspecialchars((string)$conversation['status']) ?></span></p>
</div>

<div class="card timeline">
  <?php foreach ($messages as $message): ?>
    <div class="msg msg-<?= htmlspecialchars((string)$message['direction']) ?>">
      <div class="muted"><?= htmlspecialchars((string)$message['created_at']) ?> • <?= htmlspecialchars((string)$message['message_type']) ?></div>
      <div><?= nl2br(htmlspecialchars((string)($message['body'] ?? ''))) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="cards">
  <div class="card">
    <h3>Enviar mensagem</h3>
    <form method="post" action="/inbox/send">
      <input type="hidden" name="conversation_id" value="<?= (int)$conversation['id'] ?>">
      <label>Mensagem</label>
      <input name="body" required>
      <button type="submit">Enviar</button>
    </form>
  </div>
  <div class="card">
    <h3>Nota interna</h3>
    <form method="post" action="/inbox/note">
      <input type="hidden" name="conversation_id" value="<?= (int)$conversation['id'] ?>">
      <label>Nota</label>
      <input name="note" required>
      <button type="submit">Salvar nota</button>
    </form>
  </div>
</div>

<div class="cards">
  <div class="card">
    <h3>Takeover manual</h3>
    <form method="post" action="/inbox/takeover">
      <input type="hidden" name="conversation_id" value="<?= (int)$conversation['id'] ?>">
      <button type="submit">Assumir conversa</button>
    </form>
  </div>
  <div class="card">
    <h3>Atribuir atendente</h3>
    <form method="post" action="/inbox/assign">
      <input type="hidden" name="conversation_id" value="<?= (int)$conversation['id'] ?>">
      <label>ID do utilizador</label>
      <input name="assigned_user_id" type="number" min="1" required>
      <button type="submit">Atribuir</button>
    </form>
  </div>
  <div class="card">
    <h3>Mudar estado</h3>
    <form method="post" action="/inbox/status">
      <input type="hidden" name="conversation_id" value="<?= (int)$conversation['id'] ?>">
      <select name="status">
        <option value="open">Aberta</option>
        <option value="pending">Pendente</option>
        <option value="resolved">Resolvida</option>
        <option value="closed">Fechada</option>
      </select>
      <button type="submit">Atualizar</button>
    </form>
  </div>
</div>
<?php endif; ?>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
