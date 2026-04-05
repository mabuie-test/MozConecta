<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'MozConecta') ?></title>
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
        <a href="/billing/plans">Planos</a>
        <a href="/billing/history">Financeiro</a>
        <a href="/profile">Perfil</a>
        <form method="post" action="/logout" class="inline-form"><button type="submit">Sair</button></form>
      <?php else: ?>
        <a href="/login">Login</a>
        <a href="/register">Registo</a>
      <?php endif; ?>
    </div>
  </nav>
  <main class="container"><?= $content ?? '' ?></main>
</body>
</html>
