<?php ob_start(); ?>
<section class="hero">
  <h1>Automação comercial via WhatsApp para operações reais</h1>
  <p>Plataforma SaaS com CRM, IA e billing real via API Débito (M-Pesa/eMola).</p>
  <div class="actions">
    <a class="btn" href="/register">Iniciar trial 24h</a>
    <a class="btn btn-secondary" href="/login">Acessar painel</a>
  </div>
</section>
<section class="cards">
  <article class="card"><h3>Billing integrado</h3><p>Checkout, cobranças e status com confirmação automática.</p></article>
  <article class="card"><h3>M-Pesa e eMola</h3><p>Processamento C2B via gateway Débito.</p></article>
  <article class="card"><h3>Pronto para escalar</h3><p>Arquitetura multi-tenant e desacoplada para produção.</p></article>
</section>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
