<?php ob_start(); ?>
<h1>Bot de Venda de Internet</h1>
<div class="cards">
  <div class="card">
    <h3>Novo pacote</h3>
    <form method="post" action="/internet/packages/create">
      <label>Nome</label><input name="name" required>
      <label>Descrição</label><input name="description">
      <label>Preço</label><input type="number" step="0.01" name="price" required>
      <label>Validade (dias)</label><input type="number" name="validity_days" value="30">
      <label>Mensagem comercial</label><textarea name="sales_message" rows="3"></textarea>
      <label><input type="checkbox" name="is_active" value="1" checked> Activo</label>
      <button type="submit">Cadastrar pacote</button>
    </form>
  </div>
  <div class="card">
    <h3>Novo pedido</h3>
    <form method="post" action="/internet/orders/create">
      <label>ID Contacto</label><input type="number" name="contact_id" required>
      <label>ID Pacote</label><input type="number" name="package_id" required>
      <label>Nome cliente</label><input name="customer_name">
      <label>Telefone cliente</label><input name="customer_phone" required>
      <label>Endereço de instalação</label><input name="installation_address">
      <label>Notas operador</label><input name="operator_notes">
      <button type="submit">Criar pedido</button>
    </form>
  </div>
</div>

<div class="card">
  <h3>Pacotes</h3>
  <table>
    <thead><tr><th>ID</th><th>Pacote</th><th>Preço</th><th>Validade</th></tr></thead>
    <tbody>
      <?php foreach ($packages as $pkg): ?>
      <tr>
        <td><?= (int)$pkg['id'] ?></td>
        <td><?= htmlspecialchars((string)$pkg['name']) ?></td>
        <td><?= htmlspecialchars((string)$pkg['price']) ?></td>
        <td><?= (int)$pkg['validity_days'] ?> dias</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h3>Pedidos</h3>
  <table>
    <thead><tr><th>ID</th><th>Cliente</th><th>Pacote</th><th>Status</th><th>Ação</th></tr></thead>
    <tbody>
      <?php foreach ($orders as $order): ?>
      <tr>
        <td><?= (int)$order['id'] ?></td>
        <td><?= htmlspecialchars((string)$order['contact_name']) ?> (<?= htmlspecialchars((string)$order['customer_phone']) ?>)</td>
        <td><?= htmlspecialchars((string)$order['package_name']) ?></td>
        <td><span class="badge"><?= htmlspecialchars((string)$order['status']) ?></span></td>
        <td>
          <form method="post" action="/internet/orders/status" class="inline-form">
            <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
            <select name="status">
              <option value="new">new</option>
              <option value="qualified">qualified</option>
              <option value="sent_to_operator">sent_to_operator</option>
              <option value="approved">approved</option>
              <option value="installed">installed</option>
              <option value="cancelled">cancelled</option>
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
