<?php ob_start(); ?>
<?php $flow = $graph['flow'] ?? null; $nodes = $graph['nodes'] ?? []; $logs = $graph['logs'] ?? []; ?>
<h1>Editor de Fluxo</h1>
<?php if (!$flow): ?>
  <div class="error">Fluxo não encontrado.</div>
<?php else: ?>
<div class="card">
  <p><strong><?= htmlspecialchars((string)$flow['name']) ?></strong></p>
  <p>Trigger: <?= htmlspecialchars((string)$flow['trigger_type']) ?> | Valor: <?= htmlspecialchars((string)($flow['trigger_value'] ?? '')) ?></p>
</div>

<div class="cards">
  <div class="card">
    <h3>Adicionar nó</h3>
    <form method="post" action="/flows/nodes/add">
      <input type="hidden" name="flow_id" value="<?= (int)$flowId ?>">
      <label>node_key</label><input name="node_key" required>
      <label>Tipo</label>
      <select name="type">
        <option value="send_message">enviar mensagem</option>
        <option value="menu">menu</option>
        <option value="keyword">palavra-chave</option>
        <option value="condition_time">condição horário</option>
        <option value="condition_tag">condição etiqueta</option>
        <option value="condition_stage">condição etapa</option>
        <option value="apply_tag">aplicar etiqueta</option>
        <option value="create_task">criar tarefa</option>
        <option value="move_stage">mudar etapa</option>
        <option value="webhook">disparar webhook</option>
        <option value="handoff_human">encaminhar humano</option>
        <option value="wait_reply">aguardar resposta</option>
        <option value="end">finalizar</option>
      </select>
      <label>Mensagem</label><input name="message">
      <label>Tag</label><input name="tag">
      <label>Task title</label><input name="task_title">
      <label>Task description</label><input name="task_description">
      <label>Stage ID</label><input type="number" name="stage_id">
      <label>Webhook URL</label><input name="url">
      <label>Minutos (wait_reply)</label><input type="number" name="minutes" value="10">
      <label>Minutos para due task</label><input type="number" name="due_minutes" value="60">
      <label><input type="checkbox" name="is_start" value="1"> Nó inicial</label>
      <button type="submit">Adicionar nó</button>
    </form>
  </div>

  <div class="card">
    <h3>Adicionar aresta</h3>
    <form method="post" action="/flows/edges/add">
      <input type="hidden" name="flow_id" value="<?= (int)$flowId ?>">
      <label>from_node_id</label><input type="number" name="from_node_id" required>
      <label>to_node_id</label><input type="number" name="to_node_id" required>
      <label>condition_type</label>
      <select name="condition_type">
        <option value="always">always</option>
        <option value="keyword">keyword</option>
        <option value="option">option</option>
        <option value="time_window">time_window (HH:MM-HH:MM)</option>
        <option value="tag">tag</option>
        <option value="fallback">fallback</option>
      </select>
      <label>condition_value</label><input name="condition_value">
      <label>priority</label><input type="number" name="priority" value="100">
      <button type="submit">Adicionar aresta</button>
    </form>
  </div>
</div>

<div class="card">
  <h3>Nós cadastrados</h3>
  <table>
    <thead><tr><th>ID</th><th>Key</th><th>Tipo</th><th>Start</th></tr></thead>
    <tbody>
      <?php foreach ($nodes as $node): ?>
        <tr>
          <td><?= (int)$node['id'] ?></td>
          <td><?= htmlspecialchars((string)$node['node_key']) ?></td>
          <td><?= htmlspecialchars((string)$node['type']) ?></td>
          <td><?= (int)$node['is_start'] === 1 ? 'sim' : 'não' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h3>Logs de execução</h3>
  <table>
    <thead><tr><th>Quando</th><th>Evento</th><th>Node</th><th>Payload</th></tr></thead>
    <tbody>
      <?php foreach ($logs as $log): ?>
        <tr>
          <td><?= htmlspecialchars((string)$log['created_at']) ?></td>
          <td><?= htmlspecialchars((string)$log['event_type']) ?></td>
          <td><?= htmlspecialchars((string)($log['node_id'] ?? '-')) ?></td>
          <td><code><?= htmlspecialchars((string)($log['event_payload'] ?? '{}')) ?></code></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
