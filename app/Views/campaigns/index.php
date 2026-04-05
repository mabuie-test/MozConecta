<?php ob_start(); ?>
<h1>Campanhas e Remarketing</h1>
<div class="card">
  <h3>Nova campanha</h3>
  <form method="post" action="/campaigns/create">
    <label>Nome</label><input name="name" required>
    <label>Tipo</label>
    <select name="type">
      <option value="broadcast">broadcast</option>
      <option value="remarketing">remarketing</option>
      <option value="post_sale">pós-venda</option>
      <option value="cold_leads">clientes frios</option>
      <option value="lost_leads">leads perdidos</option>
    </select>
    <label>Mensagem template</label><textarea name="message_template" rows="3" required></textarea>
    <label>Segmentação</label>
    <select name="segment_type">
      <option value="all">todos</option>
      <option value="tags">etiquetas</option>
      <option value="stage">etapa do funil</option>
      <option value="cold">clientes frios</option>
      <option value="lost">leads perdidos</option>
      <option value="post_sale">pós-venda</option>
    </select>
    <label>Valor de segmento (ex: vip, stage id)</label><input name="segment_value">
    <label>Lote</label><input type="number" name="batch_size" value="50" min="1">
    <label>Agendar (YYYY-MM-DD HH:MM:SS)</label><input name="scheduled_at">
    <button type="submit">Criar campanha</button>
  </form>
</div>

<div class="card">
  <table>
    <thead><tr><th>ID</th><th>Nome</th><th>Status</th><th>Segmento</th><th>Envio</th><th>Ações</th></tr></thead>
    <tbody>
      <?php foreach ($campaigns as $campaign): ?>
      <tr>
        <td><?= (int)$campaign['id'] ?></td>
        <td><?= htmlspecialchars((string)$campaign['name']) ?></td>
        <td><span class="badge"><?= htmlspecialchars((string)$campaign['status']) ?></span></td>
        <td><?= htmlspecialchars((string)$campaign['segment_type']) ?> <?= htmlspecialchars((string)($campaign['segment_value'] ?? '')) ?></td>
        <td><?= (int)$campaign['sent_count'] ?>/<?= (int)$campaign['total_recipients'] ?> (falhas <?= (int)$campaign['failed_count'] ?>)</td>
        <td>
          <form method="post" action="/campaigns/run" class="inline-form"><input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>"><button type="submit">rodar lote</button></form>
          <form method="post" action="/campaigns/pause" class="inline-form"><input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>"><button type="submit">pausar</button></form>
          <form method="post" action="/campaigns/resume" class="inline-form"><input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>"><button type="submit">retomar</button></form>
          <form method="post" action="/campaigns/cancel" class="inline-form"><input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>"><button type="submit">cancelar</button></form>
          <form method="post" action="/campaigns/report" class="inline-form"><input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>"><button type="submit">relatório</button></form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
