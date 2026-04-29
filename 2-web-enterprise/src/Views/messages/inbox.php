<header class="page-header">
    <div>
        <div class="eyebrow">Workspace · Messages</div>
        <h1>Your <em>inbox</em></h1>
    </div>
    <a href="/messages/new" class="btn-primary">New message</a>
</header>

<?php if (!$messages): ?>
    <p class="muted">Inbox is empty.</p>
<?php else: ?>
    <ul class="thread-list">
        <?php foreach ($messages as $m): ?>
            <li>
                <a href="/messages/<?= (int) $m['id'] ?>">
                    <strong><?= htmlspecialchars($m['sender_name']) ?></strong>
                    <!-- V2: Stored XSS — preview echoes message body raw. -->
                    <span class="snippet"><?= mb_substr($m['body'], 0, 120) ?></span>
                    <span class="muted"><?= htmlspecialchars((string) $m['created_at']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
