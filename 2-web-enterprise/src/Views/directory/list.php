<header class="page-header">
    <div>
        <div class="eyebrow">Workspace · Directory</div>
        <h1>People at <em>CompanyHub</em></h1>
    </div>
    <form method="get" action="/directory/search" class="search-form">
        <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search by name, email or department">
        <button type="submit">Search</button>
    </form>
</header>

<?php if ($q !== ''): ?>
    <!-- V3: Reflected XSS — `q` echoed verbatim, no htmlspecialchars(). -->
    <p class="muted">Results for <strong><?= $q ?></strong></p>
<?php endif; ?>

<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Role</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($employees as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['display_name']) ?></td>
                <td><?= htmlspecialchars($e['email']) ?></td>
                <td><?= htmlspecialchars($e['department'] ?? '') ?></td>
                <td><span class="badge badge-<?= htmlspecialchars($e['role']) ?>"><?= htmlspecialchars($e['role']) ?></span></td>
                <td><a href="/directory/<?= (int) $e['id'] ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
