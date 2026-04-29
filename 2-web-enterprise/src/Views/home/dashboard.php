<header class="page-header">
    <div>
        <div class="eyebrow">Workspace · Dashboard</div>
        <h1>Welcome back, <em><?= htmlspecialchars(explode(' ', $user['display_name'])[0]) ?></em></h1>
        <p class="muted"><?= htmlspecialchars($user['department'] ?? '') ?> · signed in as <?= htmlspecialchars($user['email']) ?></p>
    </div>
</header>

<section class="cards">
    <a class="card" href="/notes">
        <div class="card-label">Your notes</div>
        <div class="card-stat"><?= (int) $noteCount ?></div>
        <div class="card-help">Drafts and shared writeups</div>
    </a>
    <a class="card" href="/messages">
        <div class="card-label">Inbox</div>
        <div class="card-stat"><?= (int) $messageCount ?></div>
        <div class="card-help">Direct messages awaiting</div>
    </a>
    <a class="card" href="/files">
        <div class="card-label">Your files</div>
        <div class="card-stat"><?= (int) $fileCount ?></div>
        <div class="card-help">Documents you have uploaded</div>
    </a>
    <a class="card" href="/links">
        <div class="card-label">Team links</div>
        <div class="card-stat"><?= (int) $linkCount ?></div>
        <div class="card-help">Shared bookmarks across the company</div>
    </a>
</section>

<section class="quick-actions">
    <h2>Quick actions</h2>
    <ul>
        <li><a href="/notes/new">Write a new note</a></li>
        <li><a href="/messages/new">Send a message to a colleague</a></li>
        <li><a href="/files">Upload a shared document</a></li>
        <li><a href="/links">Save a team link</a></li>
        <li><a href="/import">Bulk-import contacts (XML)</a></li>
    </ul>
</section>
