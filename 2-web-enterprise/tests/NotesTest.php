<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

use GuzzleHttp\RequestOptions;

class NotesTest extends TestCase
{
    public function testNotesIndexShowsOwnNotes(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/notes');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Sprint planning', $this->bodyOf($r));
    }

    public function testNotesIndexShowsPublicNotesFromOthers(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/notes');
        $this->assertStringContainsString('Company values', $this->bodyOf($r));
    }

    public function testCreateNoteAddsRow(): void
    {
        $jar = $this->loginAs();
        $title = 'Test note ' . bin2hex(random_bytes(3));
        $r = $this->client($jar)->post('/notes', [
            RequestOptions::FORM_PARAMS => ['title' => $title, 'body' => 'Hello', 'is_public' => '1'],
        ]);
        $this->assertResponseStatus(302, $r);
        $row = self::pdo()->prepare('SELECT id FROM notes WHERE title = ?');
        $row->execute([$title]);
        $this->assertNotFalse($row->fetchColumn());
    }

    public function testShowNoteRendersBody(): void
    {
        $jar = $this->loginAs();
        $aliceId = (int) self::pdo()->query("SELECT id FROM users WHERE email='alice@companyhub.local'")->fetchColumn();
        self::pdo()->exec("INSERT INTO notes (user_id, title, body, is_public) VALUES ($aliceId, 'Showable', 'Body content here', 0)");
        $id = (int) self::pdo()->lastInsertId();

        $r = $this->client($jar)->get('/notes/' . $id);
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Body content here', $this->bodyOf($r));
    }

    public function testEditNoteUpdatesRow(): void
    {
        $jar = $this->loginAs();
        $aliceId = (int) self::pdo()->query("SELECT id FROM users WHERE email='alice@companyhub.local'")->fetchColumn();
        self::pdo()->exec("INSERT INTO notes (user_id, title, body, is_public) VALUES ($aliceId, 'Old', 'Old body', 0)");
        $id = (int) self::pdo()->lastInsertId();

        $r = $this->client($jar)->post('/notes/' . $id, [
            RequestOptions::FORM_PARAMS => ['title' => 'New title', 'body' => 'New body'],
        ]);
        $this->assertResponseStatus(302, $r);
        $row = self::pdo()->query("SELECT title FROM notes WHERE id={$id}")->fetchColumn();
        $this->assertSame('New title', $row);
    }

    public function testDeleteNoteRemovesRow(): void
    {
        $jar = $this->loginAs();
        $aliceId = (int) self::pdo()->query("SELECT id FROM users WHERE email='alice@companyhub.local'")->fetchColumn();
        self::pdo()->exec("INSERT INTO notes (user_id, title, body, is_public) VALUES ($aliceId, 'To delete', '', 0)");
        $id = (int) self::pdo()->lastInsertId();

        $r = $this->client($jar)->post('/notes/' . $id . '/delete');
        $this->assertResponseStatus(302, $r);
        $count = (int) self::pdo()->query("SELECT COUNT(*) FROM notes WHERE id={$id}")->fetchColumn();
        $this->assertSame(0, $count);
    }

    public function testNotesRequireAuth(): void
    {
        $r = $this->client()->get('/notes');
        $this->assertResponseStatus(302, $r);
    }
}
