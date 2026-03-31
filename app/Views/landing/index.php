<?php ob_start(); ?>
<section class="hero">
  <h1>Automação comercial via WhatsApp para operações reais</h1>
  <p>Base SaaS multi-tenant pronta para evolução com CRM, IA, campanhas, billing e integrações API.</p>
  <div class="actions">
    <a class="btn" href="/register">Iniciar trial 24h</a>
    <a class="btn btn-secondary" href="/login">Acessar painel</a>
  </div>
</section>
<section class="cards">
  <article class="card"><h3>MVC profissional</h3><p>Separação limpa entre Controllers, Services, Repositories e Integrations.</p></article>
  <article class="card"><h3>Pronto para shared hosting/VPS</h3><p>Document root em <code>public/</code> e bootstrap leve.</p></article>
  <article class="card"><h3>Escalável</h3><p>Camadas preparadas para WhatsApp, IA, pagamentos e jobs assíncronos.</p></article>
</section>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
