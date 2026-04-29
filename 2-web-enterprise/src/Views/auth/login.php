<section class="auth-card">
    <div class="eyebrow">Sign in</div>
    <h1>Welcome <em>back</em></h1>
    <p class="muted">Continue with your work email and password.</p>
    <form method="post" action="/login">
        <!-- V5: CSRF — no token field. -->
        <!-- V11: Open Redirect — `next` echoed back into the form, then trusted on success. -->
        <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">

        <label>
            Email
            <input type="text" name="email" autocomplete="username" required>
        </label>

        <label>
            Password
            <input type="password" name="password" autocomplete="current-password" required>
        </label>

        <button type="submit" class="btn-primary">Sign in</button>
    </form>
    <p class="auth-meta">
        <a href="/forgot">Forgot password?</a> · <a href="/register">Create account</a>
    </p>
</section>
