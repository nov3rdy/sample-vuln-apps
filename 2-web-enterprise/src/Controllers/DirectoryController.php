<?php
declare(strict_types=1);

namespace CompanyHub\Controllers;

use CompanyHub\Auth;
use CompanyHub\Controller;
use CompanyHub\Db;

class DirectoryController extends Controller
{
    public function index(): void
    {
        Auth::requireUser();
        $employees = Db::all('SELECT id, email, display_name, department, role, avatar_path FROM users ORDER BY display_name');
        $this->view('directory/list', ['employees' => $employees, 'q' => '']);
    }

    public function search(): void
    {
        Auth::requireUser();
        $q = (string) $this->param('q', '');

        // V3: Reflected XSS — `q` is rendered into the page heading by the template
        // without escaping. Try /directory/search?q=<script>alert(1)</script>
        $like = '%' . $q . '%';
        $employees = Db::all(
            'SELECT id, email, display_name, department, role, avatar_path FROM users
             WHERE display_name LIKE ? OR email LIKE ? OR department LIKE ?
             ORDER BY display_name',
            [$like, $like, $like]
        );
        $this->view('directory/list', ['employees' => $employees, 'q' => $q]);
    }

    public function show(array $params): void
    {
        Auth::requireUser();
        $id = (int) ($params['id'] ?? 0);
        $employee = Db::one('SELECT id, email, display_name, department, role, avatar_path, created_at FROM users WHERE id = ?', [$id]);
        if (!$employee) {
            http_response_code(404);
            echo '<h1>Employee not found</h1>';
            return;
        }
        $this->view('directory/show', ['employee' => $employee]);
    }
}
