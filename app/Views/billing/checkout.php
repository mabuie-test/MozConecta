<?php ob_start(); ?>
<h1>Checkout</h1>
<div class="card">
  <p><strong>Invoice:</strong> <?= htmlspecialchars((string)$invoice['invoice_no']) ?></p>
  <p><strong>Plano:</strong> <?= htmlspecialchars((string)$invoice['plan_name']) ?></p>
  <p><strong>Total:</strong> <?= htmlspecialchars((string)$invoice['amount_total']) ?> <?= htmlspecialchars((string)$invoice['currency']) ?></p>
  <form method="post" action="/billing/checkout">
    <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">
    <label>Método</label>
    <select name="method">
      <option value="mpesa">M-Pesa</option>
      <option value="emola">eMola</option>
    </select>
    <label>Número (MSISDN)</label>
    <input name="msisdn" placeholder="Ex: 841234567 ou 258841234567" required>
    <label>Notas internas (opcional)</label>
    <input name="internal_notes">
    <button type="submit">Iniciar cobrança</button>
  </form>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
