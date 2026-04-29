<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

class DirectoryTest extends TestCase
{
    public function testDirectoryListShowsAllUsers(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/directory');
        $this->assertResponseStatus(200, $r);
        $body = $this->bodyOf($r);
        $this->assertStringContainsString('Alice Anderson', $body);
        $this->assertStringContainsString('Bob Brown',      $body);
        $this->assertStringContainsString('Carol Carter',   $body);
    }

    public function testDirectoryShowReturnsEmployeeProfile(): void
    {
        $jar = $this->loginAs();
        $bobId = (int) self::pdo()->query("SELECT id FROM users WHERE email='bob@companyhub.local'")->fetchColumn();
        $r = $this->client($jar)->get('/directory/' . $bobId);
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Bob Brown', $this->bodyOf($r));
        $this->assertStringContainsString('bob@companyhub.local', $this->bodyOf($r));
    }

    public function testDirectorySearchFiltersByName(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/directory/search?q=Carol');
        $this->assertResponseStatus(200, $r);
        $body = $this->bodyOf($r);
        $this->assertStringContainsString('Carol Carter', $body);
        $this->assertStringNotContainsString('Bob Brown', $body);
    }

    public function testDirectoryRequiresAuth(): void
    {
        $r = $this->client()->get('/directory');
        $this->assertResponseStatus(302, $r);
    }
}
