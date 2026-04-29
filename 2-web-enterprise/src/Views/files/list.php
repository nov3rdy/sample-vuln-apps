<header class="page-header">
    <div>
        <div class="eyebrow">Workspace · Files</div>
        <h1>Shared <em>documents</em></h1>
        <p class="muted">Files visible to everyone in the company.</p>
    </div>
</header>

<form method="post" action="/files" enctype="multipart/form-data" class="form upload-form">
    <label>
        Choose a file
        <input type="file" name="file" required>
    </label>
    <button type="submit" class="btn-primary">Upload</button>
    <p class="muted">Uploaded files are visible to everyone in the company.</p>
</form>

<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Owner</th>
            <th>Size</th>
            <th>Type</th>
            <th>Uploaded</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($files as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['filename']) ?></td>
                <td><?= htmlspecialchars($f['owner_name']) ?></td>
                <td><?= number_format((int) $f['size_bytes'] / 1024, 1) ?> KB</td>
                <td><?= htmlspecialchars($f['mime_claimed'] ?? '') ?></td>
                <td><?= htmlspecialchars((string) $f['uploaded_at']) ?></td>
                <td><a href="/files/download?id=<?= (int) $f['id'] ?>">Download</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
