<?php ob_start(); ?>
<h1>Estado do Pagamento</h1>
<div class="card">
  <p><strong>Invoice:</strong> <?= htmlspecialchars((string)($invoice['invoice_no'] ?? '—')) ?></p>
  <p><strong>Referência Débito:</strong> <?= htmlspecialchars((string)($payment['debito_reference'] ?? '—')) ?></p>
  <p><strong>Status:</strong> <span class="badge"><?= htmlspecialchars((string)($payment['payment_status'] ?? '—')) ?></span></p>
  <p><strong>Última verificação:</strong> <?= htmlspecialchars((string)($payment['status_checked_at'] ?? '—')) ?></p>
  <?php if (in_array(($payment['payment_status'] ?? ''), ['pending','processing'], true)): ?>
    <p class="muted">Pagamento ainda pendente. Atualize a página para nova verificação.</p>
  <?php endif; ?>
</div>
<p><a class="btn" href="/billing/history">Ver histórico financeiro</a></p>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
