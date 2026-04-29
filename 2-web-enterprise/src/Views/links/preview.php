<header class="page-header">
    <div>
        <div class="eyebrow">Team Links · Preview</div>
        <h1><em><?= htmlspecialchars($title) ?></em></h1>
        <p class="muted"><?= htmlspecialchars($url) ?> · HTTP <?= (int) $status ?></p>
    </div>
</header>

<article class="preview-card">
    <h2><?= htmlspecialchars($title) ?></h2>
    <pre class="preview-excerpt"><?= htmlspecialchars($excerpt) ?></pre>
</article>

<p><a href="/links">← Back to links</a></p>
