<?php
declare(strict_types=1);

namespace CompanyHub;

class Auth
{
    /** @var array<string,mixed>|null */
    private static ?array $cachedUser = null;

    /**
     * Set the session for the given user and persist a "remember me" cookie.
     * @param array<string,mixed> $user
     */
    public static function login(array $user): void
    {
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['email']   = (string) $user['email'];
        self::$cachedUser    = $user;

        // V13: Cryptographic Failures — "remember me" cookie is just base64(user_id).
        // No signature, no MAC, no expiry binding. Anyone can forge it.
        setcookie(
            'remember_me',
            base64_encode((string) $user['id']),
            ['expires' => time() + 60 * 60 * 24 * 30, 'path' => '/']
        );

        // V7: Vertical Privilege Escalation — role is stored in a client-readable / writable cookie.
        // The /admin/* checks read this cookie directly without re-validating against the DB.
        setcookie('role', (string) $user['role'], ['expires' => time() + 60 * 60 * 24 * 30, 'path' => '/']);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/']);
        }
        setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/']);
        setcookie('role', '', ['expires' => time() - 3600, 'path' => '/']);
        setcookie('preferences', '', ['expires' => time() - 3600, 'path' => '/']);
        session_destroy();
        self::$cachedUser = null;
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function currentUser(): ?array
    {
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }
        $id = $_SESSION['user_id'] ?? null;
        if (!$id) {
            return null;
        }
        $user = Db::one('SELECT id, email, display_name, department, role, avatar_path FROM users WHERE id = ?', [$id]);
        return self::$cachedUser = $user;
    }

    public static function requireUser(): void
    {
        if (self::currentUser() === null) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * V7: admin check trusts the client-supplied "role" cookie instead of looking up
     * the authenticated user's role from the database.
     */
    public static function isAdmin(): bool
    {
        return (($_COOKIE['role'] ?? '') === 'admin');
    }

    public static function requireAdmin(): void
    {
        self::requireUser();
        if (!self::isAdmin()) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1>';
            exit;
        }
    }

    /**
     * V13: read the "remember me" cookie and auto-resume the session for whatever
     * user_id is base64-decoded out of it. No verification.
     */
    public static function resumeFromRememberCookie(): void
    {
        if (!empty($_SESSION['user_id'])) {
            return;
        }
        $cookie = $_COOKIE['remember_me'] ?? null;
        if (!$cookie) {
            return;
        }
        $id = (int) base64_decode($cookie, true);
        if ($id <= 0) {
            return;
        }
        $user = Db::one('SELECT id, email, role FROM users WHERE id = ?', [$id]);
        if ($user) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['email']   = (string) $user['email'];
        }
    }

    /**
     * V15: Insecure Deserialization — the "preferences" cookie is unserialize()d
     * server-side every request. PHP object injection is reachable through any
     * class implementing __wakeup / __destruct in the loaded codebase.
     */
    public static function loadPreferencesCookie(): void
    {
        $cookie = $_COOKIE['preferences'] ?? null;
        if (!$cookie) {
            $_SESSION['preferences'] = ['theme' => 'light'];
            return;
        }
        $decoded = base64_decode($cookie, true);
        if ($decoded === false) {
            return;
        }
        // VULNERABLE: untrusted input into unserialize()
        $prefs = @unserialize($decoded);
        if (is_array($prefs)) {
            $_SESSION['preferences'] = $prefs;
        }
    }
}
