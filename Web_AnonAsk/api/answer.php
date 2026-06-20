<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/rate-limit.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {

    // ============================================================
    // 回答问题（需登录，且是问题的主人）
    // POST /api/answer.php?action=create
    // Body: { "question_id": 1, "content": "回答内容" }
    // ============================================================
    case 'create':
        if ($method !== 'POST') jsonResponse(405, '方法不允许', null, 405);
        $uid = requireLogin();

        $ip = getClientIP();
        if (!checkRateLimit('uid:'.$uid, 'create_answer', RATE_LIMIT_CREATE_ANSWER, RATE_LIMIT_WINDOW))
            jsonResponse(403, '操作过于频繁', null, 429);

        $body = getJsonBody();
        if (!$body) jsonResponse(400, '请求体格式错误', null, 400);

        $questionId = (int)($body['question_id'] ?? 0);
        $content    = trim($body['content'] ?? '');

        if ($questionId <= 0) jsonResponse(400, '问题ID错误', null, 400);
        if ($content === '') jsonResponse(400, '回答内容不能为空', null, 400);
        if (mb_strlen($content) > 5000) jsonResponse(400, '回答内容不能超过5000字', null, 400);

        try {
            $db = getDB();

            // 验证问题存在且属于当前用户
            $stmt = $db->prepare('SELECT id, target_uid, status FROM questions WHERE id=? LIMIT 1');
            $stmt->execute([$questionId]);
            $q = $stmt->fetch();

            if (!$q) jsonResponse(404, '问题不存在', null, 404);
            if ((int)$q['target_uid'] !== $uid) jsonResponse(403, '无权回答此问题', null, 403);
            if ((int)$q['status'] === 0) jsonResponse(400, '此问题已回答过了', null, 400);

            // 检查是否已有回答
            $stmt = $db->prepare('SELECT id FROM answers WHERE question_id=? LIMIT 1');
            $stmt->execute([$questionId]);
            if ($stmt->fetch()) jsonResponse(400, '此问题已回答过了', null, 400);

            // 插入回答
            $stmt = $db->prepare('INSERT INTO answers (question_id, author_uid, content, ip_address) VALUES (?,?,?,?)');
            $stmt->execute([$questionId, $uid, h($content), $ip]);

            // 更新问题状态为已回答
            $stmt = $db->prepare('UPDATE questions SET status=0 WHERE id=?');
            $stmt->execute([$questionId]);

            jsonResponse(0, '回答成功');
        } catch (Exception $e) {
            jsonResponse(500, '服务器内部错误', null, 500);
        }
        break;

    // ============================================================
    // 删除回答（问题主人）
    // POST /api/answer.php?action=delete
    // Body: { "question_id": 1 }
    // ============================================================
    case 'delete':
        if ($method !== 'POST') jsonResponse(405, '方法不允许', null, 405);
        $uid = requireLogin();

        $body = getJsonBody();
        if (!$body) jsonResponse(400, '请求体格式错误', null, 400);

        $questionId = (int)($body['question_id'] ?? 0);
        if ($questionId <= 0) jsonResponse(400, '问题ID错误', null, 400);

        try {
            $db = getDB();

            // 验证问题属于当前用户
            $stmt = $db->prepare('SELECT id, target_uid FROM questions WHERE id=? LIMIT 1');
            $stmt->execute([$questionId]);
            $q = $stmt->fetch();

            if (!$q) jsonResponse(404, '问题不存在', null, 404);
            if ((int)$q['target_uid'] !== $uid) jsonResponse(403, '无权操作', null, 403);

            // 删除回答和问题
            $stmt = $db->prepare('DELETE FROM answers WHERE question_id=?');
            $stmt->execute([$questionId]);
            $stmt = $db->prepare('DELETE FROM questions WHERE id=?');
            $stmt->execute([$questionId]);

            jsonResponse(0, '已删除');
        } catch (Exception $e) {
            jsonResponse(500, '服务器内部错误', null, 500);
        }
        break;

    default:
        jsonResponse(400, '未知操作 (create / delete)', null, 400);
}
