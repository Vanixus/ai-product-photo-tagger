<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/constants.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('DEMO_PASSWORD')) {
    define('DEMO_PASSWORD', 'demo2026');
}
if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', 'admin2026');
}

/**
 * Check whether the current session is authenticated (client or admin).
 */
function isAuthenticated(): bool
{
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['client', 'admin'], true);
}

/**
 * Check whether the current session belongs to an admin.
 */
function isAdmin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Redirect to login page if not authenticated.
 */
function requireAuth(): void
{
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirect to login page if not admin.
 */
function requireAdmin(): void
{
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Attempt login with given password. Returns role string or null on failure.
 */
function attemptLogin(string $password): ?string
{
    if ($password === '') {
        return null;
    }

    if (hash_equals(ADMIN_PASSWORD, $password)) {
        return 'admin';
    }

    if (hash_equals(DEMO_PASSWORD, $password)) {
        return 'client';
    }

    return null;
}

/**
 * Get the current coin balance from the database.
 */
function getWalletBalance(): int
{
    require_once __DIR__ . '/../config/db.php';
    $stmt = db()->query('SELECT balance FROM demo_wallet WHERE id = 1');
    $row = $stmt->fetch();
    return $row ? (int)$row['balance'] : 0;
}

/**
 * Set the coin balance in the database.
 */
function setWalletBalance(int $balance): int
{
    $balance = max(0, $balance);
    require_once __DIR__ . '/../config/db.php';
    $stmt = db()->prepare('UPDATE demo_wallet SET balance = :balance WHERE id = 1');
    $stmt->execute([':balance' => $balance]);
    return $balance;
}

/**
 * Deduct one coin from the wallet. Returns new balance or -1 if insufficient.
 */
function deductCoin(): int
{
    require_once __DIR__ . '/../config/db.php';
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->query('SELECT balance FROM demo_wallet WHERE id = 1 FOR UPDATE');
        $row = $stmt->fetch();
        $current = $row ? (int)$row['balance'] : 0;

        if ($current <= 0) {
            $pdo->rollBack();
            return -1;
        }

        $newBalance = $current - 1;
        $update = $pdo->prepare('UPDATE demo_wallet SET balance = :balance WHERE id = 1');
        $update->execute([':balance' => $newBalance]);
        $pdo->commit();
        return $newBalance;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
