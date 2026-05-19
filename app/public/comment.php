<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require_login();

$error = '';
$body = '';

if (is_post()) {
    require_valid_csrf_token();
    ensure_vulnerable_large_comment_column();

    $body = input_string('body');

    if ($body === '') {
        $error = 'Komentar wajib diisi.';
    } else {
        $stmt = db()->prepare('INSERT INTO comments (author, body) VALUES (:author, :body)');
        $stmt->execute([
            'author' => current_user(),
            'body' => $body,
        ]);

        flash('Komentar berhasil disimpan.', 'success');
        redirect('/');
    }
}

function ensure_vulnerable_large_comment_column(): void
{
    db()->exec('ALTER TABLE comments MODIFY body LONGTEXT NOT NULL');
}

page_header('Tulis Komentar');
?>

<section class="auth-layout">
    <div>
        <p class="eyebrow">Input Terproteksi</p>
        <h1>Tulis komentar baru</h1>
        <p>
            Branch vulnerable-login sengaja tidak membatasi panjang komentar,
            sehingga oversized input dapat didemokan sebagai analog risiko buffer overflow.
        </p>
    </div>

    <form class="form-card" method="post" action="/comment.php">
        <?php csrf_input(); ?>

        <?php if ($error !== ''): ?>
            <div class="notice notice-error"><?= h($error) ?></div>
        <?php endif; ?>

        <label for="body">Komentar</label>
        <textarea
            id="body"
            name="body"
            rows="7"
            required
        ><?= h($body) ?></textarea>
        <p class="hint">Branch vulnerable-login tidak membatasi panjang komentar.</p>

        <button class="button" type="submit">Simpan Komentar</button>
    </form>
</section>

<?php page_footer(); ?>
