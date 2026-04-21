<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)($_POST['password'] ?? '');
    $role = attemptLogin($password);

    if ($role !== null) {
        $_SESSION['role'] = $role;
        $destination = $role === 'admin' ? 'admin.php' : 'index.php';
        header('Location: ' . $destination);
        exit;
    }

    $error = 'Invalid password. Please try again.';
}

if (isAuthenticated()) {
    $dest = isAdmin() ? 'admin.php' : 'index.php';
    header('Location: ' . $dest);
    exit;
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
    <title>Sign In &mdash; <?php echo h(APP_NAME); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="shell login-shell">

    <div class="login-card">
        <div class="login-header">
            <div class="topbar-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
            </div>
            <h1><?php echo h(APP_NAME); ?></h1>
            <p class="lead">Enter your password to access the demo.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="login-error">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <?php echo h($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter access password" required autofocus>
            <button class="btn btn-full" type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Sign In
            </button>
        </form>
    </div>

</main>
<footer class="footer">
    <p>&copy; 2024 Driss Jadidi. <a href="cgu.php">Conditions Générales d'Utilisation</a></p>
</footer>
</body>
</html>
