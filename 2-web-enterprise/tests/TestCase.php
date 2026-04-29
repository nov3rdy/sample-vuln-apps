<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static function baseUrl(): string
    {
        return getenv('BASE_URL') ?: 'http://web';
    }

    protected static function pdo(): PDO
    {
        static $pdo;
        if (!$pdo) {
            $dsn = sprintf(
                'mysql:host=%s;port=3306;dbname=%s;charset=utf8mb4',
                getenv('DB_HOST') ?: 'db',
                getenv('DB_NAME') ?: 'companyhub'
            );
            $pdo = new PDO($dsn, getenv('DB_USER') ?: 'companyhub', getenv('DB_PASS') ?: 'companyhub', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return $pdo;
    }

    protected function client(?CookieJar $jar = null): Client
    {
        return new Client([
            'base_uri' => self::baseUrl(),
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HTTP_ERRORS     => false,
            RequestOptions::TIMEOUT         => 10,
            RequestOptions::COOKIES         => $jar,
        ]);
    }

    protected function loginAs(string $email = 'alice@companyhub.local', string $password = 'password1'): CookieJar
    {
        $jar = new CookieJar();
        $client = $this->client($jar);
        $client->post('/login', [RequestOptions::FORM_PARAMS => [
            'email'    => $email,
            'password' => $password,
            'next'     => '/dashboard',
        ]]);
        return $jar;
    }

    protected function loginAsAdmin(): CookieJar
    {
        return $this->loginAs('admin@companyhub.local', 'admin123');
    }

    protected function createUniqueUser(): array
    {
        $email = 'tester+' . bin2hex(random_bytes(4)) . '@companyhub.local';
        $name  = 'Test ' . bin2hex(random_bytes(3));
        self::pdo()->prepare(
            'INSERT INTO users (email, password_md5, display_name, department, role) VALUES (?, ?, ?, ?, ?)'
        )->execute([$email, md5('test1234'), $name, 'QA', 'user']);
        $id = (int) self::pdo()->lastInsertId();
        return ['id' => $id, 'email' => $email, 'password' => 'test1234', 'name' => $name];
    }

    protected static function reseed(): void
    {
        self::pdo()->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach (['notes', 'messages', 'files', 'links', 'password_resets', 'users'] as $t) {
            self::pdo()->exec("TRUNCATE TABLE {$t}");
        }
        self::pdo()->exec('SET FOREIGN_KEY_CHECKS=1');

        $insertUser = self::pdo()->prepare(
            'INSERT INTO users (email, password_md5, display_name, department, role) VALUES (?, ?, ?, ?, ?)'
        );
        foreach ([
            ['admin@companyhub.local', 'admin123',  'Avery Admin',    'IT',          'admin'],
            ['alice@companyhub.local', 'password1', 'Alice Anderson', 'Engineering', 'user'],
            ['bob@companyhub.local',   'qwerty',    'Bob Brown',      'Sales',       'user'],
            ['carol@companyhub.local', 'sunshine',  'Carol Carter',   'HR',          'user'],
            ['dave@companyhub.local',  'letmein',   'Dave Davis',     'Engineering', 'user'],
        ] as [$email, $pw, $name, $dept, $role]) {
            $insertUser->execute([$email, md5($pw), $name, $dept, $role]);
        }

        $aliceId = (int) self::pdo()->query("SELECT id FROM users WHERE email='alice@companyhub.local'")->fetchColumn();
        $bobId   = (int) self::pdo()->query("SELECT id FROM users WHERE email='bob@companyhub.local'")->fetchColumn();
        $adminId = (int) self::pdo()->query("SELECT id FROM users WHERE email='admin@companyhub.local'")->fetchColumn();

        $insertNote = self::pdo()->prepare(
            'INSERT INTO notes (user_id, title, body, is_public) VALUES (?, ?, ?, ?)'
        );
        $insertNote->execute([$adminId, 'Server credentials', "prod-db root: hunter2", 0]);
        $insertNote->execute([$adminId, 'Company values', 'Trust. Excellence.', 1]);
        $insertNote->execute([$aliceId, 'Sprint planning', 'Ship dashboard Friday.', 0]);
        $insertNote->execute([$bobId,   'Q2 prospects', 'Acme, Initech.', 0]);

        $insertMsg = self::pdo()->prepare(
            'INSERT INTO messages (sender_id, recipient_id, body) VALUES (?, ?, ?)'
        );
        $insertMsg->execute([$aliceId, $bobId, 'Hey Bob.']);
        $insertMsg->execute([$adminId, $aliceId, 'Heads up: maintenance.']);
        $insertMsg->execute([$bobId, $aliceId, 'Confidential: deal almost closed.']);

        $insertLink = self::pdo()->prepare(
            'INSERT INTO links (user_id, url, title) VALUES (?, ?, ?)'
        );
        $insertLink->execute([$adminId, 'https://example.com', 'Example']);

        self::pdo()->exec("UPDATE site_settings SET banner_html='Welcome to <strong>CompanyHub</strong>.' WHERE id=1");
        if ((int) self::pdo()->query('SELECT COUNT(*) FROM site_settings')->fetchColumn() === 0) {
            self::pdo()->exec("INSERT INTO site_settings (banner_html) VALUES ('Welcome to <strong>CompanyHub</strong>.')");
        }
    }

    public static function setUpBeforeClass(): void
    {
        self::reseed();
    }

    protected function assertResponseStatus(int $expected, Response $r): void
    {
        $this->assertSame($expected, $r->getStatusCode(), 'Body: ' . substr((string) $r->getBody(), 0, 500));
    }

    protected function bodyOf(Response $r): string
    {
        return (string) $r->getBody();
    }

    protected function locationOf(Response $r): string
    {
        return $r->getHeaderLine('Location');
    }
}
