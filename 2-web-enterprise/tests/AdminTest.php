<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\RequestOptions;

class AdminTest extends TestCase
{
    public function testAdminPageAccessibleWhenRoleCookieIsAdmin(): void
    {
        $jar = $this->loginAsAdmin();
        $r = $this->client($jar)->get('/admin');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Administration · Overview', $this->bodyOf($r));
    }

    public function testAdminPageForbiddenWhenRoleCookieIsUser(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/admin');
        $this->assertResponseStatus(403, $r);
    }

    public function testAdminUsersListIncludesAllAccounts(): void
    {
        $jar = $this->loginAsAdmin();
        $r = $this->client($jar)->get('/admin/users');
        $this->assertResponseStatus(200, $r);
        $body = $this->bodyOf($r);
        foreach (['alice@companyhub.local', 'bob@companyhub.local', 'carol@companyhub.local'] as $email) {
            $this->assertStringContainsString($email, $body);
        }
    }

    public function testChangeRoleUpdatesUser(): void
    {
        $jar = $this->loginAsAdmin();
        $bobId = (int) self::pdo()->query("SELECT id FROM users WHERE email='bob@companyhub.local'")->fetchColumn();

        $r = $this->client($jar)->post('/admin/users/' . $bobId . '/role', [
            RequestOptions::FORM_PARAMS => ['role' => 'admin'],
        ]);
        $this->assertResponseStatus(302, $r);
        $role = (string) self::pdo()->query("SELECT role FROM users WHERE id={$bobId}")->fetchColumn();
        $this->assertSame('admin', $role);

        // restore
        self::pdo()->exec("UPDATE users SET role='user' WHERE id={$bobId}");
    }

    public function testStatsPageRendersTableCounts(): void
    {
        $jar = $this->loginAsAdmin();
        $r = $this->client($jar)->get('/admin/stats');
        $this->assertResponseStatus(200, $r);
        $body = $this->bodyOf($r);
        $this->assertStringContainsString('users', $body);
        $this->assertStringContainsString('notes', $body);
    }

    public function testBannerPageRendersAndUpdates(): void
    {
        $jar = $this->loginAsAdmin();
        $r = $this->client($jar)->get('/admin/banner');
        $this->assertResponseStatus(200, $r);

        $r2 = $this->client($jar)->post('/admin/banner', [
            RequestOptions::FORM_PARAMS => ['banner_html' => '<em>Updated</em>'],
        ]);
        $this->assertResponseStatus(302, $r2);
        $current = (string) self::pdo()->query('SELECT banner_html FROM site_settings ORDER BY id ASC LIMIT 1')->fetchColumn();
        $this->assertSame('<em>Updated</em>', $current);

        self::pdo()->exec("UPDATE site_settings SET banner_html='Welcome to <strong>CompanyHub</strong>.' WHERE id=1");
    }
}
