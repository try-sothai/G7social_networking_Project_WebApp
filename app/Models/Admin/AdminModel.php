<?php

require_once dirname(__DIR__, 2) . '/Helpers/admin.php';

class AdminModel
{
    private PDO $db;

    public function __construct(PDO $conn)
    {
        $this->db = $conn;
        lc_admin_ensure_schema($this->db);
    }

    public function getOverviewStats(): array
    {
        $stats = [
            'total_users' => 0,
            'active_users' => 0,
            'admins' => 0,
            'restricted_users' => 0,
            'pending_reports' => 0,
            'resolved_reports' => 0,
        ];

        try {
            $stats['total_users'] = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $stats['active_users'] = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE AccountStatus = 'Active'")->fetchColumn();
            $stats['admins'] = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE Role IN ('Admin', 'SuperAdmin')")->fetchColumn();
            $stats['restricted_users'] = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE AccountStatus IN ('Suspended', 'Banned')")->fetchColumn();
            $stats['pending_reports'] = (int)$this->db->query("SELECT COUNT(*) FROM admin_reports WHERE Status = 'Pending'")->fetchColumn();
            $stats['resolved_reports'] = (int)$this->db->query("SELECT COUNT(*) FROM admin_reports WHERE Status = 'Resolved'")->fetchColumn();
        } catch (PDOException $e) {
        }

        return $stats;
    }

