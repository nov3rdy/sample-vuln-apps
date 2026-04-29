<?php
declare(strict_types=1);

namespace CompanyHub;

use CompanyHub\Controllers\AdminController;
use CompanyHub\Controllers\AuthController;
use CompanyHub\Controllers\DirectoryController;
use CompanyHub\Controllers\FilesController;
use CompanyHub\Controllers\HomeController;
use CompanyHub\Controllers\ImportController;
use CompanyHub\Controllers\LinksController;
use CompanyHub\Controllers\MessagesController;
use CompanyHub\Controllers\NotesController;
use CompanyHub\Controllers\ProfileController;
use Dotenv\Dotenv;

class App
{
    public static function run(): void
    {
        self::bootstrap();

        $router = new Router();
        self::registerRoutes($router);

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        $router->dispatch($method, $path);
    }

    private static function bootstrap(): void
    {
        Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

        // V12: Security Misconfiguration — display all errors to the browser
        ini_set('display_errors', '1');
        error_reporting(E_ALL);

        // V13: Cryptographic Failures (cookie-side) — session cookies set without
        // Secure / HttpOnly / SameSite flags. Anyone with XSS or network MITM can steal them.
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => false,
            'secure'   => false,
            'samesite' => 'None',
        ]);
        session_start();

        Auth::resumeFromRememberCookie();
        Auth::loadPreferencesCookie();
    }

    private static function registerRoutes(Router $r): void
    {
        $r->get('/',                   [HomeController::class,      'redirectHome']);

        $r->get('/login',              [AuthController::class,      'showLogin']);
        $r->post('/login',             [AuthController::class,      'doLogin']);
        $r->get('/register',           [AuthController::class,      'showRegister']);
        $r->post('/register',          [AuthController::class,      'doRegister']);
        $r->get('/logout',             [AuthController::class,      'logout']);
        $r->get('/forgot',             [AuthController::class,      'showForgot']);
        $r->post('/forgot',            [AuthController::class,      'doForgot']);
        $r->get('/reset',              [AuthController::class,      'showReset']);
        $r->post('/reset',             [AuthController::class,      'doReset']);

        $r->get('/dashboard',          [HomeController::class,      'dashboard']);

        $r->get('/directory',          [DirectoryController::class, 'index']);
        $r->get('/directory/search',   [DirectoryController::class, 'search']);
        $r->get('/directory/{id}',     [DirectoryController::class, 'show']);

        $r->get('/notes',              [NotesController::class,     'index']);
        $r->get('/notes/new',          [NotesController::class,     'create']);
        $r->post('/notes',             [NotesController::class,     'store']);
        $r->get('/notes/{id}',         [NotesController::class,     'show']);
        $r->get('/notes/{id}/edit',    [NotesController::class,     'edit']);
        $r->post('/notes/{id}',        [NotesController::class,     'update']);
        $r->post('/notes/{id}/delete', [NotesController::class,     'delete']);

        $r->get('/messages',           [MessagesController::class,  'inbox']);
        $r->get('/messages/new',       [MessagesController::class,  'compose']);
        $r->post('/messages',          [MessagesController::class,  'send']);
        $r->get('/messages/{id}',      [MessagesController::class,  'show']);

        $r->get('/files',              [FilesController::class,     'index']);
        $r->post('/files',             [FilesController::class,     'upload']);
        $r->get('/files/download',     [FilesController::class,     'download']);

        $r->get('/links',              [LinksController::class,     'index']);
        $r->post('/links',             [LinksController::class,     'store']);
        $r->get('/links/preview',      [LinksController::class,     'preview']);

        $r->get('/profile',            [ProfileController::class,   'show']);
        $r->post('/profile/avatar',    [ProfileController::class,   'avatar']);
        $r->post('/profile/preferences', [ProfileController::class, 'savePreferences']);

        $r->get('/import',             [ImportController::class,    'show']);
        $r->post('/import',            [ImportController::class,    'doImport']);

        $r->get('/admin',              [AdminController::class,     'index']);
        $r->get('/admin/users',        [AdminController::class,     'users']);
        $r->post('/admin/users/{id}/role', [AdminController::class, 'changeRole']);
        $r->get('/admin/banner',       [AdminController::class,     'showBanner']);
        $r->post('/admin/banner',      [AdminController::class,     'saveBanner']);
        $r->get('/admin/stats',        [AdminController::class,     'stats']);
    }
}
