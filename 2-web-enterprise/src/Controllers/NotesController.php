<?php
declare(strict_types=1);

namespace CompanyHub\Controllers;

use CompanyHub\Auth;
use CompanyHub\Controller;
use CompanyHub\Db;

class NotesController extends Controller
{
    public function index(): void
    {
        Auth::requireUser();
        $user = Auth::currentUser();
        $myNotes     = Db::all('SELECT * FROM notes WHERE user_id = ? ORDER BY updated_at DESC', [$user['id']]);
        $publicNotes = Db::all(
            'SELECT n.*, u.display_name AS author FROM notes n
             JOIN users u ON u.id = n.user_id
             WHERE n.is_public = 1 AND n.user_id <> ?
             ORDER BY n.updated_at DESC',
            [$user['id']]
        );
        $this->view('notes/list', compact('myNotes', 'publicNotes'));
    }

    public function show(array $params): void
    {
        Auth::requireUser();
        $id = (int) ($params['id'] ?? 0);

        // V6: IDOR — a logged-in user can read any note by guessing the id.
        // No `WHERE user_id = ?` or `is_public = 1` check.
        $note = Db::one(
            'SELECT n.*, u.display_name AS author FROM notes n
             JOIN users u ON u.id = n.user_id
             WHERE n.id = ?',
            [$id]
        );
        if (!$note) {
            http_response_code(404);
            echo '<h1>Note not found</h1>';
            return;
        }
        $this->view('notes/show', ['note' => $note]);
    }

    public function create(): void
    {
        Auth::requireUser();
        $this->view('notes/edit', ['note' => null]);
    }

    public function store(): void
    {
        Auth::requireUser();
        $user = Auth::currentUser();

        // V5: CSRF — no token is required for this state-changing POST.
        $title    = (string) $this->input('title', 'Untitled');
        $body     = (string) $this->input('body', '');
        $isPublic = $this->input('is_public') !== null ? 1 : 0;

        Db::exec(
            'INSERT INTO notes (user_id, title, body, is_public) VALUES (?, ?, ?, ?)',
            [$user['id'], $title, $body, $isPublic]
        );

        // V2: Stored XSS — body is rendered raw in the show view (no htmlspecialchars).
        // Once any user views the note, the script runs in their session.
        $this->flash('success', 'Note created.');
        $this->redirect('/notes');
    }

    public function edit(array $params): void
    {
        Auth::requireUser();
        $id = (int) ($params['id'] ?? 0);
        // V6: IDOR — note loaded without ownership check, so any user can edit any note.
        $note = Db::one('SELECT * FROM notes WHERE id = ?', [$id]);
        if (!$note) {
            http_response_code(404);
            echo '<h1>Note not found</h1>';
            return;
        }
        $this->view('notes/edit', ['note' => $note]);
    }

    public function update(array $params): void
    {
        Auth::requireUser();
        $id = (int) ($params['id'] ?? 0);

        // V5: CSRF + V6: IDOR — no token + no ownership check.
        $title    = (string) $this->input('title', 'Untitled');
        $body     = (string) $this->input('body', '');
        $isPublic = $this->input('is_public') !== null ? 1 : 0;

        Db::exec(
            'UPDATE notes SET title = ?, body = ?, is_public = ? WHERE id = ?',
            [$title, $body, $isPublic, $id]
        );
        $this->flash('success', 'Note updated.');
        $this->redirect('/notes/' . $id);
    }

    public function delete(array $params): void
    {
        Auth::requireUser();
        $id = (int) ($params['id'] ?? 0);
        // V5: CSRF + V6: IDOR — anyone can delete any note via a forged POST from another origin.
        Db::exec('DELETE FROM notes WHERE id = ?', [$id]);
        $this->flash('success', 'Note deleted.');
        $this->redirect('/notes');
    }
}
