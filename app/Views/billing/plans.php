<?php ob_start(); ?>
<h1>Planos e Assinaturas</h1>
<p class="muted">Escolha o plano ideal e pague via M-Pesa ou eMola (gateway Débito).</p>
<div class="cards">
<?php foreach ($plans as $plan): ?>
  <article class="card">
    <h3><?= htmlspecialchars($plan['name']) ?></h3>
    <p><?= htmlspecialchars((string)$plan['description']) ?></p>
    <p><strong><?= htmlspecialchars((string)$plan['price_mt']) ?> MT</strong> / <?= htmlspecialchars((string)$plan['billing_period']) ?></p>
    <a class="btn" href="/billing/checkout?plan=<?= urlencode((string)$plan['slug']) ?>">Escolher plano</a>
  </article>
<?php endforeach; ?>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
