<section class="auth-card">
    <div class="eyebrow">Account recovery</div>
    <h1>Choose a new <em>password</em></h1>
    <p class="muted">Use something strong &mdash; at least 12 characters.</p>
    <form method="post" action="/reset">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <label>
            Token
            <input type="text" name="token_display" value="<?= htmlspecialchars($token) ?>" readonly>
        </label>
        <label>
            New password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn-primary">Update password</button>
    </form>
</section>
