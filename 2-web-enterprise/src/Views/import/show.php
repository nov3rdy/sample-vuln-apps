<header class="page-header">
    <div>
        <div class="eyebrow">Workspace · Import</div>
        <h1>Bulk-import <em>contacts</em></h1>
        <p class="muted">Upload an XML file with <code>&lt;contact&gt;</code> entries to add directory members in one go.</p>
    </div>
</header>

<form method="post" action="/import" enctype="multipart/form-data" class="form upload-form">
    <label>
        XML file
        <input type="file" name="xml" accept=".xml,application/xml,text/xml" required>
    </label>
    <button type="submit" class="btn-primary">Import</button>
</form>

<details class="import-help">
    <summary>Expected format</summary>
<pre>&lt;?xml version="1.0"?&gt;
&lt;contacts&gt;
    &lt;contact&gt;
        &lt;name&gt;Jane Doe&lt;/name&gt;
        &lt;email&gt;jane@example.com&lt;/email&gt;
        &lt;note&gt;Onboarded 2024-04&lt;/note&gt;
    &lt;/contact&gt;
&lt;/contacts&gt;</pre>
</details>

<?php if (!empty($imported ?? [])): ?>
    <section class="section">
        <h2 class="section-title">Imported contacts</h2>
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Note</th></tr></thead>
            <tbody>
                <?php foreach ($imported as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <!-- V17 (visible side-effect): the `note` field is rendered raw,
                             which makes XXE-expanded entity contents observable to the importer. -->
                        <td><?= $c['note'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>
