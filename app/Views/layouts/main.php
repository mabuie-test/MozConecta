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
    <div class="links"><a href="/">Home</a><a href="/login">Login</a><a href="/dashboard">Painel</a></div>
  </nav>
  <main class="container"><?= $content ?? '' ?></main>
</body>
</html>
