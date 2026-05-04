<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

if (current_user() !== null) {
    redirect('/comment.php');
}

$error = '';
$username = '';

if (is_post()) {
    $username = input_string('username');
    $password = input_string('password');
    $confirmPassword = input_string('confirm_password');

    if (!is_valid_username($username)) {
        $error = 'Username wajib diisi, maksimal ' . MAX_USERNAME_LENGTH . ' karakter, dan hanya boleh berisi huruf, angka, titik, underscore, atau strip.';
    } elseif (is_reserved_admin_username($username)) {
        $error = 'Username admin/root tidak boleh didaftarkan dari halaman sign up publik.';
    } elseif (!is_valid_password_input($password)) {
        $error = 'Password wajib diisi dan maksimal ' . MAX_PASSWORD_LENGTH . ' karakter.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $pdo = db();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $demoPasswordColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'demo_password'")->fetch();
        $allowDemoPassword = getenv('ALLOW_DEMO_PASSWORD') === '1';

        try {
            if ($allowDemoPassword && $demoPasswordColumn) {
                $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, demo_password, is_admin) VALUES (:username, :password_hash, :demo_password, 0)');
                $stmt->execute([
                    'username' => $username,
                    'password_hash' => $hash,
                    'demo_password' => $password,
                ]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, is_admin) VALUES (:username, :password_hash, 0)');
                $stmt->execute([
                    'username' => $username,
                    'password_hash' => $hash,
                ]);
            }
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $error = 'Username sudah digunakan.';
            } else {
                throw $exception;
            }
        }

        if ($error === '') {
            login_user($username, false);
            flash('Akun berhasil dibuat. Anda login sebagai user biasa.', 'success');
            redirect('/comment.php');
        }
    }
}

page_header('Sign Up');
?>

<section class="auth-layout">
    <div>
        <p class="eyebrow">Public Registration</p>
        <h1>Daftar user baru</h1>
        <p>
            Akun baru dapat menulis komentar, tetapi tidak bisa membuka admin panel.
            Hanya akun <strong>admin</strong> yang memiliki akses monitoring database.
        </p>
    </div>

    <form class="form-card" method="post" action="/signup.php" autocomplete="off">
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

        <label for="confirm_password">Konfirmasi Password</label>
        <input
            id="confirm_password"
            name="confirm_password"
            type="password"
            maxlength="<?= MAX_PASSWORD_LENGTH ?>"
            required
        >

        <button class="button" type="submit">Buat Akun</button>
        <p class="hint">Sudah punya akun? <a class="text-link" href="/login.php">Login di sini</a>.</p>
    </form>
</section>

<?php page_footer(); ?>
