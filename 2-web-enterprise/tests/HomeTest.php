<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

class HomeTest extends TestCase
{
    public function testRootRedirectsToLoginWhenAnonymous(): void
    {
        $r = $this->client()->get('/');
        $this->assertResponseStatus(302, $r);
        $this->assertSame('/login', $this->locationOf($r));
    }

    public function testRootRedirectsToDashboardWhenLoggedIn(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/');
        $this->assertResponseStatus(302, $r);
        $this->assertSame('/dashboard', $this->locationOf($r));
    }

    public function testDashboardRendersUserName(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/dashboard');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Alice Anderson', $this->bodyOf($r));
        $this->assertStringContainsString('Welcome back', $this->bodyOf($r));
    }

    public function testDashboardRequiresAuth(): void
    {
        $r = $this->client()->get('/dashboard');
        $this->assertResponseStatus(302, $r);
        $this->assertSame('/login', $this->locationOf($r));
    }
}
