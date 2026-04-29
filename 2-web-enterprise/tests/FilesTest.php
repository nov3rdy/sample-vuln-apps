<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

use GuzzleHttp\RequestOptions;

class FilesTest extends TestCase
{
    public function testFilesIndexRequiresAuth(): void
    {
        $r = $this->client()->get('/files');
        $this->assertResponseStatus(302, $r);
    }

    public function testFilesIndexRendersForLoggedInUser(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/files');
        $this->assertResponseStatus(200, $r);
        $body = $this->bodyOf($r);
        $this->assertStringContainsString('Workspace · Files', $body);
        $this->assertStringContainsString('documents', $body);
    }

    public function testUploadStoresFile(): void
    {
        $jar = $this->loginAs();
        $unique = 'note-' . bin2hex(random_bytes(3)) . '.txt';
        $r = $this->client($jar)->post('/files', [
            RequestOptions::MULTIPART => [[
                'name'     => 'file',
                'filename' => $unique,
                'contents' => 'hello world',
                'headers'  => ['Content-Type' => 'text/plain'],
            ]],
        ]);
        $this->assertResponseStatus(302, $r);

        $row = self::pdo()->prepare('SELECT id FROM files WHERE filename = ?');
        $row->execute([$unique]);
        $this->assertNotFalse($row->fetchColumn());

        // Cleanup the file from disk so /uploads/ doesn't accumulate
        @unlink('/var/www/html/public/uploads/' . $unique);
    }

    public function testDownloadByIdReturnsFileContents(): void
    {
        $jar = $this->loginAs();
        $name = 'dl-' . bin2hex(random_bytes(3)) . '.txt';
        $contents = 'download-me';
        file_put_contents('/var/www/html/public/uploads/' . $name, $contents);
        $aliceId = (int) self::pdo()->query("SELECT id FROM users WHERE email='alice@companyhub.local'")->fetchColumn();
        self::pdo()->prepare(
            'INSERT INTO files (user_id, filename, stored_path, mime_claimed, size_bytes) VALUES (?, ?, ?, ?, ?)'
        )->execute([$aliceId, $name, 'uploads/' . $name, 'text/plain', strlen($contents)]);
        $id = (int) self::pdo()->lastInsertId();

        $r = $this->client($jar)->get('/files/download?id=' . $id);
        $this->assertResponseStatus(200, $r);
        $this->assertSame($contents, $this->bodyOf($r));

        @unlink('/var/www/html/public/uploads/' . $name);
    }
}
