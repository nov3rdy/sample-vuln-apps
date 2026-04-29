<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

use GuzzleHttp\RequestOptions;

class ProfileTest extends TestCase
{
    public function testProfilePageShowsUser(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/profile');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('alice@companyhub.local', $this->bodyOf($r));
    }

    public function testAvatarUploadStoresFile(): void
    {
        $jar = $this->loginAs();
        $name = 'avatar-' . bin2hex(random_bytes(3)) . '.png';
        $r = $this->client($jar)->post('/profile/avatar', [
            RequestOptions::MULTIPART => [[
                'name'     => 'avatar',
                'filename' => $name,
                'contents' => "\x89PNG\r\n\x1a\n" . str_repeat('x', 32),
                'headers'  => ['Content-Type' => 'image/png'],
            ]],
        ]);
        $this->assertResponseStatus(302, $r);
        $aliceId = (int) self::pdo()->query("SELECT id FROM users WHERE email='alice@companyhub.local'")->fetchColumn();
        $row = self::pdo()->query("SELECT avatar_path FROM users WHERE id={$aliceId}")->fetchColumn();
        $this->assertSame('uploads/avatars/' . $name, $row);

        @unlink('/var/www/html/public/uploads/avatars/' . $name);
    }

    public function testSavePreferencesSetsCookie(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->post('/profile/preferences', [
            RequestOptions::FORM_PARAMS => ['theme' => 'dark', 'compact_mode' => '1'],
        ]);
        $this->assertResponseStatus(302, $r);
        $cookie = $jar->getCookieByName('preferences');
        $this->assertNotNull($cookie);
        $decoded = @unserialize(base64_decode(rawurldecode($cookie->getValue())));
        $this->assertIsArray($decoded);
        $this->assertSame('dark', $decoded['theme']);
        $this->assertTrue($decoded['compact_mode']);
    }
}
