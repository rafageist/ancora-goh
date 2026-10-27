<?php /** @var callable $e */ /** @var string $content */ /** @var string $title */ ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $e($title) ?> · Divengine Timetables</title>
<link rel="stylesheet" href="/style.css">
</head>
<body>
<header class="site">
  <a class="brand" href="/">Divengine Timetables</a>
  <nav><a href="/request">Request access</a> <a href="/privacy">Privacy</a></nav>
</header>
<main>
<?= $content ?>
</main>
<footer class="site">
  <p>Free service by Divengine, built on Áncora GOH (originally 2010). Access is by individual approval only.
  <a href="/privacy">Privacy notice</a> · <a href="/terms">Terms</a></p>
</footer>
</body>
</html>
