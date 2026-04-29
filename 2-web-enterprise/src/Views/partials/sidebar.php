<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$active = static fn(string $prefix): string => str_starts_with($path, $prefix) ? ' is-active' : '';
$exact  = static fn(string $p): string => $path === $p ? ' is-active' : '';
?>
<aside class="sidebar">
    <a href="/dashboard" class="brand">
        <span class="brand-mark">
            <span class="brand-mark-inner">CH</span>
        </span>
        <span class="brand-text">
            <span class="brand-name">CompanyHub</span>
            <span class="brand-sub">Internal portal</span>
        </span>
    </a>

    <?php if ($currentUser): ?>
        <div class="nav-group">
            <div class="nav-group-label">Workspace</div>
            <nav class="nav-list">
                <a href="/dashboard" class="nav-item<?= $active('/dashboard') ?>"><span class="nav-glyph">◆</span><span class="nav-label">Dashboard</span></a>
                <a href="/directory" class="nav-item<?= $active('/directory') ?>"><span class="nav-glyph">◫</span><span class="nav-label">Directory</span></a>
                <a href="/notes"     class="nav-item<?= $active('/notes') ?>"    ><span class="nav-glyph">▤</span><span class="nav-label">Notes</span></a>
                <a href="/messages"  class="nav-item<?= $active('/messages') ?>" ><span class="nav-glyph">✦</span><span class="nav-label">Messages</span></a>
                <a href="/files"     class="nav-item<?= $active('/files') ?>"    ><span class="nav-glyph">▢</span><span class="nav-label">Files</span></a>
                <a href="/links"     class="nav-item<?= $active('/links') ?>"    ><span class="nav-glyph">↗</span><span class="nav-label">Team links</span></a>
                <a href="/import"    class="nav-item<?= $active('/import') ?>"   ><span class="nav-glyph">↥</span><span class="nav-label">Import</span></a>
            </nav>
        </div>

        <?php if ($isAdmin): ?>
            <div class="nav-group nav-group--restricted">
                <div class="nav-group-label">
                    Administration
                    <span class="nav-group-badge">Restricted</span>
                </div>
                <nav class="nav-list">
                    <a href="/admin"        class="nav-item<?= $exact('/admin') ?>"        ><span class="nav-glyph">⌂</span><span class="nav-label">Overview</span></a>
                    <a href="/admin/users"  class="nav-item<?= $active('/admin/users') ?>" ><span class="nav-glyph">◐</span><span class="nav-label">Users</span></a>
                    <a href="/admin/banner" class="nav-item<?= $active('/admin/banner') ?>"><span class="nav-glyph">▭</span><span class="nav-label">Site banner</span></a>
                    <a href="/admin/stats"  class="nav-item<?= $active('/admin/stats') ?>" ><span class="nav-glyph">▦</span><span class="nav-label">Database stats</span></a>
                </nav>
            </div>
        <?php endif; ?>

        <div class="sidebar-spacer"></div>

        <div class="user-card">
            <a href="/profile" class="user-card-link">
                <?php if (!empty($currentUser['avatar_path'])): ?>
                    <img class="user-avatar" src="/<?= htmlspecialchars($currentUser['avatar_path']) ?>" alt="">
                <?php else: ?>
                    <span class="user-avatar user-avatar--init">
                        <?= htmlspecialchars(strtoupper(substr($currentUser['display_name'] ?? '?', 0, 1))) ?>
                    </span>
                <?php endif; ?>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($currentUser['display_name']) ?></span>
                    <span class="user-sub">
                        <?= htmlspecialchars($currentUser['role']) ?>
                        <?php if (!empty($currentUser['department'])): ?>
                            · <?= htmlspecialchars($currentUser['department']) ?>
                        <?php endif; ?>
                    </span>
                </span>
            </a>
            <a href="/logout" class="user-logout" title="Sign out" aria-label="Sign out">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </a>
        </div>
    <?php endif; ?>
</aside>
