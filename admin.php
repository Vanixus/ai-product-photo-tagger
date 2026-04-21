<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

require_once __DIR__ . '/config/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $amount = max(0, min(999, (int)($_POST['amount'] ?? 0)));

    if ($action === 'set') {
        setWalletBalance($amount);
        $message = 'Balance set to ' . $amount . ' coins.';
        $messageType = 'ok';
    } elseif ($action === 'add') {
        $current = getWalletBalance();
        $newBalance = setWalletBalance($current + $amount);
        $message = 'Added ' . $amount . ' coins. New balance: ' . $newBalance . '.';
        $messageType = 'ok';
    } elseif ($action === 'remove') {
        $current = getWalletBalance();
        $newBalance = setWalletBalance($current - $amount);
        $message = 'Removed ' . $amount . ' coins. New balance: ' . $newBalance . '.';
        $messageType = 'ok';
    } elseif ($action === 'logout') {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

$balance = getWalletBalance();

$stmt = db()->query('SELECT COUNT(*) AS total FROM product_images');
$totalImages = (int)$stmt->fetch()['total'];

$stmt = db()->query('SELECT file_path, tags, description, created_at FROM product_images ORDER BY created_at DESC LIMIT 5');
$recentImages = $stmt->fetchAll();

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
    <title>Admin &mdash; <?php echo h(APP_NAME); ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <meta http-equiv="refresh" content="30">
</head>
<body>
<main class="shell">

    <header class="topbar">
        <div class="topbar-brand">
            <div class="topbar-icon" style="background: var(--danger);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h1>Admin Panel</h1>
        </div>
        <nav class="topbar-nav">
            <a class="btn btn-ghost btn-sm" href="index.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                App
            </a>
            <a class="btn btn-ghost btn-sm" href="gallery.php">Gallery</a>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-danger btn-sm" type="submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </button>
            </form>
        </nav>
    </header>

    <?php if ($message !== ''): ?>
        <div class="admin-alert admin-alert-<?php echo $messageType; ?>">
            <?php echo h($message); ?>
        </div>
    <?php endif; ?>

    <div class="admin-grid">

        <section class="panel admin-wallet-panel">
            <div class="panel-header">
                <h2>Coin Wallet</h2>
                <p class="lead">Manage the client's Demo Coin balance remotely.</p>
            </div>

            <div class="admin-balance-display">
                <div class="admin-balance-number" id="balance-value"><?php echo $balance; ?></div>
                <div class="admin-balance-label">Demo Coins</div>
            </div>

            <div class="divider"></div>

            <form method="POST" class="admin-coin-form">
                <div class="admin-coin-row">
                    <label for="amount">Amount</label>
                    <input id="amount" name="amount" type="number" min="1" max="999" value="5" required>
                </div>
                <div class="admin-coin-actions">
                    <button class="btn" type="submit" name="action" value="add">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        Add Coins
                    </button>
                    <button class="btn btn-danger" type="submit" name="action" value="remove">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        Remove
                    </button>
                    <button class="btn btn-ghost" type="submit" name="action" value="set">
                        Set Exact
                    </button>
                </div>
            </form>
        </section>

        <section class="panel admin-stats-panel">
            <div class="panel-header">
                <h2>Stats</h2>
            </div>

            <div class="admin-stat-cards">
                <div class="admin-stat-card">
                    <div class="admin-stat-value"><?php echo $balance; ?></div>
                    <div class="admin-stat-label">Coins Left</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-value"><?php echo $totalImages; ?></div>
                    <div class="admin-stat-label">Images Processed</div>
                </div>
            </div>

            <div class="divider"></div>

            <h3>Recent Activity</h3>
            <?php if (count($recentImages) === 0): ?>
                <p class="lead">No images processed yet.</p>
            <?php else: ?>
                <div class="admin-recent-list">
                    <?php foreach ($recentImages as $img): ?>
                        <?php
                        $tags = json_decode((string)$img['tags'], true);
                        $tags = is_array($tags) ? $tags : [];
                        $date = date('M j, g:ia', strtotime((string)$img['created_at']));
                        ?>
                        <div class="admin-recent-item">
                            <span class="admin-recent-tags"><?php echo h(implode(', ', array_slice($tags, 0, 4))); ?></span>
                            <span class="admin-recent-date"><?php echo h($date); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>

</main>
<footer class="footer">
    <p>&copy; 2024 Driss Jadidi. <a href="cgu.php">Conditions Générales d'Utilisation</a></p>
</footer>
</body>
</html>
