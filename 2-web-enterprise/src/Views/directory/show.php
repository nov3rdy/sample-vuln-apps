<header class="page-header">
    <div>
        <div class="eyebrow">Directory · <?= htmlspecialchars($employee['department'] ?: 'Unassigned') ?></div>
        <h1><?= htmlspecialchars($employee['display_name']) ?></h1>
        <p class="muted">Role: <?= htmlspecialchars($employee['role']) ?></p>
    </div>
</header>

<section class="profile-card">
    <?php if (!empty($employee['avatar_path'])): ?>
        <img src="/<?= htmlspecialchars($employee['avatar_path']) ?>" alt="" class="avatar-lg">
    <?php endif; ?>
    <dl>
        <dt>Email</dt><dd><?= htmlspecialchars($employee['email']) ?></dd>
        <dt>Joined</dt><dd><?= htmlspecialchars((string) $employee['created_at']) ?></dd>
    </dl>
    <a href="/messages/new?to=<?= (int) $employee['id'] ?>" class="btn-primary">Send message</a>
</section>

<p><a href="/directory">← Back to directory</a></p>
