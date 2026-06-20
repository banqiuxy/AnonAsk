<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

function checkRateLimit(string $bucket, string $action, int $limit, int $window): bool {
    if (!defined('RATE_LIMIT_ENABLED') || !RATE_LIMIT_ENABLED) return true;
    try {
        $db = getDB();

        // 惰性清理
        if (mt_rand(1, 10) === 1) {
            $clean = $db->prepare('DELETE FROM rate_limits WHERE hit_at < DATE_SUB(NOW(), INTERVAL ? SECOND)');
            $clean->execute([$window * 2]);
        }

        // 记录请求
        $ins = $db->prepare('INSERT INTO rate_limits (bucket, action, hit_at) VALUES (?, ?, NOW())');
        $ins->execute([$bucket, $action]);

        // 统计
        $cnt = $db->prepare('SELECT COUNT(*) AS cnt FROM rate_limits WHERE bucket=? AND action=? AND hit_at>DATE_SUB(NOW(), INTERVAL ? SECOND)');
        $cnt->execute([$bucket, $action, $window]);
        $row = $cnt->fetch();

        return $row['cnt'] <= $limit;

    } catch (Exception $e) {
        return true;
    }
}
