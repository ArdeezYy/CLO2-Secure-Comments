<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require_login();

$error = '';
$body = '';

if (is_post()) {
    require_valid_csrf_token();

    $body = input_string('body');

    if (!is_valid_comment($body)) {
        $error = 'Komentar wajib diisi dan maksimal ' . MAX_COMMENT_LENGTH . ' karakter.';
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

page_header('Tulis Komentar');
?>

<section class="auth-layout">
    <div>
        <p class="eyebrow">Input Terproteksi</p>
        <h1>Tulis komentar baru</h1>
        <p>
            Input diproses menggunakan prepared statement, dibatasi panjangnya,
            dan akan di-escape saat ditampilkan di halaman publik.
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
            maxlength="<?= MAX_COMMENT_LENGTH ?>"
            rows="7"
            required
        ><?= h($body) ?></textarea>
        <p class="hint">Maksimal <?= MAX_COMMENT_LENGTH ?> karakter.</p>

        <button class="button" type="submit">Simpan Komentar</button>
    </form>
</section>

<?php page_footer(); ?>
