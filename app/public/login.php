<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

if (current_user() !== null) {
    redirect('/comment.php');
}

$error = '';
$username = '';

if (is_post()) {
    require_valid_csrf_token();

    $username = input_string('username');
    $password = input_string('password');

    if (!is_valid_username($username) || !is_valid_password_input($password)) {
        sleep(FAILED_LOGIN_DELAY_SECONDS);
        $error = 'Username atau password tidak valid.';
    } else {
        $stmt = db()->prepare('SELECT username, password_hash, is_admin FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, (string) $user['password_hash'])) {
            login_user((string) $user['username'], (bool) $user['is_admin']);
            flash('Login berhasil. Anda dapat menambahkan komentar.', 'success');
            redirect('/comment.php');
        }

        sleep(FAILED_LOGIN_DELAY_SECONDS);
        $error = 'Username atau password salah.';
    }
}

page_header('Login');
?>

<section class="auth-layout">
    <div>
        <p class="eyebrow">Restricted Access</p>
        <h1>Login pengguna</h1>
        <p>
            Gunakan akun demo <strong>admin</strong> dengan password
            <strong>Admin@240!</strong> untuk masuk dan menulis komentar.
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
        <input
            id="password"
            name="password"
            type="password"
            maxlength="<?= MAX_PASSWORD_LENGTH ?>"
            required
        >

        <button class="button" type="submit">Login</button>
    </form>
</section>

<?php page_footer(); ?>
