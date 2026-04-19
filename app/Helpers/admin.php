<?php

if (!function_exists('lc_admin_table_exists')) {
    function lc_admin_table_exists(PDO $conn, string $tableName): bool
    {
        try {
            $stmt = $conn->prepare('SHOW TABLES LIKE :table_name');
            $stmt->execute([':table_name' => $tableName]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    function lc_admin_column_exists(PDO $conn, string $tableName, string $columnName): bool
    {
        try {
            $stmt = $conn->prepare("SHOW COLUMNS FROM `{$tableName}` LIKE :column_name");
            $stmt->execute([':column_name' => $columnName]);
            return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    function lc_admin_ensure_schema(PDO $conn): void
    {
        static $schemaReady = false;
        if ($schemaReady) {
            return;
        }

        if (!lc_admin_column_exists($conn, 'users', 'Role')) {
            $conn->exec("ALTER TABLE users ADD COLUMN Role VARCHAR(20) NOT NULL DEFAULT 'User'");
        }

        if (!lc_admin_column_exists($conn, 'users', 'AccountStatus')) {
            $conn->exec("ALTER TABLE users ADD COLUMN AccountStatus VARCHAR(20) NOT NULL DEFAULT 'Active'");
        }

        if (!lc_admin_table_exists($conn, 'admin_reports')) {
            // Schema must match AdminModel.php (EntityType, EntityID, Details, ReviewedBy)
            $conn->exec("
                CREATE TABLE admin_reports (
                    ReportID   INT           AUTO_INCREMENT PRIMARY KEY,
                    ReporterID INT           NOT NULL,
                    EntityType VARCHAR(50)   NOT NULL DEFAULT 'post',
                    EntityID   INT           NOT NULL,
                    Reason     VARCHAR(255)  NOT NULL,
                    Details    TEXT          NULL,
                    Status     ENUM('Pending','Reviewed','Resolved','Dismissed') DEFAULT 'Pending',
                    ReviewedBy INT           NULL,
                    ReviewedAt DATETIME      NULL,
                    CreatedAt  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT fk_report_reporter FOREIGN KEY (ReporterID) REFERENCES users(UserID) ON DELETE CASCADE,
                    CONSTRAINT fk_report_reviewer FOREIGN KEY (ReviewedBy) REFERENCES users(UserID) ON DELETE SET NULL,
                    UNIQUE KEY unique_report (ReporterID, EntityType, EntityID),
                    KEY idx_admin_reports_status     (Status),
                    KEY idx_admin_reports_created_at (CreatedAt)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ");
        }

        $schemaReady = true;
    }

    function lc_admin_normalize_role(?string $role): string
    {
        $role = trim((string)$role);
        return in_array($role, ['User', 'Admin', 'SuperAdmin'], true) ? $role : 'User';
    }

    function lc_admin_normalize_status(?string $status): string
    {
        $status = trim((string)$status);
        return in_array($status, ['Active', 'Suspended', 'Banned'], true) ? $status : 'Active';
    }

    function lc_admin_sync_session(PDO $conn): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['user_id'])) {
            return;
        }

        lc_admin_ensure_schema($conn);

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        if ($userId === 1) {
            try {
                $bootstrapStmt = $conn->prepare("
                    UPDATE users
                    SET
                        Role = CASE
                            WHEN Role IS NULL OR Role = '' OR Role = 'User' THEN 'SuperAdmin'
                            ELSE Role
                        END,
                        AccountStatus = CASE
                            WHEN AccountStatus IS NULL OR AccountStatus = '' THEN 'Active'
                            ELSE AccountStatus
                        END
                    WHERE UserID = :user_id
                ");
                $bootstrapStmt->execute([':user_id' => $userId]);
            } catch (PDOException $e) {
            }
        }

        try {
            $stmt = $conn->prepare("
                SELECT UserID, Username, Email, Role, AccountStatus
                FROM users
                WHERE UserID = :user_id
                LIMIT 1
            ");
            $stmt->execute([':user_id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $user = false;
        }

        if (!$user) {
            return;
        }

        $_SESSION['username'] = (string)($user['Username'] ?? ($_SESSION['username'] ?? 'User'));
        $_SESSION['Email'] = (string)($user['Email'] ?? ($_SESSION['Email'] ?? ''));
        $_SESSION['user_role'] = $userId === 1
            ? 'SuperAdmin'
            : lc_admin_normalize_role($user['Role'] ?? 'User');
        $_SESSION['account_status'] = lc_admin_normalize_status($user['AccountStatus'] ?? 'Active');
    }

    function lc_admin_sync_session_if_possible($conn): void
    {
        if ($conn instanceof PDO) {
            lc_admin_sync_session($conn);
        }
    }

    function lc_current_user_role(): string
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId === 1) {
            return 'SuperAdmin';
        }

        return lc_admin_normalize_role($_SESSION['user_role'] ?? 'User');
    }

    function lc_current_user_is_admin(): bool
    {
        return in_array(lc_current_user_role(), ['Admin', 'SuperAdmin'], true);
    }

    function lc_admin_enforce_active_account(PDO $conn): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['user_id'])) {
            return;
        }

        lc_admin_sync_session($conn);
        $status = lc_admin_normalize_status($_SESSION['account_status'] ?? 'Active');
        if ($status === 'Active') {
            return;
        }

        session_unset();
        session_destroy();

        $redirect = defined('BASE_URL')
            ? BASE_URL . 'app/Views/Login/login_gmail.php?error=Account_' . rawurlencode($status)
            : '../Login/login_gmail.php?error=Account_' . rawurlencode($status);

        header('Location: ' . $redirect);
        exit();
    }

    function lc_admin_require_admin(PDO $conn): void
    {
        lc_admin_sync_session($conn);
        lc_admin_enforce_active_account($conn);

        if (lc_current_user_is_admin()) {
            return;
        }

        http_response_code(403);
        echo 'Access denied. Admin privileges are required.';
        exit();
    }
}
