<?php ob_start(); ?>
<h1>Instâncias WhatsApp</h1>
<div class="card">
  <h3>Nova instância</h3>
  <form method="post" action="/whatsapp/instances/create">
    <label>Nome</label><input name="name" required>
    <label>Número</label><input name="phone_number" required>
    <label>Provider</label><input name="provider_name" value="<?= htmlspecialchars((string)env('WHATSAPP_PROVIDER_DEFAULT', 'generic_api')) ?>">

    <p><strong>Método principal:</strong> Linked Devices / WhatsApp Web (não oficial)</p>
<label>Modo de pareamento</label>
    <select name="pairing_mode"><option value="qr">QR</option><option value="code">Código de pareamento</option></select>

    <p class="muted" style="margin-top:8px;">
      Atenção: Linked Devices/WhatsApp Web via QR/pairing é alternativa não oficial e traz risco real de banimento e instabilidade para SaaS comercial.
    </p>

    <button type="submit">Criar instância</button>
  </form>
</div>
<div class="card">
  <form method="post" action="/whatsapp/instances/sync"><button type="submit">Sincronizar estados</button></form>
  <table>
    <thead><tr><th>ID</th><th>Nome</th><th>Número</th><th>Status</th><th>Provider</th><th>Ações</th></tr></thead>
    <tbody>
      <?php foreach ($instances as $instance): ?>
        <tr>
          <td><?= (int)$instance['id'] ?></td>
          <td><?= htmlspecialchars((string)$instance['name']) ?></td>
          <td><?= htmlspecialchars((string)$instance['phone_number']) ?></td>
          <td><span class="badge"><?= htmlspecialchars((string)$instance['status']) ?></span></td>
          <td><?= htmlspecialchars((string)$instance['provider_name']) ?></td>
          <td>
            <a href="/whatsapp/instances/show?id=<?= (int)$instance['id'] ?>">ver</a> |
            <form method="post" action="/whatsapp/instances/pair" class="inline-form"><input type="hidden" name="id" value="<?= (int)$instance['id'] ?>"><button type="submit">parear</button></form>
            <form method="post" action="/whatsapp/instances/reconnect" class="inline-form"><input type="hidden" name="id" value="<?= (int)$instance['id'] ?>"><button type="submit">reconectar</button></form>
            <form method="post" action="/whatsapp/instances/disconnect" class="inline-form"><input type="hidden" name="id" value="<?= (int)$instance['id'] ?>"><button type="submit">desconectar</button></form>
            <form method="post" action="/whatsapp/instances/delete" class="inline-form"><input type="hidden" name="id" value="<?= (int)$instance['id'] ?>"><button type="submit">eliminar</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
