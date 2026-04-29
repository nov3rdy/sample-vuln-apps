<header class="page-header">
    <div>
        <div class="eyebrow">Administration · Database</div>
        <h1>Database <em>stats</em></h1>
        <p class="muted">MySQL <?= htmlspecialchars($version) ?></p>
    </div>
</header>

<table class="data-table">
    <thead><tr><th>Table</th><th>Rows</th></tr></thead>
    <tbody>
        <?php foreach ($counts as $name => $c): ?>
            <tr><td><?= htmlspecialchars($name) ?></td><td><?= (int) $c ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>
