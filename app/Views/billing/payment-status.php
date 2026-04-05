<?php ob_start(); ?>
<h1>Estado do Pagamento</h1>
<div class="card">
  <p><strong>Invoice:</strong> <?= htmlspecialchars((string)($invoice['invoice_no'] ?? '—')) ?></p>
  <p><strong>Método:</strong> <?= htmlspecialchars(strtoupper((string)($payment['provider_method'] ?? $payment['provider'] ?? '—'))) ?></p>
  <p><strong>Referência Débito:</strong> <?= htmlspecialchars((string)($payment['debito_reference'] ?? '—')) ?></p>
  <p><strong>Status:</strong> <span class="badge"><?= htmlspecialchars((string)($payment['payment_status'] ?? '—')) ?></span></p>
  <p><strong>Status bruto provider:</strong> <?= htmlspecialchars((string)($payment['raw_provider_status'] ?? '—')) ?></p>
  <p><strong>Última verificação:</strong> <?= htmlspecialchars((string)($payment['status_checked_at'] ?? '—')) ?></p>
  <?php if (in_array(($payment['payment_status'] ?? ''), ['pending','processing'], true)): ?>
    <p class="muted">Pagamento pendente. Pode reconsultar manualmente abaixo.</p>
  <?php endif; ?>

  <form method="post" action="/billing/payment-status/recheck" style="margin-top:12px;">
    <input type="hidden" name="payment_id" value="<?= (int)$payment['id'] ?>">
    <button type="submit">Reconsultar status agora</button>
  </form>
</div>
<p>
  <a class="btn" href="/billing/payment-detail?payment_id=<?= (int)$payment['id'] ?>">Ver detalhes técnicos</a>
  <a class="btn" href="/billing/history">Ver histórico financeiro</a>
</p>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
