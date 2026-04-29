<?php
declare(strict_types=1);

namespace CompanyHub\Controllers;

use CompanyHub\Auth;
use CompanyHub\Controller;
use CompanyHub\Db;

class MessagesController extends Controller
{
    public function inbox(): void
    {
        Auth::requireUser();
        $user = Auth::currentUser();
        $messages = Db::all(
            'SELECT m.*, u.display_name AS sender_name FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.recipient_id = ?
             ORDER BY m.created_at DESC',
            [$user['id']]
        );
        $this->view('messages/inbox', ['messages' => $messages]);
    }

    public function show(array $params): void
    {
        Auth::requireUser();
        $id = (int) ($params['id'] ?? 0);

        // V6: IDOR — any logged-in user can read any DM by id, no recipient check.
        $message = Db::one(
            'SELECT m.*, s.display_name AS sender_name, r.display_name AS recipient_name
             FROM messages m
             JOIN users s ON s.id = m.sender_id
             JOIN users r ON r.id = m.recipient_id
             WHERE m.id = ?',
            [$id]
        );
        if (!$message) {
            http_response_code(404);
            echo '<h1>Message not found</h1>';
            return;
        }
        $this->view('messages/thread', ['message' => $message]);
    }

    public function compose(): void
    {
        Auth::requireUser();
        $recipients = Db::all('SELECT id, display_name, email FROM users ORDER BY display_name');
        $this->view('messages/compose', ['recipients' => $recipients]);
    }

    public function send(): void
    {
        Auth::requireUser();
        $user = Auth::currentUser();
        // V5: CSRF — no token check on send.
        $recipientId = (int) $this->input('recipient_id', '0');
        $body        = (string) $this->input('body', '');

        if ($recipientId <= 0 || $body === '') {
            $this->flash('error', 'Recipient and body are required.');
            $this->redirect('/messages/new');
        }

        // V2: Stored XSS — message body is rendered raw in the inbox + thread views.
        Db::exec(
            'INSERT INTO messages (sender_id, recipient_id, body) VALUES (?, ?, ?)',
            [$user['id'], $recipientId, $body]
        );
        $this->flash('success', 'Message sent.');
        $this->redirect('/messages');
    }
}
