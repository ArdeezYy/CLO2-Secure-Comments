<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

if (current_user() !== null) {
    redirect('/comment.php');
}

$error = '';
$username = '';
$unsafeSql = '';

if (is_post()) {
    require_valid_csrf_token();

    $username = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === '' || $password === '' || strlen($username) > 120 || strlen($password) > 120) {
        $error = 'Username atau password tidak valid.';
    } else {
        // Branch vulnerable-login sengaja tidak memakai rate limiting agar brute force mudah didemokan.
        ensure_vulnerable_demo_password();

        $unsafeSql = "SELECT username, is_admin FROM users WHERE username = '{$username}' AND demo_password = '{$password}' LIMIT 1";

        try {
            $user = db()->query($unsafeSql)->fetch();
        } catch (PDOException) {
            $user = false;
        }

        if ($user) {
            login_user((string) $user['username'], (bool) $user['is_admin']);
            flash('Login berhasil. Pada branch ini login sengaja rentan SQL injection.', 'success');
            redirect('/comment.php');
        }

        $error = 'Username atau password salah.';
    }
}

function ensure_vulnerable_demo_password(): void
{
    $pdo = db();
    $adminUsername = getenv('ADMIN_USERNAME') ?: 'admin';
    $adminPassword = getenv('ADMIN_PASSWORD') ?: 'Admin@240!';

    $column = $pdo->query("SHOW COLUMNS FROM users LIKE 'demo_password'")->fetch();

    if (!$column) {
        try {
            $pdo->exec('ALTER TABLE users ADD COLUMN demo_password VARCHAR(128) NULL');
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '42S21') {
                throw $exception;
            }
        }
    }

    $stmt = $pdo->prepare('UPDATE users SET demo_password = :password WHERE username = :username AND demo_password IS NULL');
    $stmt->execute([
        'username' => $adminUsername,
        'password' => $adminPassword,
    ]);
}

page_header('Login');
?>

<section class="auth-layout">
    <div>
        <p class="eyebrow">Restricted Access</p>
        <h1>Login pengguna</h1>
        <p>
            Branch ini sengaja memakai login rentan untuk menunjukkan bagaimana
            SQL injection dapat membypass autentikasi.
        </p>
        <p>
            Payload bukti: username <code>' OR '1'='1' -- -</code>, password
            bebas. Kembali ke branch <code>secure-login</code> untuk versi aman.
        </p>
        <p>
            Branch ini juga sengaja tidak memakai delay dan lockout login, sehingga
            percobaan password berulang dapat berjalan cepat untuk demo brute force.
        </p>
        <p>
            Belum punya akun? <a class="text-link" href="/signup.php">Daftar user baru</a>.
        </p>
    </div>

    <form class="form-card" method="post" action="/login.php" autocomplete="off">
        <?php csrf_input(); ?>

        <?php if ($error !== ''): ?>
            <div class="notice notice-error"><?= h($error) ?></div>
        <?php endif; ?>

        <label for="username">Username</label>
        <input
            id="username"
            name="username"
            type="text"
            maxlength="<?= MAX_USERNAME_LENGTH ?>"
            value="<?= h($username) ?>"
            required
            autofocus
        >

        <label for="password">Password</label>
        <div class="password-field">
            <input
                id="password"
                name="password"
                type="password"
                maxlength="<?= MAX_PASSWORD_LENGTH ?>"
                required
            >
            <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Tampilkan password"></button>
        </div>

        <button class="button" type="submit">Login</button>

        <?php if ($unsafeSql !== ''): ?>
            <div class="query-box">
                <strong>Query rentan yang dijalankan</strong>
                <pre class="code-block"><?= h($unsafeSql) ?></pre>
            </div>
        <?php endif; ?>
    </form>
</section>

<?php page_footer(); ?>
