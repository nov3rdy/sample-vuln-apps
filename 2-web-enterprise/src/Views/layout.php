<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CompanyHub · Internal File System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="theme-<?= htmlspecialchars($_SESSION['preferences']['theme'] ?? 'light') ?><?= !empty($_SESSION['preferences']['compact_mode']) ? ' compact' : '' ?>">
<div class="app">
    <?php require dirname(__DIR__) . '/Views/partials/sidebar.php'; ?>

    <main class="main">
        <?php if (!empty($banner['banner_html'])): ?>
            <!-- V2: Stored XSS — banner_html is rendered raw on every page. -->
            <div class="banner"><?= $banner['banner_html'] ?></div>
        <?php endif; ?>

        <div id="notification" class="notification" hidden></div>

        <?php require dirname(__DIR__) . '/Views/partials/flash.php'; ?>

        <div class="page">
            <?= $content ?>
        </div>

        <footer class="page-footer">
            CompanyHub · Internal use only · <a href="/debug.php">Server diagnostics</a>
        </footer>
    </main>
</div>

<script src="/assets/js/notification.js"></script>
</body>
</html>
