<?php
declare(strict_types=1);

namespace CompanyHub\Controllers;

use CompanyHub\Auth;
use CompanyHub\Controller;
use CompanyHub\Db;

class AdminController extends Controller
{
    public function index(): void
    {
        // V7: Vertical Privilege Escalation — requireAdmin() trusts the client `role` cookie.
        // Set `Cookie: role=admin` in any request to access /admin/* without being an admin user.
        // V18: Logging Failures — admin access is never logged or audited.
        Auth::requireAdmin();
        $this->view('admin/index', []);
    }

    public function users(): void
    {
        Auth::requireAdmin();
        $users = Db::all('SELECT id, email, display_name, department, role, password_md5, created_at FROM users ORDER BY id');
        $this->view('admin/users', ['users' => $users]);
    }

    public function changeRole(array $params): void
    {
        // V5: CSRF + V7: trusted role cookie + V18: no audit log on privilege change.
        Auth::requireAdmin();
        $id   = (int) ($params['id'] ?? 0);
        $role = (string) $this->input('role', 'user');
        if (!in_array($role, ['user', 'admin'], true)) {
            $this->flash('error', 'Invalid role.');
            $this->redirect('/admin/users');
        }
        Db::exec('UPDATE users SET role = ? WHERE id = ?', [$role, $id]);
        $this->flash('success', 'Role updated.');
        $this->redirect('/admin/users');
    }

    public function showBanner(): void
    {
        Auth::requireAdmin();
        $row = Db::one('SELECT banner_html FROM site_settings ORDER BY id ASC LIMIT 1');
        $this->view('admin/banner', ['banner_html' => $row['banner_html'] ?? '']);
    }

    public function saveBanner(): void
    {
        // V5: CSRF — no token. V2: stored XSS — banner_html is rendered raw on every page.
        Auth::requireAdmin();
        $html = (string) $this->input('banner_html', '');
        Db::exec('UPDATE site_settings SET banner_html = ? WHERE id = 1', [$html]);
        $this->flash('success', 'Banner updated.');
        $this->redirect('/admin/banner');
    }

    public function stats(): void
    {
        Auth::requireAdmin();
        $tables = ['users', 'notes', 'messages', 'files', 'links', 'password_resets'];
        $counts = [];
        foreach ($tables as $t) {
            $counts[$t] = (int) (Db::one("SELECT COUNT(*) AS c FROM {$t}")['c'] ?? 0);
        }
        $version = (string) (Db::one('SELECT VERSION() AS v')['v'] ?? 'unknown');
        $this->view('admin/stats', ['counts' => $counts, 'version' => $version]);
    }
}
