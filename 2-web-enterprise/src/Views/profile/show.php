<header class="page-header">
    <div>
        <div class="eyebrow">Account · Profile</div>
        <h1>Your <em>profile</em></h1>
    </div>
</header>

<section class="profile-card">
    <?php if (!empty($user['avatar_path'])): ?>
        <img src="/<?= htmlspecialchars($user['avatar_path']) ?>" alt="" class="avatar-lg">
    <?php endif; ?>
    <dl>
        <dt>Name</dt><dd><?= htmlspecialchars($user['display_name']) ?></dd>
        <dt>Email</dt><dd><?= htmlspecialchars($user['email']) ?></dd>
        <dt>Department</dt><dd><?= htmlspecialchars($user['department'] ?? '') ?></dd>
        <dt>Role</dt><dd><?= htmlspecialchars($user['role']) ?></dd>
    </dl>
</section>

<section class="section">
    <h2 class="section-title">Avatar</h2>
    <form method="post" action="/profile/avatar" enctype="multipart/form-data" class="form">
        <input type="file" name="avatar" required>
        <button type="submit" class="btn-primary">Upload avatar</button>
    </form>
</section>

<section class="section">
    <h2 class="section-title">Preferences</h2>
    <form method="post" action="/profile/preferences" class="form">
        <label>
            Theme
            <select name="theme">
                <option value="light"  <?= ($preferences['theme'] ?? 'light') === 'light'  ? 'selected' : '' ?>>Light</option>
                <option value="dark"   <?= ($preferences['theme'] ?? '') === 'dark'   ? 'selected' : '' ?>>Dark</option>
                <option value="sepia"  <?= ($preferences['theme'] ?? '') === 'sepia'  ? 'selected' : '' ?>>Sepia</option>
            </select>
        </label>
        <label class="inline">
            <input type="checkbox" name="compact_mode" value="1" <?= !empty($preferences['compact_mode']) ? 'checked' : '' ?>>
            Compact layout
        </label>
        <button type="submit" class="btn-primary">Save</button>
    </form>
</section>
