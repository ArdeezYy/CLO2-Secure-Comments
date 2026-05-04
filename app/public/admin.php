<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require_admin();

$pdo = db();

$userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$commentCount = (int) $pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn();
$latestCommentAt = $pdo->query('SELECT MAX(created_at) FROM comments')->fetchColumn();
$demoPasswordColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'demo_password'")->fetch();
$showDemoPassword = getenv('ALLOW_DEMO_PASSWORD') === '1' && (bool) $demoPasswordColumn;

$users = $pdo->query('SELECT * FROM users ORDER BY id ASC')->fetchAll();
$comments = $pdo->query('SELECT id, author, body, created_at FROM comments ORDER BY created_at DESC, id DESC LIMIT 50')->fetchAll();

page_header('Admin Panel');
?>

<section class="hero admin-hero">
    <p class="eyebrow">Database Monitoring</p>
    <h1>Admin panel database</h1>
    <p>
        Pantau tabel user dan komentar langsung dari aplikasi. Semua output tetap
        di-escape agar payload XSS di database tidak dieksekusi browser.
        Panel ini hanya dapat diakses akun admin/root.
    </p>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <span>Total User</span>
        <strong><?= h((string) $userCount) ?></strong>
    </article>
    <article class="stat-card">
        <span>Total Komentar</span>
        <strong><?= h((string) $commentCount) ?></strong>
    </article>
    <article class="stat-card">
        <span>Komentar Terakhir</span>
        <strong><?= h($latestCommentAt ? date('d M Y H:i', strtotime((string) $latestCommentAt)) : '-') ?></strong>
    </article>
    <article class="stat-card">
        <span>Mode Demo SQLi</span>
        <?php if ($showDemoPassword): ?>
            <strong class="status-danger">Aktif</strong>
        <?php else: ?>
            <strong class="status-safe">Nonaktif</strong>
        <?php endif; ?>
    </article>
</section>

<section class="panel admin-panel">
    <div class="section-title">
        <h2>Tabel Users</h2>
        <span><?= count($users) ?> baris</span>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Password Hash</th>
                    <?php if ($showDemoPassword): ?>
                        <th>Demo Password</th>
                    <?php endif; ?>
                    <th>Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $userRow): ?>
                    <tr>
                        <td><?= h((string) $userRow['id']) ?></td>
                        <td><?= h((string) $userRow['username']) ?></td>
                        <td><?= (int) $userRow['is_admin'] === 1 ? 'admin' : 'user' ?></td>
                        <td><code><?= h((string) $userRow['password_hash']) ?></code></td>
                        <?php if ($showDemoPassword): ?>
                            <td><code><?= h((string) ($userRow['demo_password'] ?? '')) ?></code></td>
                        <?php endif; ?>
                        <td><?= h(date('d M Y H:i', strtotime((string) $userRow['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel admin-panel">
    <div class="section-title">
        <h2>Tabel Comments</h2>
        <span>Maksimal 50 komentar terbaru</span>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Author</th>
                    <th>Komentar</th>
                    <th>Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $commentRow): ?>
                    <tr>
                        <td><?= h((string) $commentRow['id']) ?></td>
                        <td><?= h((string) $commentRow['author']) ?></td>
                        <td class="comment-cell"><?= h((string) $commentRow['body']) ?></td>
                        <td><?= h(date('d M Y H:i', strtotime((string) $commentRow['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php page_footer(); ?>
