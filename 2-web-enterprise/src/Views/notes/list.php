<header class="page-header">
    <div>
        <div class="eyebrow">Workspace · Notes</div>
        <h1>Personal &amp; <em>shared</em> notes</h1>
    </div>
    <a href="/notes/new" class="btn-primary">New note</a>
</header>

<section class="section">
    <h2 class="section-title">Your notes</h2>
    <?php if (!$myNotes): ?>
        <p class="muted">No notes yet. <a href="/notes/new">Write your first.</a></p>
    <?php else: ?>
        <ul class="note-list">
            <?php foreach ($myNotes as $n): ?>
                <li>
                    <a href="/notes/<?= (int) $n['id'] ?>"><?= htmlspecialchars($n['title']) ?></a>
                    <?php if ($n['is_public']): ?><span class="badge badge-public">public</span><?php endif; ?>
                    <span class="muted"><?= htmlspecialchars((string) $n['updated_at']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<section class="section">
    <h2 class="section-title">From the team</h2>
    <?php if (!$publicNotes): ?>
        <p class="muted">No public notes yet.</p>
    <?php else: ?>
        <ul class="note-list">
            <?php foreach ($publicNotes as $n): ?>
                <li>
                    <a href="/notes/<?= (int) $n['id'] ?>"><?= htmlspecialchars($n['title']) ?></a>
                    <span class="muted">by <?= htmlspecialchars($n['author']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
