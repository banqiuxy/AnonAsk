<?php
/**
 * 用户公开页路由 — /u.php?uid=202400000001
 * 
 * 希望用 /u/{uid} 格式请配置 Nginx rewite:
 *   rewrite ^/u/(\d+)$ /u.php?uid=$1 last;
 */
$_GET['uid'] = (int)($_GET['uid'] ?? 0);
require __DIR__ . '/pages/u.php';
