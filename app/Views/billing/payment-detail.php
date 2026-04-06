<?php ob_start(); ?>
<h1>Detalhe do Pagamento</h1>
<div class="card">
  <p><strong>ID:</strong> #<?= (int)$payment['id'] ?></p>
  <p><strong>Invoice:</strong> <?= htmlspecialchars((string)($invoice['invoice_no'] ?? '—')) ?></p>
  <p><strong>Método:</strong> <?= htmlspecialchars(strtoupper((string)($payment['provider_method'] ?? $payment['provider'] ?? '—'))) ?></p>
  <p><strong>Wallet usado:</strong> <?= htmlspecialchars((string)($payment['wallet_id_used'] ?? '—')) ?></p>
  <p><strong>Referência Débito:</strong> <?= htmlspecialchars((string)($payment['debito_reference'] ?? '—')) ?></p>
  <p><strong>Provider ref:</strong> <?= htmlspecialchars((string)($payment['provider_reference'] ?? '—')) ?></p>
  <p><strong>Transação externa:</strong> <?= htmlspecialchars((string)($payment['external_transaction_id'] ?? '—')) ?></p>
  <p><strong>Status:</strong> <?= htmlspecialchars((string)($payment['payment_status'] ?? '—')) ?></p>
  <p><strong>Status bruto:</strong> <?= htmlspecialchars((string)($payment['raw_provider_status'] ?? '—')) ?></p>
  <p><strong>Falha:</strong> <?= htmlspecialchars((string)($payment['failure_reason'] ?? '—')) ?></p>
  <p><strong>Poll attempts:</strong> <?= htmlspecialchars((string)($payment['poll_attempts'] ?? '0')) ?></p>
  <p><strong>Último poll:</strong> <?= htmlspecialchars((string)($payment['last_poll_at'] ?? '—')) ?></p>
  <p><strong>Callback recebido:</strong> <?= htmlspecialchars((string)($payment['callback_received_at'] ?? '—')) ?></p>

  <form method="post" action="/billing/payment-status/recheck" style="margin-top:10px;">
    <input type="hidden" name="payment_id" value="<?= (int)$payment['id'] ?>">
    <button type="submit">Reconsultar status</button>
  </form>

  <?php if (in_array(($payment['payment_status'] ?? ''), ['failed','cancelled','canceled'], true)): ?>
    <form method="post" action="/billing/payment/retry" style="margin-top:10px;">
      <input type="hidden" name="payment_id" value="<?= (int)$payment['id'] ?>">
      <button type="submit">Tentar novamente</button>
    </form>
  <?php endif; ?>
</div>
<p><a class="btn" href="/billing/history">Voltar ao histórico</a></p>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
