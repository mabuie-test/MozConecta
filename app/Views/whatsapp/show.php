<?php ob_start();
$meta = [];
if (!empty($instance['metadata_json'])) {
  $decoded = json_decode((string)$instance['metadata_json'], true);
  $meta = is_array($decoded) ? $decoded : [];
}
$connectionMethod = (string)($meta['connection_method'] ?? 'official_api');
?>
<h1>Instância #<?= (int)($instance['id'] ?? 0) ?></h1>
<div class="cards">
  <article class="card">
    <h3>Estado</h3>
    <p>Status: <span class="badge"><?= htmlspecialchars((string)($instance['status'] ?? '—')) ?></span></p>
    <p>Método de conexão: <strong><?= htmlspecialchars($connectionMethod) ?></strong></p>
    <?php if ($connectionMethod === 'linked_devices_unofficial'): ?>
      <p class="muted">Aviso: conexão não oficial (Linked Devices/WhatsApp Web) com risco de banimento e instabilidade.</p>
    <?php endif; ?>
    <p>Erro: <?= htmlspecialchars((string)($instance['last_error'] ?? '—')) ?></p>
    <p>Conectado em: <?= htmlspecialchars((string)($instance['connected_at'] ?? '—')) ?></p>
    <p>Desconectado em: <?= htmlspecialchars((string)($instance['disconnected_at'] ?? '—')) ?></p>
  </article>
  <article class="card">
    <h3>Pareamento</h3>
    <?php if (!empty($instance['qr_code'])): ?>
      <pre style="white-space:pre-wrap"><?= htmlspecialchars((string)$instance['qr_code']) ?></pre>
      <p>QR expira em: <?= htmlspecialchars((string)($instance['qr_expires_at'] ?? '—')) ?></p>
    <?php else: ?>
      <p>Sem QR ativo no momento.</p>
    <?php endif; ?>
  </article>
</div>
<div class="card">
  <h3>Sessões de pareamento</h3>
  <table>
    <thead><tr><th>ID</th><th>Status</th><th>Ref</th><th>Expira</th><th>Criado</th></tr></thead>
    <tbody>
    <?php foreach ($pairings as $p): ?>
      <tr><td><?= (int)$p['id'] ?></td><td><?= htmlspecialchars((string)$p['status']) ?></td><td><?= htmlspecialchars((string)$p['provider_reference']) ?></td><td><?= htmlspecialchars((string)$p['qr_expires_at']) ?></td><td><?= htmlspecialchars((string)$p['created_at']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<div class="card">
  <h3>Eventos técnicos</h3>
  <table>
    <thead><tr><th>Data</th><th>Tipo</th><th>Status</th><th>Mensagem</th></tr></thead>
    <tbody>
    <?php foreach ($events as $event): ?>
      <tr><td><?= htmlspecialchars((string)$event['created_at']) ?></td><td><?= htmlspecialchars((string)$event['event_type']) ?></td><td><?= htmlspecialchars((string)$event['event_status']) ?></td><td><?= htmlspecialchars((string)($event['technical_message'] ?? '')) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p><a class="btn" href="/whatsapp/instances">Voltar</a></p>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
