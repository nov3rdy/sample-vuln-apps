<section class="auth-card">
    <div class="eyebrow">Create account</div>
    <h1>Join <em>CompanyHub</em></h1>
    <p class="muted">A few details and we'll have you set up.</p>
    <form method="post" action="/register">
        <label>
            Work email
            <input type="email" name="email" required>
        </label>
        <label>
            Display name
            <input type="text" name="display_name">
        </label>
        <label>
            Department
            <input type="text" name="department">
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn-primary">Create account</button>
    </form>
    <p class="auth-meta">Already have an account? <a href="/login">Sign in</a></p>
</section>
