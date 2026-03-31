<?php ob_start(); ?>
<header class="hero">
  <h1>Central completa de atendimento, vendas, CRM e automação via WhatsApp</h1>
  <p>SaaS multi-tenant com IA, funil comercial, campanhas e billing local M-Pesa/eMola.</p>
  <a class="btn" href="/register">Iniciar trial grátis de 24 horas</a>
</header>
<section><h2>Vantagens</h2><ul><li>Inbox multiatendente</li><li>CRM + Funil visual</li><li>Automações híbridas com IA</li></ul></section>
<section><h2>Planos</h2><p>Do Trial 24h ao Enterprise, com escalabilidade por consumo.</p></section>
<section><h2>Pagamento seguro via M-Pesa e eMola</h2><p>Conciliação por webhook com idempotência e auditoria.</p></section>
<section><h2>FAQ</h2><p>Trial expira em 24h, sem bloqueio de login, mas com recursos restritos.</p></section>
<?php $content = ob_get_clean(); $title='MozConecta - SaaS WhatsApp'; require __DIR__ . '/../layouts/main.php'; ?>
