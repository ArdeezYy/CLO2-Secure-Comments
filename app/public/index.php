<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$stmt = db()->query('SELECT author, body, created_at FROM comments ORDER BY created_at DESC, id DESC');
$comments = $stmt->fetchAll();

page_header('Papan Komentar Publik');
?>

<section class="hero">
    <p class="eyebrow">Pengamanan Aplikasi Web</p>
    <h1>Papan komentar publik dengan akses tulis terbatas.</h1>
    <p>
        Halaman ini bisa dibaca semua pengunjung. Komentar baru hanya dapat dibuat
        setelah autentikasi berhasil melalui sesi pengguna.
    </p>
    <div class="actions">
        <?php if (current_user() !== null): ?>
            <a class="button" href="/comment.php">Tulis Komentar</a>
        <?php else: ?>
            <a class="button" href="/login.php">Login untuk Menulis</a>
        <?php endif; ?>
    </div>
</section>

<section class="panel">
    <div class="section-title">
        <h2>Komentar Terbaru</h2>
        <span><?= count($comments) ?> data</span>
    </div>

    <?php if ($comments === []): ?>
        <p class="empty">Belum ada komentar.</p>
    <?php else: ?>
        <div class="comments">
            <?php foreach ($comments as $comment): ?>
                <article class="comment">
                    <div class="comment-meta">
                        <strong><?= h($comment['author']) ?></strong>
                        <time><?= h(date('d M Y H:i', strtotime((string) $comment['created_at']))) ?></time>
                    </div>
                    <p><?= nl2br(h($comment['body'])) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php page_footer(); ?>
