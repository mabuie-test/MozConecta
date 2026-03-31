<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'MozConecta') ?></title>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<?= $content ?? '' ?>
</body>
</html>
