<header class="page-header">
    <div>
        <div class="eyebrow">Administration · Banner</div>
        <h1>Site <em>banner</em></h1>
        <p class="muted">HTML is allowed and appears on every page.</p>
    </div>
</header>

<form method="post" action="/admin/banner" class="form">
    <label>
        Banner HTML
        <textarea name="banner_html" rows="6"><?= htmlspecialchars($banner_html) ?></textarea>
    </label>
    <button type="submit" class="btn-primary">Save banner</button>
</form>

<section class="section">
    <h2 class="section-title">Live preview</h2>
    <!-- V2: Stored XSS — banner is rendered raw across the whole site. -->
    <div class="banner"><?= $banner_html ?></div>
</section>
