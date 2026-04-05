<?php ob_start(); ?>
<h1>Histórico Financeiro</h1>
<div class="card">
  <table>
    <thead><tr><th>Invoice</th><th>Plano</th><th>Total</th><th>Status</th><th>Vencimento</th></tr></thead>
    <tbody>
    <?php foreach ($history as $row): ?>
      <tr>
        <td><?= htmlspecialchars((string)$row['invoice_no']) ?></td>
        <td><?= htmlspecialchars((string)$row['plan_name']) ?></td>
        <td><?= htmlspecialchars((string)$row['amount_total']) ?> <?= htmlspecialchars((string)$row['currency']) ?></td>
        <td><?= htmlspecialchars((string)$row['status']) ?></td>
        <td><?= htmlspecialchars((string)$row['due_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
