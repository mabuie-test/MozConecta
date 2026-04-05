<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= e($title ?? 'MozConecta') ?></title>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
  <nav class="topbar">
    <div class="brand">MozConecta</div>
    <div class="links">
      <a href="/">Home</a>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="/dashboard">Painel</a>
        <a href="/whatsapp/instances">WhatsApp</a>
        <a href="/inbox">Inbox</a>
        <a href="/crm/contacts">CRM</a>
        <a href="/crm/pipeline">Pipeline</a>
        <a href="/tasks">Tarefas</a>
        <a href="/flows">Fluxos</a>
        <a href="/ai/settings">IA</a>
        <a href="/campaigns">Campanhas</a>
        <a href="/internet">Internet Bot</a>
        <a href="/notifications">Notificações</a>
        <a href="/billing/plans">Planos</a>
        <a href="/billing/history">Financeiro</a>
        <a href="/profile">Perfil</a>
        <form method="post" action="/logout" class="inline-form"><?= csrf_field() ?><button type="submit">Sair</button></form>
      <?php else: ?>
        <a href="/login">Login</a>
        <a href="/register">Registo</a>
      <?php endif; ?>
    </div>
  </nav>
  <main class="container"><?= $content ?? '' ?></main>
  <script>
    (() => {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      if (!token) return;
      document.querySelectorAll('form[method="post"], form[method="POST"]').forEach((form) => {
        if (!form.querySelector('input[name="_token"]')) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = '_token';
          input.value = token;
          form.appendChild(input);
        }
      });
    })();
  </script>
</body>
</html>
