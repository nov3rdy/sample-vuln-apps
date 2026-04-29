<header class="page-header">
    <div>
        <div class="eyebrow">Messages · Compose</div>
        <h1>Send a <em>message</em></h1>
    </div>
</header>

<form method="post" action="/messages" class="form">
    <label>
        To
        <select name="recipient_id" required>
            <option value="">Select a colleague</option>
            <?php $preselect = (int) ($_GET['to'] ?? 0); ?>
            <?php foreach ($recipients as $r): ?>
                <option value="<?= (int) $r['id'] ?>" <?= $preselect === (int) $r['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['display_name']) ?> &lt;<?= htmlspecialchars($r['email']) ?>&gt;
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Body
        <textarea name="body" rows="8" required></textarea>
    </label>
    <button type="submit" class="btn-primary">Send</button>
</form>
