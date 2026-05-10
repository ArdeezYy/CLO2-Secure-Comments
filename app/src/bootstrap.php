<?php
declare(strict_types=1);

const MAX_USERNAME_LENGTH = 50;
const MAX_PASSWORD_LENGTH = 128;
const MIN_PASSWORD_LENGTH = 8;
const MAX_COMMENT_LENGTH = 500;
const FAILED_LOGIN_DELAY_SECONDS = 2;
const RESERVED_ADMIN_USERNAMES = ['admin', 'root'];

start_secure_session();

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('CLO2SESSID');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: 'db';
    $name = getenv('DB_NAME') ?: 'clo2_comments';
    $user = getenv('DB_USER') ?: 'clo2_user';
    $pass = getenv('DB_PASS') ?: 'clo2_password';

    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    ensure_user_role_column($pdo);
    ensure_default_admin($pdo);

    return $pdo;
}

function ensure_user_role_column(PDO $pdo): void
{
    $column = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_admin'")->fetch();

    if (!$column) {
        try {
            $pdo->exec('ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0');
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '42S21') {
                throw $exception;
            }
        }
    }
}

function ensure_default_admin(PDO $pdo): void
{
    ensure_user_role_column($pdo);

    $username = getenv('ADMIN_USERNAME') ?: 'admin';
    $password = getenv('ADMIN_PASSWORD') ?: 'Admin@240!';

    if (!is_valid_username($username) || $password === '' || strlen($password) > MAX_PASSWORD_LENGTH) {
        return;
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);

    if ($stmt->fetch()) {
        $update = $pdo->prepare('UPDATE users SET is_admin = 1 WHERE username = :username');
        $update->execute(['username' => $username]);
        return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (username, password_hash, is_admin) VALUES (:username, :password_hash, 1)');
    $insert->execute([
        'username' => $username,
        'password_hash' => $hash,
    ]);
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header("Location: {$path}");
    exit;
}

function current_user(): ?string
{
    return isset($_SESSION['username']) && is_string($_SESSION['username'])
        ? $_SESSION['username']
        : null;
}

function current_user_is_admin(): bool
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function login_user(string $username, bool $isAdmin): void
{
    session_regenerate_id(true);
    $_SESSION['username'] = $username;
    $_SESSION['is_admin'] = $isAdmin;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_input(): void
{
    ?>
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <?php
}

function has_valid_csrf_token(): bool
{
    $token = $_POST['csrf_token'] ?? '';

    return is_string($token) && hash_equals(csrf_token(), $token);
}

function require_valid_csrf_token(): void
{
    if (!has_valid_csrf_token()) {
        flash('Sesi form tidak valid. Silakan coba lagi.', 'error');
        redirect($_SERVER['REQUEST_URI'] ?? '/');
    }
}

function require_login(): void
{
    if (current_user() === null) {
        flash('Silakan login terlebih dahulu untuk menambahkan komentar.', 'error');
        redirect('/login.php');
    }
}

function require_admin(): void
{
    require_login();

    if (!current_user_is_admin()) {
        flash('Admin panel hanya dapat diakses oleh akun admin/root.', 'error');
        redirect('/');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function is_valid_username(string $username): bool
{
    return $username !== ''
        && strlen($username) <= MAX_USERNAME_LENGTH
        && preg_match('/^[A-Za-z0-9_.-]+$/', $username) === 1;
}

function is_reserved_admin_username(string $username): bool
{
    return in_array(strtolower($username), RESERVED_ADMIN_USERNAMES, true);
}

function is_valid_password_input(string $password): bool
{
    return $password !== '' && strlen($password) <= MAX_PASSWORD_LENGTH;
}

function password_policy_error(string $password): ?string
{
    if (!is_valid_password_input($password)) {
        return 'Password wajib diisi dan maksimal ' . MAX_PASSWORD_LENGTH . ' karakter.';
    }

    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        return 'Password minimal ' . MIN_PASSWORD_LENGTH . ' karakter.';
    }

    if (preg_match('/[a-z]/', $password) !== 1
        || preg_match('/[A-Z]/', $password) !== 1
        || preg_match('/[0-9]/', $password) !== 1
        || preg_match('/[^A-Za-z0-9]/', $password) !== 1) {
        return 'Password harus memuat huruf besar, huruf kecil, angka, dan simbol.';
    }

    return null;
}

function is_valid_comment(string $comment): bool
{
    return $comment !== '' && strlen($comment) <= MAX_COMMENT_LENGTH;
}

function flash(string $message, string $type = 'info'): void
{
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type,
    ];
}

function take_flash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function page_header(string $title): void
{
    $user = current_user();
    $flash = take_flash();
    ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> - CLO 2</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="/">CLO 2 Secure Comments</a>
        <nav class="nav">
            <a href="/">Komentar</a>
            <?php if ($user !== null): ?>
                <?php if (current_user_is_admin()): ?>
                    <a href="/admin.php">Admin Panel</a>
                <?php endif; ?>
                <a href="/comment.php">Tulis Komentar</a>
                <span class="user">Login: <?= h($user) ?></span>
                <form class="inline-form" method="post" action="/logout.php">
                    <?php csrf_input(); ?>
                    <button class="button button-outline" type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a href="/signup.php">Sign Up</a>
                <a class="button" href="/login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="shell">
        <?php if ($flash !== null): ?>
            <div class="notice notice-<?= h((string) ($flash['type'] ?? 'info')) ?>">
                <?= h((string) ($flash['message'] ?? '')) ?>
            </div>
        <?php endif; ?>
    <?php
}

function page_footer(): void
{
    ?>
    </main>
    <script src="/assets/app.js" defer></script>
</body>
</html>
    <?php
}
