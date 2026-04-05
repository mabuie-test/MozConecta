<?php ob_start(); ?>
<h1>CRM de Contactos e Leads</h1>
<div class="card">
  <form method="get" action="/crm/contacts">
    <label>Buscar</label><input name="search" value="<?= htmlspecialchars((string)($filters['search'] ?? '')) ?>" placeholder="Nome, telefone, email">
    <label>Estágio</label>
    <select name="stage_id">
      <option value="">Todos</option>
      <?php foreach ($stages as $stage): ?>
        <option value="<?= (int)$stage['id'] ?>" <?= ((string)($filters['stage_id'] ?? '') === (string)$stage['id']) ? 'selected' : '' ?>><?= htmlspecialchars((string)$stage['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Filtrar</button>
  </form>
</div>

<div class="card">
  <h3>Novo contacto</h3>
  <form method="post" action="/crm/contacts/store">
    <label>Nome de exibição</label><input name="display_name" required>
    <label>Telefone</label><input name="phone" required>
    <label>Email</label><input name="email">
    <label>Origem do lead</label><input name="lead_origin" placeholder="whatsapp, site, campanha">
    <label>Estágio do funil</label>
    <select name="funnel_stage_id">
      <?php foreach ($stages as $stage): ?>
        <option value="<?= (int)$stage['id'] ?>"><?= htmlspecialchars((string)$stage['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <label>Prioridade</label>
    <select name="priority">
      <option value="low">Baixa</option>
      <option value="medium" selected>Média</option>
      <option value="high">Alta</option>
      <option value="urgent">Urgente</option>
    </select>
    <label>Valor potencial</label><input name="potential_value" type="number" min="0" step="0.01">
    <label>Etiquetas (separadas por vírgula)</label><input name="tags" placeholder="vip, quente, retorno">
    <label>Notas</label><input name="notes">
    <button type="submit">Criar lead</button>
  </form>
</div>

<div class="card">
  <table>
    <thead><tr><th>Contacto</th><th>Origem</th><th>Funil</th><th>Prioridade</th><th>Score</th><th>Valor</th><th>Atualizar</th></tr></thead>
    <tbody>
      <?php foreach ($contacts as $contact): ?>
      <tr>
        <td>
          <strong><?= htmlspecialchars((string)$contact['display_name']) ?></strong><br>
          <span class="muted"><?= htmlspecialchars((string)$contact['phone']) ?></span>
        </td>
        <td><?= htmlspecialchars((string)($contact['lead_origin'] ?? '-')) ?></td>
        <td><?= htmlspecialchars((string)($contact['stage_name'] ?? '-')) ?></td>
        <td><span class="badge"><?= htmlspecialchars((string)$contact['priority']) ?></span></td>
        <td><?= (int)($contact['lead_score'] ?? 0) ?></td>
        <td><?= htmlspecialchars((string)($contact['potential_value'] ?? '-')) ?></td>
        <td>
          <form method="post" action="/crm/contacts/update">
            <input type="hidden" name="id" value="<?= (int)$contact['id'] ?>">
            <input type="hidden" name="display_name" value="<?= htmlspecialchars((string)$contact['display_name']) ?>">
            <input type="hidden" name="phone" value="<?= htmlspecialchars((string)$contact['phone']) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars((string)($contact['email'] ?? '')) ?>">
            <input type="hidden" name="lead_origin" value="<?= htmlspecialchars((string)($contact['lead_origin'] ?? '')) ?>">
            <input type="hidden" name="priority" value="<?= htmlspecialchars((string)($contact['priority'] ?? 'medium')) ?>">
            <input type="hidden" name="potential_value" value="<?= htmlspecialchars((string)($contact['potential_value'] ?? '')) ?>">
            <input type="hidden" name="notes" value="<?= htmlspecialchars((string)($contact['notes'] ?? '')) ?>">
            <select name="funnel_stage_id">
              <?php foreach ($stages as $stage): ?>
                <option value="<?= (int)$stage['id'] ?>" <?= ((int)$contact['funnel_stage_id'] === (int)$stage['id']) ? 'selected' : '' ?>><?= htmlspecialchars((string)$stage['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit">Mudar estágio</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
