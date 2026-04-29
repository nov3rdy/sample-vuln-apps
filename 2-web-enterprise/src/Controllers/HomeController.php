<?php
declare(strict_types=1);

namespace CompanyHub\Controllers;

use CompanyHub\Auth;
use CompanyHub\Controller;
use CompanyHub\Db;

class HomeController extends Controller
{
    public function redirectHome(): void
    {
        if (Auth::currentUser() === null) {
            $this->redirect('/login');
        }
        $this->redirect('/dashboard');
    }

    public function dashboard(): void
    {
        Auth::requireUser();
        $user = Auth::currentUser();
        $noteCount    = (int) (Db::one('SELECT COUNT(*) AS c FROM notes    WHERE user_id = ?', [$user['id']])['c'] ?? 0);
        $messageCount = (int) (Db::one('SELECT COUNT(*) AS c FROM messages WHERE recipient_id = ?', [$user['id']])['c'] ?? 0);
        $fileCount    = (int) (Db::one('SELECT COUNT(*) AS c FROM files    WHERE user_id = ?', [$user['id']])['c'] ?? 0);
        $linkCount    = (int) (Db::one('SELECT COUNT(*) AS c FROM links',                            [])['c'] ?? 0);
        $this->view('home/dashboard', compact('user', 'noteCount', 'messageCount', 'fileCount', 'linkCount'));
    }
}
