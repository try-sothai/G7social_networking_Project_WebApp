<?php
session_start();
header('Content-Type: application/json');

require_once '../../../config/db_config.php';
require_once '../../Helpers/functions.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!lc_current_user_is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit();
}

$today = date('Y-m-d');

$from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'] ?? '') ? $_GET['from'] : date('Y-m-d', strtotime('-29 days'));
$to   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']   ?? '') ? $_GET['to']   : $today;

if ($to   > $today) $to   = $today;
if ($from > $to)    $from = $to;

$fromDt = new DateTime($from);
$toDt   = new DateTime($to);
if ($fromDt->diff($toDt)->days > 365) {
    $fromDt = (clone $toDt)->modify('-365 days');
    $from   = $fromDt->format('Y-m-d');
}

try {
    $stmt = $conn->prepare("
        SELECT DATE(i.CreatedAt) AS date,
            SUM(CASE WHEN i.Type IN ('like','love','haha') THEN 1 ELSE 0 END) AS likes,
            SUM(CASE WHEN i.Type = 'comment'              THEN 1 ELSE 0 END) AS comments
        FROM interactions i
        WHERE DATE(i.CreatedAt) BETWEEN ? AND ?
        GROUP BY DATE(i.CreatedAt)
    ");
    $stmt->execute([$from, $to]);

    $byDate = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $byDate[$row['date']] = ['likes' => (int)$row['likes'], 'comments' => (int)$row['comments']];
    }

    $trend = [];
    $cur   = clone $fromDt;
    while ($cur <= $toDt) {
        $d       = $cur->format('Y-m-d');
        $trend[] = ['date' => $d, 'likes' => $byDate[$d]['likes'] ?? 0, 'comments' => $byDate[$d]['comments'] ?? 0];
        $cur->modify('+1 day');
    }

    echo json_encode($trend);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
