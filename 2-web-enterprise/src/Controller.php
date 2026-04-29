<?php
declare(strict_types=1);

namespace CompanyHub;

abstract class Controller
{
    /**
     * Render a view template under src/Views/{template}.php inside a chrome layout.
     * @param array<string,mixed> $data
     */
    protected function view(string $template, array $data = [], string $layout = 'layout'): void
    {
        $viewFile = dirname(__DIR__) . '/src/Views/' . $template . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("View not found: $template");
        }

        $currentUser = Auth::currentUser();
        $isAdmin     = Auth::isAdmin();
        $flash       = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $banner      = Db::one('SELECT banner_html FROM site_settings ORDER BY id ASC LIMIT 1');

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require dirname(__DIR__) . '/src/Views/' . $layout . '.php';
    }

    /**
     * Render a view inside the auth (chromeless) layout — no sidebar, no banner.
     * Used for /login, /register, /forgot, /reset.
     * @param array<string,mixed> $data
     */
    protected function viewAuth(string $template, array $data = []): void
    {
        $this->view($template, $data, 'auth_layout');
    }

    /**
     * Render a view template without the layout (used for raw-HTML responses like the link preview).
     * @param array<string,mixed> $data
     */
    protected function viewRaw(string $template, array $data = []): void
    {
        $viewFile = dirname(__DIR__) . '/src/Views/' . $template . '.php';
        extract($data, EXTR_SKIP);
        require $viewFile;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function param(string $name, ?string $default = null): ?string
    {
        $val = $_GET[$name] ?? $_POST[$name] ?? $default;
        return $val === null ? null : (string) $val;
    }

    protected function input(string $name, ?string $default = null): ?string
    {
        $val = $_POST[$name] ?? $default;
        return $val === null ? null : (string) $val;
    }

    /**
     * @param array<string,mixed> $data
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
