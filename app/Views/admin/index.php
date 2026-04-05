<?php ob_start(); ?>
<h1>Admin Global</h1>
<div class="cards">
  <?php foreach (($stats ?? []) as $k => $v): ?>
    <article class="card"><h3><?= e(ucwords(str_replace('_', ' ', (string)$k))) ?></h3><p><?= (int)$v ?></p></article>
  <?php endforeach; ?>
</div>

<div class="card">
  <h3>CMS básico da landing</h3>
  <form method="post" action="/admin/cms/save">
    <label>Headline</label><input name="cms_headline" value="<?= e((string)($cms['cms.landing.headline'] ?? 'Central inteligente de atendimento e vendas via WhatsApp')) ?>">
    <label>Subheadline</label><input name="cms_subheadline" value="<?= e((string)($cms['cms.landing.subheadline'] ?? 'Automação, CRM, campanhas e IA em um único painel SaaS')) ?>">
    <button type="submit">Guardar CMS</button>
  </form>
</div>

<div class="card">
  <h3>Auditoria recente</h3>
  <table>
    <thead><tr><th>Quando</th><th>Ação</th><th>Entidade</th><th>ID</th></tr></thead>
    <tbody>
      <?php foreach (($audit ?? []) as $log): ?>
      <tr>
        <td><?= e((string)$log['created_at']) ?></td>
        <td><?= e((string)$log['action']) ?></td>
        <td><?= e((string)$log['entity_type']) ?></td>
        <td><?= e((string)($log['entity_id'] ?? '-')) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
