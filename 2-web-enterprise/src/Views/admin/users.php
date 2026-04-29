<header class="page-header">
    <div>
        <div class="eyebrow">Administration · Users</div>
        <h1>User <em>management</em></h1>
        <p class="muted">Promote, demote, or audit any account in the directory.</p>
    </div>
</header>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Name</th>
            <th>Department</th>
            <th>Role</th>
            <th>password_md5</th>
            <th>Created</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= (int) $u['id'] ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['display_name']) ?></td>
                <td><?= htmlspecialchars($u['department'] ?? '') ?></td>
                <td><span class="badge badge-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                <td><code class="hash"><?= htmlspecialchars($u['password_md5']) ?></code></td>
                <td><?= htmlspecialchars((string) $u['created_at']) ?></td>
                <td>
                    <!-- V5: CSRF — role-change form has no anti-CSRF token. -->
                    <form method="post" action="/admin/users/<?= (int) $u['id'] ?>/role" class="inline">
                        <select name="role">
                            <option value="user"  <?= $u['role'] === 'user'  ? 'selected' : '' ?>>user</option>
                            <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                        </select>
                        <button type="submit" class="btn-small">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
