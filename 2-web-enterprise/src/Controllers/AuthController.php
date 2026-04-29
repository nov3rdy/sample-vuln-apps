<?php
declare(strict_types=1);

namespace CompanyHub\Controllers;

use CompanyHub\Auth;
use CompanyHub\Controller;
use CompanyHub\Db;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $next = $this->param('next', '/dashboard');
        $this->viewAuth('auth/login', ['next' => $next]);
    }

    public function doLogin(): void
    {
        $email    = $this->input('email', '');
        $password = $this->input('password', '');
        $next     = $this->input('next', '/dashboard');

        // V13: Cryptographic Failures — passwords stored as raw MD5
        $hash = md5((string) $password);

        // V1: SQL Injection — raw string concatenation, no binding.
        // Payload: email=' OR 1=1-- bypasses auth (logs in as the first user).
        $sql = "SELECT id, email, role FROM users
                WHERE email = '" . $email . "' AND password_md5 = '" . $hash . "'
                LIMIT 1";
        $rows = Db::rawAll($sql);

        // V14: Identification & Auth Failures — no rate limit, no lockout, no failed-login backoff.
        // V18: Logging Failures — failed login attempts are not logged or audited.
        if (!$rows) {
            $this->flash('error', 'Invalid email or password.');
            $this->redirect('/login?next=' . urlencode((string) $next));
        }

        Auth::login($rows[0]);

        // V11: Open Redirect — `next` is taken verbatim, no allowlist/host check.
        $this->redirect((string) $next);
    }

    public function showRegister(): void
    {
        $this->viewAuth('auth/register');
    }

    public function doRegister(): void
    {
        $email = trim((string) $this->input('email', ''));
        $name  = trim((string) $this->input('display_name', ''));
        $dept  = trim((string) $this->input('department', ''));
        $pw    = (string) $this->input('password', '');

        if ($email === '' || $pw === '') {
            $this->flash('error', 'Email and password are required.');
            $this->redirect('/register');
        }

        // V13: MD5 password hashing
        $hash = md5($pw);
        try {
            Db::exec(
                'INSERT INTO users (email, password_md5, display_name, department, role) VALUES (?, ?, ?, ?, ?)',
                [$email, $hash, $name ?: $email, $dept, 'user']
            );
        } catch (\PDOException $e) {
            $this->flash('error', 'Registration failed: ' . $e->getMessage());
            $this->redirect('/register');
        }

        $user = Db::one('SELECT id, email, role FROM users WHERE email = ?', [$email]);
        Auth::login($user);
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }

    public function showForgot(): void
    {
        $this->viewAuth('auth/forgot');
    }

    public function doForgot(): void
    {
        $email = (string) $this->input('email', '');
        $user  = Db::one('SELECT id FROM users WHERE email = ?', [$email]);
        if ($user) {
            // V14: short, weak token (6 alphanumeric chars ≈ 36 bits) — brute-forceable
            $token = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
            Db::exec(
                'INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))',
                [$user['id'], $token]
            );
            // In a real app we'd email this. Here we surface it so the demo is self-contained.
            $this->flash('info', "Reset link generated. Token: {$token} — visit /reset?token={$token}");
        } else {
            $this->flash('info', 'If that email exists, a reset link was sent.');
        }
        $this->redirect('/forgot');
    }

    public function showReset(): void
    {
        $token = (string) $this->param('token', '');
        $this->viewAuth('auth/reset', ['token' => $token]);
    }

    public function doReset(): void
    {
        $token = (string) $this->input('token', '');
        $pw    = (string) $this->input('password', '');

        $row = Db::one(
            'SELECT user_id FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1',
            [$token]
        );
        if (!$row) {
            $this->flash('error', 'Invalid or expired token.');
            $this->redirect('/forgot');
        }
        Db::exec('UPDATE users SET password_md5 = ? WHERE id = ?', [md5($pw), $row['user_id']]);
        Db::exec('UPDATE password_resets SET used_at = NOW() WHERE token = ?', [$token]);
        $this->flash('success', 'Password updated. Please log in.');
        $this->redirect('/login');
    }
}
