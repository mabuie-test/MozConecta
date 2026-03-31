<?php ob_start(); ?>
<h1>Painel SaaS (Fase 1)</h1>
<div class="cards">
  <article class="card"><h3>Arquitetura</h3><p>Bootstrap, router, middlewares e logger ativos.</p></article>
  <article class="card"><h3>Próxima fase</h3><p>Entram regras de negócio, billing e módulos operacionais.</p></article>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
