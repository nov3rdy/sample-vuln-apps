<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

use GuzzleHttp\RequestOptions;

class ImportTest extends TestCase
{
    public function testImportFormRenders(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/import');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Bulk-import', $this->bodyOf($r));
    }

    public function testValidImportCreatesUsers(): void
    {
        $jar = $this->loginAs();
        $email = 'imp-' . bin2hex(random_bytes(3)) . '@companyhub.local';
        $xml = "<?xml version='1.0'?><contacts><contact><name>Imp Test</name><email>{$email}</email><note>n</note></contact></contacts>";
        $r = $this->client($jar)->post('/import', [
            RequestOptions::MULTIPART => [[
                'name'     => 'xml',
                'filename' => 'import.xml',
                'contents' => $xml,
                'headers'  => ['Content-Type' => 'application/xml'],
            ]],
        ]);
        $this->assertResponseStatus(200, $r);
        $row = self::pdo()->prepare('SELECT id FROM users WHERE email = ?');
        $row->execute([$email]);
        $this->assertNotFalse($row->fetchColumn());
    }
}
