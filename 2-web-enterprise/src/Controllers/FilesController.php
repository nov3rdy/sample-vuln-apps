<?php
declare(strict_types=1);

namespace CompanyHub\Controllers;

use CompanyHub\Auth;
use CompanyHub\Controller;
use CompanyHub\Db;

class FilesController extends Controller
{
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads';

    public function index(): void
    {
        Auth::requireUser();
        $user = Auth::currentUser();
        $files = Db::all(
            'SELECT f.*, u.display_name AS owner_name FROM files f
             JOIN users u ON u.id = f.user_id
             ORDER BY f.uploaded_at DESC'
        );
        $this->view('files/list', ['files' => $files, 'user' => $user]);
    }

    public function upload(): void
    {
        Auth::requireUser();
        $user = Auth::currentUser();

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'No file uploaded.');
            $this->redirect('/files');
        }

        $upload   = $_FILES['file'];
        $original = (string) $upload['name'];
        $tmp      = (string) $upload['tmp_name'];

        // V9: Insecure File Upload — extension not validated, mime is taken from the
        // client (`$upload['type']`), and the file is stored under the webroot. A user
        // can upload `shell.php` and request `/uploads/shell.php` to execute it.
        $stored = self::UPLOAD_DIR . '/' . $original;
        @mkdir(self::UPLOAD_DIR, 0775, true);
        move_uploaded_file($tmp, $stored);

        Db::exec(
            'INSERT INTO files (user_id, filename, stored_path, mime_claimed, size_bytes) VALUES (?, ?, ?, ?, ?)',
            [
                $user['id'],
                $original,
                'uploads/' . $original,
                (string) ($upload['type'] ?? 'application/octet-stream'),
                (int) ($upload['size'] ?? 0),
            ]
        );

        $this->flash('success', 'Uploaded: ' . $original);
        $this->redirect('/files');
    }

    public function download(): void
    {
        Auth::requireUser();
        $idParam   = $this->param('id');
        $fileParam = $this->param('file');

        // V6: IDOR — `?id=` lookup with no ownership check; any user can download any file.
        if ($idParam !== null) {
            $file = Db::one('SELECT * FROM files WHERE id = ?', [(int) $idParam]);
            if (!$file) {
                http_response_code(404);
                echo 'Not found';
                return;
            }
            $path = self::UPLOAD_DIR . '/' . basename($file['filename']);
            $this->stream($path, $file['filename']);
            return;
        }

        // V8: Path Traversal — `?file=` is concatenated under uploads/ without normalizing.
        // Try /files/download?file=../../etc/passwd
        if ($fileParam !== null) {
            $path = self::UPLOAD_DIR . '/' . $fileParam;
            $this->stream($path, basename($fileParam));
            return;
        }

        http_response_code(400);
        echo 'Provide ?id= or ?file=';
    }

    private function stream(string $path, string $name): void
    {
        if (!is_file($path)) {
            http_response_code(404);
            echo 'File missing on disk';
            return;
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
}
