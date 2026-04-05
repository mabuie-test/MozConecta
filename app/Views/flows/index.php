<?php ob_start(); ?>
<h1>Construtor de Fluxos</h1>
<div class="card">
  <h3>Novo fluxo</h3>
  <form method="post" action="/flows/create">
    <label>Nome</label><input name="name" required>
    <label>Tipo de trigger</label>
    <select name="trigger_type">
      <option value="keyword">palavra-chave</option>
      <option value="all_inbound">todas mensagens</option>
      <option value="remarketing">remarketing</option>
      <option value="reentry">reentrada</option>
    </select>
    <label>Trigger value (keywords separadas por vírgula)</label><input name="trigger_value">
    <label>Mensagem fallback</label><input name="fallback_message">
    <label><input type="checkbox" name="allow_reentry" value="1" checked> Permitir reentrada</label>
    <label><input type="checkbox" name="allow_remarketing" value="1"> Permitir remarketing</label>
    <label><input type="checkbox" name="is_active" value="1" checked> Ativo</label>
    <button type="submit">Criar fluxo</button>
  </form>
</div>

<div class="card">
  <table>
    <thead><tr><th>Fluxo</th><th>Trigger</th><th>Status</th><th>Ações</th></tr></thead>
    <tbody>
      <?php foreach ($flows as $flow): ?>
      <tr>
        <td><?= htmlspecialchars((string)$flow['name']) ?></td>
        <td><?= htmlspecialchars((string)$flow['trigger_type']) ?>: <?= htmlspecialchars((string)($flow['trigger_value'] ?? '')) ?></td>
        <td><span class="badge"><?= (int)$flow['is_active'] === 1 ? 'ativo' : 'inativo' ?></span></td>
        <td>
          <a href="/flows/show?id=<?= (int)$flow['id'] ?>">abrir</a>
          <form method="post" action="/flows/toggle" class="inline-form">
            <input type="hidden" name="flow_id" value="<?= (int)$flow['id'] ?>">
            <input type="hidden" name="is_active" value="<?= (int)$flow['is_active'] === 1 ? '0' : '1' ?>">
            <button type="submit"><?= (int)$flow['is_active'] === 1 ? 'desativar' : 'ativar' ?></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
