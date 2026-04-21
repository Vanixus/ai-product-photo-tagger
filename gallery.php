<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireAuth();

$rows = [];
$error = null;

try {
    require_once __DIR__ . '/config/db.php';
    $stmt = db()->query('SELECT file_path, user_note, tags, description, created_at FROM product_images ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();
} catch (Throwable $exception) {
    $error = 'Unable to load gallery. Check database configuration.';
}

function pathToUrl(string $relativePath): string
{
    $normalized = str_replace('\\', '/', ltrim($relativePath, '/\\'));
    if (APP_BASE_URL === '') {
        return $normalized;
    }

    return rtrim(APP_BASE_URL, '/') . '/' . $normalized;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gallery &mdash; <?php echo h(APP_NAME); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="shell">

    <header class="topbar">
        <div class="topbar-brand">
            <div class="topbar-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            </div>
            <h1>Gallery</h1>
        </div>
        <nav class="topbar-nav">
            <span class="queue-count"><?php echo count($rows); ?> image<?php echo count($rows) !== 1 ? 's' : ''; ?></span>
            <a class="btn btn-ghost btn-sm" href="index.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Upload
            </a>
        </nav>
    </header>

    <?php if ($error !== null): ?>
        <section class="panel">
            <p class="warn"><?php echo h($error); ?></p>
        </section>

    <?php elseif (count($rows) === 0): ?>
        <section class="panel">
            <div class="empty-state">
                <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                <p>No processed images yet.</p>
                <a class="btn btn-sm" href="index.php">Upload your first image</a>
            </div>
        </section>

    <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($rows as $row): ?>
                <?php
                $tags = json_decode((string)$row['tags'], true);
                $tags = is_array($tags) ? $tags : [];
                $date = date('M j, Y \a\t g:ia', strtotime((string)$row['created_at']));
                ?>
                <article class="gallery-card">
                    <div class="gallery-card-img">
                        <img src="<?php echo h(pathToUrl((string)$row['file_path'])); ?>" alt="Product" loading="lazy">
                    </div>
                    <div class="gallery-card-body">
                        <?php if (count($tags) > 0): ?>
                            <ul class="tag-list">
                                <?php foreach ($tags as $tag): ?>
                                    <li><?php echo h((string)$tag); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <p><?php echo h((string)$row['description']); ?></p>
                    </div>
                    <div class="gallery-card-footer">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <?php echo h($date); ?>
                        <?php if (!empty($row['user_note'])): ?>
                            &middot;
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <?php echo h((string)$row['user_note']); ?>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>
<footer class="footer">
    <p>&copy; 2024 Driss Jadidi. <a href="cgu.php">Conditions Générales d'Utilisation</a></p>
</footer>
</body>
</html>
