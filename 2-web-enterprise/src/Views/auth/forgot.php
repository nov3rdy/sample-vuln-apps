<section class="auth-card">
    <div class="eyebrow">Account recovery</div>
    <h1>Reset your <em>password</em></h1>
    <p class="muted">We'll send a one-time reset token to your work email.</p>
    <form method="post" action="/forgot">
        <label>
            Email
            <input type="email" name="email" required>
        </label>
        <button type="submit" class="btn-primary">Send reset link</button>
    </form>
</section>
