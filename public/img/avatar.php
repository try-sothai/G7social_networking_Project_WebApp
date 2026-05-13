<?php
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');

$name = trim($_GET['name'] ?? 'U');
$bg = preg_replace('/[^a-fA-F0-9]/', '', $_GET['background'] ?? 'dbeafe');
$color = preg_replace('/[^a-fA-F0-9]/', '', $_GET['color'] ?? '1d4ed8');

if (strlen($bg) !== 6) $bg = 'dbeafe';
if (strlen($color) !== 6) $color = '1d4ed8';

$words = preg_split('/\s+/', $name);
$initials = '';
foreach (array_slice($words, 0, 2) as $w) {
    if ($w !== '') $initials .= mb_strtoupper(mb_substr($w, 0, 1));
}
if ($initials === '') $initials = 'U';

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">
  <rect width="100" height="100" fill="#<?= htmlspecialchars($bg) ?>"/>
  <text x="50" y="50" dy=".35em" text-anchor="middle" font-family="Arial, sans-serif" font-size="<?= strlen($initials) > 1 ? 38 : 44 ?>" font-weight="700" fill="#<?= htmlspecialchars($color) ?>"><?= htmlspecialchars($initials) ?></text>
</svg>
