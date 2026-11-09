<?php
/**
 * Class Auth  (Modul 1 - Login/Autentikasi, UC-01, FR-26)
 * Mengelola sesi login dan otorisasi berbasis role (NFR-05).
 */
class Auth
{
    public const MAX_ATTEMPTS = 5;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * @return string|null null jika berhasil, pesan error jika gagal
     */
    public static function attempt(string $email, string $password): ?string
    {
        $_SESSION['attempts'] = $_SESSION['attempts'] ?? 0;
        if ($_SESSION['attempts'] >= self::MAX_ATTEMPTS) {
            return 'Terlalu banyak percobaan login. Silakan coba lagi nanti.';
        }

        $user = (new User())->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['attempts']++;
            return 'Email atau password salah.';
        }
        if ($user['status'] !== 'aktif') {
            return 'Akun Anda nonaktif. Hubungi Admin.';
        }

        session_regenerate_id(true);           // cegah session fixation
        $_SESSION['attempts'] = 0;
        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'nama'  => $user['nama'],
            'email' => $user['email'],
            'role'  => $user['nama_role'],
        ];
        return null;
    }

    /** Token CSRF untuk setiap form POST (NFR-08 keamanan). */
    public static function csrfToken(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
    }

    public static function verifyCsrf(): void
    {
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['_csrf'] ?? '')) {
            http_response_code(419);
            exit('Sesi formulir kedaluwarsa. Muat ulang halaman.');
        }
    }

    public static function check(): bool { return isset($_SESSION['user']); }
    public static function user(): ?array { return $_SESSION['user'] ?? null; }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /** Hentikan request jika belum login atau role tidak diizinkan. */
    public static function requireRole(array $roles = []): void
    {
        if (!self::check()) {
            header('Location: index.php?page=login');
            exit;
        }
        if ($roles && !in_array(self::user()['role'], $roles, true)) {
            http_response_code(403);
            require __DIR__ . '/../views/layout/403.php';
            exit;
        }
    }
}
