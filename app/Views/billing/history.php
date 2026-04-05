<?php ob_start(); ?>
<h1>Histórico Financeiro</h1>
<div class="card">
  <h3>Pagamentos</h3>
  <table>
    <thead><tr><th>ID</th><th>Invoice</th><th>Método</th><th>Valor</th><th>Status</th><th>Débito Ref.</th><th>Ações</th></tr></thead>
    <tbody>
    <?php foreach (($history['payments'] ?? []) as $row): ?>
      <tr>
        <td>#<?= (int)$row['id'] ?></td>
        <td><?= htmlspecialchars((string)$row['invoice_no']) ?></td>
        <td><?= htmlspecialchars(strtoupper((string)($row['provider_method'] ?? $row['provider'] ?? '—'))) ?></td>
        <td><?= htmlspecialchars((string)$row['amount']) ?> <?= htmlspecialchars((string)$row['currency']) ?></td>
        <td><?= htmlspecialchars((string)$row['payment_status']) ?></td>
        <td><?= htmlspecialchars((string)($row['debito_reference'] ?? '—')) ?></td>
        <td><a href="/billing/payment-detail?payment_id=<?= (int)$row['id'] ?>">Detalhes</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h3>Invoices</h3>
  <table>
    <thead><tr><th>Invoice</th><th>Plano</th><th>Total</th><th>Status</th><th>Vencimento</th></tr></thead>
    <tbody>
    <?php foreach (($history['invoices'] ?? []) as $row): ?>
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
