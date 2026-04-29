<?php
declare(strict_types=1);

namespace CompanyHub\Controllers;

use CompanyHub\Auth;
use CompanyHub\Controller;
use CompanyHub\Db;
use DOMDocument;

class ImportController extends Controller
{
    public function show(): void
    {
        Auth::requireUser();
        $this->view('import/show', []);
    }

    public function doImport(): void
    {
        Auth::requireUser();

        if (!isset($_FILES['xml']) || $_FILES['xml']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'No XML uploaded.');
            $this->redirect('/import');
        }

        $xml = file_get_contents($_FILES['xml']['tmp_name']);
        if ($xml === false) {
            $this->flash('error', 'Could not read upload.');
            $this->redirect('/import');
        }

        // V17: XXE — DOMDocument is loaded with LIBXML_NOENT (substitute entities)
        // and LIBXML_DTDLOAD (allow external DTDs). External entity payloads can
        // exfiltrate local files (file:///etc/passwd) into the parsed output.
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadXML($xml, LIBXML_NOENT | LIBXML_DTDLOAD);

        $imported = [];
        foreach ($doc->getElementsByTagName('contact') as $contact) {
            $name  = trim($contact->getElementsByTagName('name')->item(0)?->textContent ?? '');
            $email = trim($contact->getElementsByTagName('email')->item(0)?->textContent ?? '');
            $note  = trim($contact->getElementsByTagName('note')->item(0)?->textContent ?? '');
            if ($email === '' || $name === '') {
                continue;
            }
            try {
                Db::exec(
                    'INSERT INTO users (email, password_md5, display_name, department, role) VALUES (?, ?, ?, ?, ?)',
                    [$email, md5('imported'), $name, 'Imported', 'user']
                );
            } catch (\PDOException $e) {
                // already exists — skip
            }
            $imported[] = ['name' => $name, 'email' => $email, 'note' => $note];
        }

        // V17 (continued): the parsed-out contents of expanded entities flow back to the
        // user via the import summary view, which makes blind XXE observable.
        $this->view('import/show', ['imported' => $imported]);
    }
}
