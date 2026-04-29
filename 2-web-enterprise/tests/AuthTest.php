<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;

class AuthTest extends TestCase
{
    public function testLoginPageRenders(): void
    {
        $r = $this->client()->get('/login');
        $this->assertResponseStatus(200, $r);
        $body = $this->bodyOf($r);
        $this->assertStringContainsString('Sign in', $body);
        $this->assertStringContainsString('Welcome', $body);
    }

    public function testLoginWithValidCredentialsRedirectsToDashboard(): void
    {
        $jar = new CookieJar();
        $r = $this->client($jar)->post('/login', [
            RequestOptions::FORM_PARAMS => ['email' => 'alice@companyhub.local', 'password' => 'password1', 'next' => '/dashboard'],
        ]);
        $this->assertResponseStatus(302, $r);
        $this->assertSame('/dashboard', $this->locationOf($r));
    }

    public function testLoginWithBadCredentialsRedirectsBackToLogin(): void
    {
        $r = $this->client()->post('/login', [
            RequestOptions::FORM_PARAMS => ['email' => 'alice@companyhub.local', 'password' => 'wrong', 'next' => '/dashboard'],
        ]);
        $this->assertResponseStatus(302, $r);
        $this->assertStringStartsWith('/login', $this->locationOf($r));
    }

    public function testRegisterCreatesAccountAndLogsIn(): void
    {
        $email = 'fresh+' . bin2hex(random_bytes(3)) . '@companyhub.local';
        $jar = new CookieJar();
        $r = $this->client($jar)->post('/register', [
            RequestOptions::FORM_PARAMS => [
                'email'        => $email,
                'display_name' => 'Fresh Tester',
                'department'   => 'QA',
                'password'     => 'pw',
            ],
        ]);
        $this->assertResponseStatus(302, $r);
        $this->assertSame('/dashboard', $this->locationOf($r));

        $row = self::pdo()->prepare('SELECT id FROM users WHERE email = ?');
        $row->execute([$email]);
        $this->assertNotFalse($row->fetchColumn());
    }

    public function testLogoutClearsSession(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/logout');
        $this->assertResponseStatus(302, $r);
        $this->assertSame('/login', $this->locationOf($r));

        $r2 = $this->client($jar)->get('/dashboard');
        $this->assertResponseStatus(302, $r2);
    }

    public function testForgotIssuesToken(): void
    {
        $r = $this->client()->post('/forgot', [
            RequestOptions::FORM_PARAMS => ['email' => 'alice@companyhub.local'],
        ]);
        $this->assertResponseStatus(302, $r);
        $count = (int) self::pdo()->query("SELECT COUNT(*) FROM password_resets pr JOIN users u ON u.id=pr.user_id WHERE u.email='alice@companyhub.local'")->fetchColumn();
        $this->assertGreaterThan(0, $count);
    }

    public function testResetWithValidTokenChangesPassword(): void
    {
        $aliceId = (int) self::pdo()->query("SELECT id FROM users WHERE email='alice@companyhub.local'")->fetchColumn();
        self::pdo()->exec("INSERT INTO password_resets (user_id, token, expires_at) VALUES ($aliceId, 'abc123', DATE_ADD(NOW(), INTERVAL 1 HOUR))");

        $r = $this->client()->post('/reset', [
            RequestOptions::FORM_PARAMS => ['token' => 'abc123', 'password' => 'newpass'],
        ]);
        $this->assertResponseStatus(302, $r);
        $hash = (string) self::pdo()->query("SELECT password_md5 FROM users WHERE id={$aliceId}")->fetchColumn();
        $this->assertSame(md5('newpass'), $hash);

        // restore
        self::pdo()->exec("UPDATE users SET password_md5='" . md5('password1') . "' WHERE id={$aliceId}");
    }
}
