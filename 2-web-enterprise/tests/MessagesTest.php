<?php
declare(strict_types=1);

namespace CompanyHub\Tests;

use GuzzleHttp\RequestOptions;

class MessagesTest extends TestCase
{
    public function testInboxShowsRecipientMessages(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/messages');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Heads up: maintenance', $this->bodyOf($r));
    }

    public function testComposeFormRendersRecipients(): void
    {
        $jar = $this->loginAs();
        $r = $this->client($jar)->get('/messages/new');
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Bob Brown', $this->bodyOf($r));
    }

    public function testSendMessageInsertsRow(): void
    {
        $jar = $this->loginAs();
        $bobId = (int) self::pdo()->query("SELECT id FROM users WHERE email='bob@companyhub.local'")->fetchColumn();
        $body = 'Hi Bob ' . bin2hex(random_bytes(3));

        $r = $this->client($jar)->post('/messages', [
            RequestOptions::FORM_PARAMS => ['recipient_id' => $bobId, 'body' => $body],
        ]);
        $this->assertResponseStatus(302, $r);
        $row = self::pdo()->prepare('SELECT id FROM messages WHERE body = ?');
        $row->execute([$body]);
        $this->assertNotFalse($row->fetchColumn());
    }

    public function testShowMessageRendersBody(): void
    {
        $jar = $this->loginAs();
        $aliceId = (int) self::pdo()->query("SELECT id FROM users WHERE email='alice@companyhub.local'")->fetchColumn();
        $bobId   = (int) self::pdo()->query("SELECT id FROM users WHERE email='bob@companyhub.local'")->fetchColumn();
        self::pdo()->exec("INSERT INTO messages (sender_id, recipient_id, body) VALUES ($bobId, $aliceId, 'Showable msg body')");
        $id = (int) self::pdo()->lastInsertId();

        $r = $this->client($jar)->get('/messages/' . $id);
        $this->assertResponseStatus(200, $r);
        $this->assertStringContainsString('Showable msg body', $this->bodyOf($r));
    }
}
