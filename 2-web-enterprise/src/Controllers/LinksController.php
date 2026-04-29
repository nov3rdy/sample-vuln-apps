<?php
declare(strict_types=1);

namespace CompanyHub\Controllers;

use CompanyHub\Auth;
use CompanyHub\Controller;
use CompanyHub\Db;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class LinksController extends Controller
{
    public function index(): void
    {
        Auth::requireUser();
        $links = Db::all(
            'SELECT l.*, u.display_name AS owner_name FROM links l
             JOIN users u ON u.id = l.user_id
             ORDER BY l.created_at DESC'
        );
        $this->view('links/list', ['links' => $links]);
    }

    public function store(): void
    {
        Auth::requireUser();
        $user = Auth::currentUser();
        // V5: CSRF — no token on this state-changing POST.
        $url   = (string) $this->input('url', '');
        $title = (string) $this->input('title', '');
        if ($url === '') {
            $this->flash('error', 'URL required.');
            $this->redirect('/links');
        }
        Db::exec('INSERT INTO links (user_id, url, title) VALUES (?, ?, ?)', [$user['id'], $url, $title]);
        $this->flash('success', 'Link added.');
        $this->redirect('/links');
    }

    public function preview(): void
    {
        Auth::requireUser();
        $url = (string) $this->param('url', '');
        if ($url === '') {
            http_response_code(400);
            echo 'Provide ?url=';
            return;
        }

        // V10: SSRF — the server fetches whatever URL the user supplies, with no
        // scheme/host allowlist and no block on link-local / cloud-metadata addresses.
        // Try /links/preview?url=http://169.254.169.254/latest/meta-data/ on AWS,
        // or /links/preview?url=file:///etc/passwd (file:// requires allow_url_include
        // but http:// to internal hosts works out of the box).
        $client = new Client([
            RequestOptions::TIMEOUT         => 5,
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::HTTP_ERRORS     => false,
            RequestOptions::VERIFY          => false,
        ]);

        try {
            $response = $client->request('GET', $url);
            $body     = (string) $response->getBody();
            $status   = $response->getStatusCode();
            $title    = $this->extractTitle($body) ?? $url;
            $excerpt  = mb_substr(trim(strip_tags($body)), 0, 800);
        } catch (\Throwable $e) {
            $status  = 0;
            $title   = $url;
            $excerpt = 'Fetch failed: ' . $e->getMessage();
        }

        $this->view('links/preview', compact('url', 'status', 'title', 'excerpt'));
    }

    private function extractTitle(string $html): ?string
    {
        if (preg_match('#<title>(.*?)</title>#is', $html, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
