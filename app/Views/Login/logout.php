<?php
require_once '../../../config/db_config.php';
require_once '../../../config/remember_me.php';
lc_remember_clear($conn);
session_destroy();
header("Location: login_gmail.php");
exit();
?>
