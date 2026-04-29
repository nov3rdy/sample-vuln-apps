<?php
declare(strict_types=1);

namespace CompanyHub\Controllers;

use CompanyHub\Auth;
use CompanyHub\Controller;
use CompanyHub\Db;

class ProfileController extends Controller
{
    private const AVATAR_DIR = __DIR__ . '/../../public/uploads/avatars';

    public function show(): void
    {
        Auth::requireUser();
        $user        = Auth::currentUser();
        $preferences = $_SESSION['preferences'] ?? ['theme' => 'light'];
        $this->view('profile/show', ['user' => $user, 'preferences' => $preferences]);
    }

    public function avatar(): void
    {
        Auth::requireUser();
        $user = Auth::currentUser();

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'No avatar uploaded.');
            $this->redirect('/profile');
        }

        $upload   = $_FILES['avatar'];
        $original = (string) $upload['name'];

        // V9: Insecure File Upload — no extension or content-type whitelist on avatars.
        // The "image" lands directly in the webroot at /uploads/avatars/{name}.
        @mkdir(self::AVATAR_DIR, 0775, true);
        $path = self::AVATAR_DIR . '/' . $original;
        move_uploaded_file($upload['tmp_name'], $path);

        Db::exec('UPDATE users SET avatar_path = ? WHERE id = ?', ['uploads/avatars/' . $original, $user['id']]);
        $this->flash('success', 'Avatar updated.');
        $this->redirect('/profile');
    }

    public function savePreferences(): void
    {
        Auth::requireUser();
        // V5: CSRF — no token on this state-changing POST.
        $theme        = (string) $this->input('theme', 'light');
        $compactMode  = $this->input('compact_mode') !== null;

        $prefs = ['theme' => $theme, 'compact_mode' => $compactMode];

        // V15: Insecure Deserialization — preferences are serialized PHP objects/arrays
        // stored in a client-controlled cookie. On every request Auth::loadPreferencesCookie()
        // calls unserialize() on the cookie value, enabling PHP object injection if any
        // class with a magic __wakeup / __destruct is reachable.
        setcookie('preferences', base64_encode(serialize($prefs)), [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => '/',
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        $_SESSION['preferences'] = $prefs;

        $this->flash('success', 'Preferences saved.');
        $this->redirect('/profile');
    }
}
