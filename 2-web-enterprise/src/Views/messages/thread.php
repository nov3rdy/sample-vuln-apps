<header class="page-header">
    <div>
        <div class="eyebrow">Messages · #<?= (int) $message['id'] ?></div>
        <h1>From <em><?= htmlspecialchars($message['sender_name']) ?></em></h1>
        <p class="muted">To <?= htmlspecialchars($message['recipient_name']) ?> · <?= htmlspecialchars((string) $message['created_at']) ?></p>
    </div>
</header>

<article class="message-body">
    <!-- V2: Stored XSS — body rendered raw. -->
    <?= $message['body'] ?>
</article>

<p><a href="/messages">← Back to inbox</a></p>
