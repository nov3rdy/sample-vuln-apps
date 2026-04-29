<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

use GuzzleHttp\RequestOptions;

class LinksTest extends TestCase
{
    public function testLinksIndexShowsExistingLinks(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/links');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Example', $this->bodyOf($r));
    }

    public function testStoreLinkInsertsRow(): void
    {
        $jar = $this->loginAs();
        $url = 'https://demo-' . bin2hex(random_bytes(3)) . '.example';
        $r = $this->client($jar)->post('/links', [
            RequestOptions::FORM_PARAMS => ['url' => $url, 'title' => 'Demo'],
        ]);
        $this->assertResponseStatus(302, $r);
        $row = self::pdo()->prepare('SELECT id FROM links WHERE url = ?');
        $row->execute([$url]);
        $this->assertNotFalse($row->fetchColumn());
    }

    public function testPreviewFetchesUrlAndReturnsExcerpt(): void
    {
        $jar = $this->loginAs();
        // Hit the in-cluster nginx itself for a deterministic, reachable preview target
        $r = $this->client($jar)->get('/links/preview?url=' . urlencode('http://web/login'));
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Sign in', $this->bodyOf($r));
    }
}
