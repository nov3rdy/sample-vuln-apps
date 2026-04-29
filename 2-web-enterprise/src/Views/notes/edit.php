<header class="page-header">
    <div>
        <div class="eyebrow">Notes · <?= $note ? 'Edit' : 'Compose' ?></div>
        <h1><?= $note ? 'Edit <em>note</em>' : '<em>Write</em> a note' ?></h1>
    </div>
</header>

<form method="post" action="<?= $note ? '/notes/' . (int) $note['id'] : '/notes' ?>" class="form">
    <label>
        Title
        <input type="text" name="title" value="<?= htmlspecialchars($note['title'] ?? '') ?>" required>
    </label>
    <label>
        Body
        <textarea name="body" rows="12"><?= htmlspecialchars($note['body'] ?? '') ?></textarea>
    </label>
    <label class="inline">
        <input type="checkbox" name="is_public" value="1" <?= !empty($note['is_public']) ? 'checked' : '' ?>>
        Visible to everyone in the company
    </label>
    <button type="submit" class="btn-primary"><?= $note ? 'Save changes' : 'Create note' ?></button>
</form>