    public function getUsers(string $search = '', string $roleFilter = '', string $statusFilter = ''): array
    {
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(Username LIKE :search OR Email LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if (in_array($roleFilter, ['User', 'Admin', 'SuperAdmin'], true)) {
            $where[] = 'Role = :role_filter';
            $params[':role_filter'] = $roleFilter;
        }

        if (in_array($statusFilter, ['Active', 'Suspended', 'Banned'], true)) {
            $where[] = 'AccountStatus = :status_filter';
            $params[':status_filter'] = $statusFilter;
        }

        $sql = "
            SELECT
                UserID,
                Username,
                Email,
                ProfilePic,
                CASE WHEN UserID = 1 THEN 'SuperAdmin' ELSE Role END AS Role,
                AccountStatus,
                CreatedAt
            FROM users
        ";

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= "
            ORDER BY
                FIELD(Role, 'SuperAdmin', 'Admin', 'User'),
                FIELD(AccountStatus, 'Active', 'Suspended', 'Banned'),
                UserID DESC
            LIMIT 150
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getReports(string $statusFilter = '', string $search = ''): array
    {
        $where = [];
        $params = [];

        if (in_array($statusFilter, ['Pending', 'Reviewed', 'Resolved', 'Dismissed'], true)) {
            $where[] = 'r.Status = :status_filter';
            $params[':status_filter'] = $statusFilter;
        }

        if ($search !== '') {
            $where[] = '(
                reporter.Username LIKE :search
                OR COALESCE(target_user.Username, "") LIKE :search
                OR r.Reason LIKE :search
                OR COALESCE(r.Details, "") LIKE :search
                OR COALESCE(p.Content, "") LIKE :search
            )';
            $params[':search'] = '%' . $search . '%';
        }

        $sql = "
            SELECT
                r.ReportID,
                r.EntityType,
                r.EntityID,
                r.Reason,
                r.Details,
                r.Status,
                r.CreatedAt,
                r.ReviewedAt,
                reporter.UserID AS ReporterID,
                reporter.Username AS ReporterName,
                reporter.Email AS ReporterEmail,
                reviewer.Username AS ReviewerName,
                p.PostID,
                p.Content AS PostContent,
                target_user.UserID AS TargetUserID,
                target_user.Username AS TargetUserName
            FROM admin_reports r
            INNER JOIN users reporter ON reporter.UserID = r.ReporterID
            LEFT JOIN posts p ON r.EntityType = 'post' AND p.PostID = r.EntityID
            LEFT JOIN users target_user ON target_user.UserID = p.UserID
            LEFT JOIN users reviewer ON reviewer.UserID = r.ReviewedBy
        ";

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY CASE WHEN r.Status = "Pending" THEN 0 ELSE 1 END, r.CreatedAt DESC LIMIT 150';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createPostReport(int $reporterId, int $postId, string $reason, string $details = ''): array
    {
        $reason = trim($reason);
        $details = trim($details);

        if ($reporterId <= 0 || $postId <= 0 || $reason === '') {
            return ['success' => false, 'message' => 'Invalid report data.'];
        }

        $postStmt = $this->db->prepare("SELECT PostID, UserID FROM posts WHERE PostID = :post_id LIMIT 1");
        $postStmt->execute([':post_id' => $postId]);
        $post = $postStmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            return ['success' => false, 'message' => 'Post not found.'];
        }

        if ((int)$post['UserID'] === $reporterId) {
            return ['success' => false, 'message' => 'You cannot report your own post.'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO admin_reports (ReporterID, EntityType, EntityID, Reason, Details, Status, ReviewedBy, ReviewedAt, CreatedAt)
            VALUES (:reporter_id, 'post', :entity_id, :reason, :details, 'Pending', NULL, NULL, NOW())
            ON DUPLICATE KEY UPDATE
                Reason = VALUES(Reason),
                Details = VALUES(Details),
                Status = 'Pending',
                ReviewedBy = NULL,
                ReviewedAt = NULL,
                CreatedAt = NOW()
        ");

        $stmt->execute([
            ':reporter_id' => $reporterId,
            ':entity_id' => $postId,
            ':reason' => mb_substr($reason, 0, 120, 'UTF-8'),
            ':details' => $details === '' ? null : mb_substr($details, 0, 1000, 'UTF-8'),
        ]);

        return ['success' => true, 'message' => 'Report submitted successfully.'];
    }

    public function updateUserRole(int $actorId, int $targetId, string $newRole): array
    {
        $newRole = lc_admin_normalize_role($newRole);
        return $this->manageUser($actorId, $targetId, ['role' => $newRole]);
    }

    public function updateUserStatus(int $actorId, int $targetId, string $newStatus): array
    {
        $newStatus = lc_admin_normalize_status($newStatus);
        return $this->manageUser($actorId, $targetId, ['status' => $newStatus]);
    }

    public function updateReportStatus(int $actorId, int $reportId, string $newStatus): array
    {
        $newStatus = trim($newStatus);
        if (!in_array($newStatus, ['Pending', 'Reviewed', 'Resolved', 'Dismissed'], true)) {
            return ['success' => false, 'message' => 'Invalid report status.'];
        }

        $actor = $this->getUserById($actorId);
        if (!$actor || !in_array($actor['Role'], ['Admin', 'SuperAdmin'], true)) {
            return ['success' => false, 'message' => 'Unauthorized action.'];
        }

        $stmt = $this->db->prepare("
            UPDATE admin_reports
            SET Status = :status, ReviewedBy = :reviewed_by, ReviewedAt = NOW()
            WHERE ReportID = :report_id
        ");
        $stmt->execute([
            ':status' => $newStatus,
            ':reviewed_by' => $actorId,
            ':report_id' => $reportId,
        ]);

        return ['success' => $stmt->rowCount() > 0, 'message' => 'Report updated successfully.'];
    }

    private function manageUser(int $actorId, int $targetId, array $changes): array
    {
        if ($actorId <= 0 || $targetId <= 0) {
            return ['success' => false, 'message' => 'Invalid user selection.'];
        }

        if ($actorId === $targetId) {
            return ['success' => false, 'message' => 'You cannot modify your own admin access or account status here.'];
        }

        $actor = $this->getUserById($actorId);
        $target = $this->getUserById($targetId);

        if (!$actor || !$target) {
            return ['success' => false, 'message' => 'User record not found.'];
        }

        $actorRole = lc_admin_normalize_role($actor['Role'] ?? 'User');
        $targetRole = lc_admin_normalize_role($target['Role'] ?? 'User');

        if (!in_array($actorRole, ['Admin', 'SuperAdmin'], true)) {
            return ['success' => false, 'message' => 'Unauthorized action.'];
        }

        if ($targetRole === 'SuperAdmin') {
            return ['success' => false, 'message' => 'SuperAdmin accounts cannot be modified here.'];
        }

        if ($actorRole !== 'SuperAdmin' && $targetRole === 'Admin') {
            return ['success' => false, 'message' => 'Only a SuperAdmin can manage other admins.'];
        }

        if ($actorRole !== 'SuperAdmin' && isset($changes['role']) && $changes['role'] !== 'User') {
            return ['success' => false, 'message' => 'Only a SuperAdmin can promote users to admin roles.'];
        }

        $sets = [];
        $params = [':user_id' => $targetId];

        if (isset($changes['role'])) {
            $sets[] = 'Role = :role';
            $params[':role'] = $changes['role'];
        }

        if (isset($changes['status'])) {
            $sets[] = 'AccountStatus = :account_status';
            $params[':account_status'] = $changes['status'];
        }

        if ($sets === []) {
            return ['success' => false, 'message' => 'No changes requested.'];
        }

        $stmt = $this->db->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE UserID = :user_id');
        $stmt->execute($params);

        return ['success' => true, 'message' => 'User updated successfully.'];
    }

    private function getUserById(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT UserID, Username, Email, Role, AccountStatus
            FROM users
            WHERE UserID = :user_id
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return null;
        }

        $user['Role'] = $userId === 1 ? 'SuperAdmin' : lc_admin_normalize_role($user['Role'] ?? 'User');
        $user['AccountStatus'] = lc_admin_normalize_status($user['AccountStatus'] ?? 'Active');
        return $user;
    }
}
