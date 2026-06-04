<?php

function lc_remember_ensure_table(PDO $db): void
{
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS remember_tokens (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                user_id     INT NOT NULL,
                token_hash  VARCHAR(64) NOT NULL,
                expires_at  DATETIME NOT NULL,
                INDEX idx_hash (token_hash),
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (PDOException $e) {
        error_log('remember_tokens table error: ' . $e->getMessage());
    }
}

function lc_remember_create(PDO $db, int $userId): void
{
    lc_remember_ensure_table($db);

    $token   = bin2hex(random_bytes(32));
    $hash    = hash('sha256', $token);
    $expires = date('Y-m-d H:i:s', time() + 30 * 24 * 3600);

    try {
        $db->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (:uid, :hash, :exp)")
           ->execute([':uid' => $userId, ':hash' => $hash, ':exp' => $expires]);
    } catch (PDOException $e) {
        error_log('remember_tokens insert error: ' . $e->getMessage());
        return;
    }

    setcookie('s7_remember', $token, [
        'expires'  => time() + 30 * 24 * 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}

function lc_remember_check(PDO $db): ?array
{
    if (empty($_COOKIE['s7_remember'])) return null;

    $hash = hash('sha256', $_COOKIE['s7_remember']);

    try {
        $stmt = $db->prepare("
            SELECT rt.user_id, u.Username, u.Email, u.Role, u.AccountStatus
            FROM remember_tokens rt
            JOIN users u ON u.UserID = rt.user_id
            WHERE rt.token_hash = :hash AND rt.expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([':hash' => $hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (PDOException $e) {
        error_log('remember_tokens check error: ' . $e->getMessage());
        return null;
    }
}

function lc_remember_clear(PDO $db): void
{
    if (!empty($_COOKIE['s7_remember'])) {
        $hash = hash('sha256', $_COOKIE['s7_remember']);
        try {
            $db->prepare("DELETE FROM remember_tokens WHERE token_hash = :hash")
               ->execute([':hash' => $hash]);
        } catch (PDOException $e) {
            error_log('remember_tokens delete error: ' . $e->getMessage());
        }

        setcookie('s7_remember', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }
}
