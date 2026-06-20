<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/rate-limit.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {

    // ============================================================
    // 向某人提问（需登录）
    // POST /api/question.php?action=create
    // Body: { "target_uid": 202400000001, "content": "你的问题" }
    // ============================================================
    case 'create':
        if ($method !== 'POST') jsonResponse(405, '方法不允许', null, 405);
        $uid = requireLogin();

        $ip = getClientIP();
        if (!checkRateLimit('uid:'.$uid, 'create_question', RATE_LIMIT_CREATE_QUESTION, RATE_LIMIT_WINDOW))
            jsonResponse(403, '操作过于频繁', null, 429);

        $body = getJsonBody();
        if (!$body) jsonResponse(400, '请求体格式错误', null, 400);

        $targetUid = (int)($body['target_uid'] ?? 0);
        $content   = trim($body['content'] ?? '');

        if ($targetUid <= 0) jsonResponse(400, '目标用户ID错误', null, 400);
        if ($uid === $targetUid) jsonResponse(400, '不能向自己提问', null, 400);
        if ($content === '') jsonResponse(400, '问题内容不能为空', null, 400);
        if (mb_strlen($content) > 2000) jsonResponse(400, '问题内容不能超过2000字', null, 400);

        try {
            $db = getDB();
            // 验证目标用户存在
            $stmt = $db->prepare('SELECT uid FROM users WHERE uid=? LIMIT 1');
            $stmt->execute([$targetUid]);
            if (!$stmt->fetch()) jsonResponse(404, '用户不存在', null, 404);

            $stmt = $db->prepare('INSERT INTO questions (target_uid, author_uid, content) VALUES (?,?,?)');
            $stmt->execute([$targetUid, $uid, h($content)]);

            jsonResponse(0, '提问成功', ['question_id' => (int)$db->lastInsertId()]);
        } catch (Exception $e) {
            jsonResponse(500, '服务器内部错误', null, 500);
        }
        break;

    // ============================================================
    // 获取某用户收到的问答（公开，无需登录）
    // GET /api/question.php?action=list-for-user&uid=202400000001
    // ============================================================
    case 'list-for-user':
        if ($method !== 'GET') jsonResponse(405, '方法不允许', null, 405);

        $targetUid = (int)($_GET['uid'] ?? 0);
        if ($targetUid <= 0) jsonResponse(400, '用户ID错误', null, 400);

        try {
            $db = getDB();

            // 查所有已回答的问题（含回答内容）
            $stmt = $db->prepare(
                'SELECT q.id, q.content AS question_content, q.created_at AS question_time,
                        a.content AS answer_content, a.created_at AS answer_time
                 FROM questions q
                 LEFT JOIN answers a ON a.question_id = q.id
                 WHERE q.target_uid = ? AND q.status >= 0
                 ORDER BY q.created_at DESC
                 LIMIT 50'
            );
            $stmt->execute([$targetUid]);
            $items = $stmt->fetchAll();

            $list = [];
            foreach ($items as $row) {
                $list[] = [
                    'id'              => (int)$row['id'],
                    'question_content' => h($row['question_content']),
                    'question_time'   => $row['question_time'],
                    'answer_content'  => $row['answer_content'] ? h($row['answer_content']) : null,
                    'answer_time'     => $row['answer_time'] ?? null,
                    'is_answered'     => $row['answer_content'] !== null,
                ];
            }

            jsonResponse(0, 'success', ['items' => $list]);
        } catch (Exception $e) {
            jsonResponse(500, '服务器内部错误', null, 500);
        }
        break;

    // ============================================================
    // 获取我收到的待回答问题（仪表盘，需登录）
    // GET /api/question.php?action=list-for-me
    // ============================================================
    case 'list-for-me':
        if ($method !== 'GET') jsonResponse(405, '方法不允许', null, 405);
        $uid = requireLogin();

        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $filter = $_GET['filter'] ?? 'all'; // all / pending / answered

        try {
            $db = getDB();

            $where = 'q.target_uid = ? AND q.status >= 0';
            $params = [$uid];

            if ($filter === 'pending') {
                $where .= ' AND q.status = 1';
            } elseif ($filter === 'answered') {
                $where .= ' AND q.status = 0';
            }

            $stmt = $db->prepare("SELECT COUNT(*) AS total FROM questions q WHERE $where");
            $stmt->execute($params);
            $total = (int)$stmt->fetch()['total'];

            $stmt = $db->prepare(
                "SELECT q.id, q.content, q.status, q.created_at,
                        a.content AS answer_content, a.created_at AS answer_time
                 FROM questions q
                 LEFT JOIN answers a ON a.question_id = q.id
                 WHERE $where
                 ORDER BY q.created_at DESC
                 LIMIT ? OFFSET ?"
            );
            $allParams = array_merge($params, [$limit, $offset]);
            $stmt->execute($allParams);
            $rows = $stmt->fetchAll();

            $items = [];
            foreach ($rows as $row) {
                $items[] = [
                    'id'             => (int)$row['id'],
                    'content'        => h($row['content']),
                    'status'         => (int)$row['status'],
                    'created_at'     => $row['created_at'],
                    'answer_content' => $row['answer_content'] ? h($row['answer_content']) : null,
                    'answer_time'    => $row['answer_time'] ?? null,
                ];
            }

            jsonResponse(0, 'success', [
                'total'    => $total,
                'page'     => $page,
                'limit'    => $limit,
                'has_more' => ($offset + $limit) < $total,
                'items'    => $items,
            ]);
        } catch (Exception $e) {
            jsonResponse(500, '服务器内部错误', null, 500);
        }
        break;

    default:
        jsonResponse(400, '未知操作 (create / list-for-user / list-for-me)', null, 400);
}
