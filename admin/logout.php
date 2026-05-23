<?php
/**
 * 管理员注销
 */
require_once __DIR__ . '/config.php';

adminLogout();
header('Location: /admin/index.php?logged_out=1');
exit;
