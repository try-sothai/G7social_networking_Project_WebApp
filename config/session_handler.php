<?php
// Store PHP sessions in MySQL so they survive Render server restarts

class DbSessionHandler implements SessionHandlerInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }

    public function read(string $id): string|false
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT data FROM php_sessions WHERE session_id = :id AND expires_at > NOW() LIMIT 1"
            );
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (string)$row['data'] : '';
        } catch (PDOException $e) {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $expires = date('Y-m-d H:i:s', time() + (int)ini_get('session.gc_maxlifetime'));
            $stmt = $this->db->prepare("
                INSERT INTO php_sessions (session_id, data, expires_at)
                VALUES (:id, :data, :expires)
                ON DUPLICATE KEY UPDATE data = :data, expires_at = :expires
            ");
            $stmt->execute([':id' => $id, ':data' => $data, ':expires' => $expires]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $this->db->prepare("DELETE FROM php_sessions WHERE session_id = :id")
                ->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM php_sessions WHERE expires_at < NOW()");
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return false;
        }
    }
}

function lc_init_db_sessions(PDO $db): void
{
    // Create table if not exists
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS php_sessions (
                session_id VARCHAR(128) NOT NULL PRIMARY KEY,
                data        MEDIUMTEXT  NOT NULL,
                expires_at  DATETIME    NOT NULL,
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (PDOException $e) {
        error_log('Session table create error: ' . $e->getMessage());
        return;
    }

    $handler = new DbSessionHandler($db);
    session_set_save_handler($handler, true);
    ini_set('session.gc_maxlifetime', 86400); // 24 hours
    ini_set('session.cookie_lifetime', 86400);
}
