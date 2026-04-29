<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CompanyHub · Sign in</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="auth-shell theme-<?= htmlspecialchars($_SESSION['preferences']['theme'] ?? 'light') ?>">
<div class="auth-shell-inner">
    <a href="/login" class="auth-brand">
        <span class="brand-mark">
            <span class="brand-mark-inner">CH</span>
        </span>
        <span class="brand-text">
            <span class="brand-name">CompanyHub</span>
            <span class="brand-sub">Internal portal</span>
        </span>
    </a>

    <?php require dirname(__DIR__) . '/Views/partials/flash.php'; ?>

    <?= $content ?>

    <p class="auth-footer">
        Internal use only · <a href="/debug.php">Server diagnostics</a>
    </p>
</div>
</body>
</html>
