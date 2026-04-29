<header class="page-header">
    <div>
        <div class="eyebrow">Notes · #<?= (int) $note['id'] ?></div>
        <h1><?= htmlspecialchars($note['title']) ?></h1>
        <p class="muted">by <?= htmlspecialchars($note['author']) ?> · last updated <?= htmlspecialchars((string) $note['updated_at']) ?></p>
    </div>
</header>

<article class="note-body">
    <!-- V2: Stored XSS — note body rendered without htmlspecialchars(). -->
    <?= $note['body'] ?>
</article>

<div class="actions">
    <a href="/notes/<?= (int) $note['id'] ?>/edit" class="btn">Edit</a>
    <!-- V5: CSRF — delete is a regular form, no token. -->
    <form method="post" action="/notes/<?= (int) $note['id'] ?>/delete" class="inline">
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>
    <a href="/notes" class="btn-link">← Back</a>
</div>
