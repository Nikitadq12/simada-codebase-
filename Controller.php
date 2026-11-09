<?php
/**
 * Abstract class Controller
 * Induk semua controller: render view di dalam layout, redirect dengan
 * pesan flash, dan pemeriksaan CSRF otomatis untuk request POST.
 */
abstract class Controller
{
    protected function render(string $view, array $data = [], string $active = ''): void
    {
        extract($data);
        $user = Auth::user();
        $csrf = Auth::csrfToken();
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . "/../views/$view.php";
        require __DIR__ . '/../views/layout/footer.php';
    }

    protected function redirect(string $query, ?string $flash = null): void
    {
        if ($flash) $_SESSION['flash'] = $flash;
        header("Location: index.php?$query");
        exit;
    }

    protected function isPost(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        Auth::verifyCsrf();
        return true;
    }

    protected function forbidden(): void
    {
        http_response_code(403);
        require __DIR__ . '/../views/layout/403.php';
        exit;
    }
}
