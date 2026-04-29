<header class="page-header">
    <div>
        <div class="eyebrow">Workspace · Team Links</div>
        <h1>Shared <em>bookmarks</em></h1>
    </div>
</header>

<form method="post" action="/links" class="form inline-form">
    <input type="url" name="url" placeholder="https://…" required>
    <input type="text" name="title" placeholder="Title (optional)">
    <button type="submit" class="btn-primary">Add link</button>
</form>

<ul class="link-list">
    <?php foreach ($links as $l): ?>
        <li>
            <a href="<?= htmlspecialchars($l['url']) ?>" target="_blank" rel="noopener">
                <?= htmlspecialchars($l['title'] ?: $l['url']) ?>
            </a>
            <span class="muted">added by <?= htmlspecialchars($l['owner_name']) ?></span>
            <a class="btn-link" href="/links/preview?url=<?= urlencode($l['url']) ?>">Preview</a>
        </li>
    <?php endforeach; ?>
</ul>
