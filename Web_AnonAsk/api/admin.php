<?php
/**
 * 管理后台专用 API
 *
 * 所有接口需要管理员登录
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../admin/config.php';

// 验证管理员身份
adminStartSession();
if (empty($_SESSION['admin_logged_in'])) {
    jsonResponse(401, '未登录或 Session 已过期', null, 401);
}

$action  = $_GET['action'] ?? '';
$method  = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    switch ($action) {

        // ====================================================================
        // 用户管理
        // ====================================================================
        case 'list-users':
            if ($method !== 'GET') jsonResponse(405, '方法不允许', null, 405);

            $search = trim($_GET['search'] ?? '');
            $page   = max(1, (int)($_GET['page'] ?? 1));
            $limit  = min(100, max(1, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

            $where  = '1=1';
            $params = [];
            if ($search !== '') {
                $where  = '(uid LIKE ? OR contact_type LIKE ? OR contact_value LIKE ?)';
                $like   = '%' . $search . '%';
                $params = [$like, $like, $like];
            }

            $stmt = $db->prepare("SELECT COUNT(*) AS total FROM users WHERE $where");
            $stmt->execute($params);
            $total = (int)$stmt->fetch()['total'];

            $stmt = $db->prepare(
                "SELECT uid, contact_type, contact_value, created_at, last_login
                 FROM users WHERE $where ORDER BY uid DESC LIMIT ? OFFSET ?"
            );
            $stmt->execute(array_merge($params, [$limit, $offset]));
            $items = $stmt->fetchAll();
            foreach ($items as &$item) $item['uid'] = (int)$item['uid'];

            jsonResponse(0, 'success', compact('total','page','limit','items') + ['has_more' => ($offset+$limit) < $total]);
            break;

        case 'add-user':
            if ($method !== 'POST') jsonResponse(405, '方法不允许', null, 405);
            $body = getJsonBody();
            if (!$body) jsonResponse(400, '请求体格式错误', null, 400);

            $type  = trim($body['contact_type'] ?? '');
            $value = trim($body['contact_value'] ?? '');
            $pwd   = $body['password'] ?? '';

            if (!in_array($type, ['phone','qq','wechat'])) jsonResponse(400, '联系方式类型错误', null, 400);
            if ($value === '' || mb_strlen($value) > 100) jsonResponse(400, '联系方式不合法', null, 400);
            if (!isValidPassword($pwd)) jsonResponse(400, '密码需6-20位小写字母+数字', null, 400);

            $stmt = $db->prepare('SELECT uid FROM users WHERE contact_type=? AND contact_value=? LIMIT 1');
            $stmt->execute([$type, $value]);
            if ($stmt->fetch()) jsonResponse(400, '该联系方式已存在', null, 400);

            $stmt = $db->prepare('INSERT INTO users (contact_type, contact_value, password_hash) VALUES (?,?,?)');
            $stmt->execute([$type, $value, password_hash($pwd, PASSWORD_DEFAULT)]);
            jsonResponse(0, '添加成功', ['uid' => (int)$db->lastInsertId()]);
            break;

        case 'delete-user':
            if ($method !== 'POST') jsonResponse(405, '方法不允许', null, 405);
            $body = getJsonBody();
            if (!$body) jsonResponse(400, '请求体格式错误', null, 400);

            $uid = (int)($body['uid'] ?? 0);
            if ($uid <= 0) jsonResponse(400, 'UID 错误', null, 400);

            // 级联删除：回答 → 问题 → 用户
            $db->prepare('DELETE a FROM answers a JOIN questions q ON a.question_id=q.id WHERE q.target_uid=?')->execute([$uid]);
            $db->prepare('DELETE FROM questions WHERE target_uid=?')->execute([$uid]);
            // 也删除该用户作为提问者的问题
            $db->prepare('DELETE a FROM answers a JOIN questions q ON a.question_id=q.id WHERE q.author_uid=?')->execute([$uid]);
            $db->prepare('DELETE FROM questions WHERE author_uid=?')->execute([$uid]);
            $stmt = $db->prepare('DELETE FROM users WHERE uid=?');
            $stmt->execute([$uid]);

            if ($stmt->rowCount() > 0) {
                jsonResponse(0, '用户及关联数据已删除');
            } else {
                jsonResponse(404, '用户不存在', null, 404);
            }
            break;

        // ====================================================================
        // 问题管理 — 按被提问者（target_uid）分组
        // ====================================================================
        case 'list-questions':
            if ($method !== 'GET') jsonResponse(405, '方法不允许', null, 405);

            // 获取所有有问题的用户（被提问者）
            $stmt = $db->prepare(
                "SELECT q.target_uid, u.contact_type, u.contact_value,
                        COUNT(*) AS question_count,
                        SUM(CASE WHEN q.status=1 THEN 1 ELSE 0 END) AS pending_count
                 FROM questions q
                 JOIN users u ON u.uid = q.target_uid
                 WHERE q.status >= 0
                 GROUP BY q.target_uid
                 ORDER BY q.target_uid DESC"
            );
            $stmt->execute();
            $groups = $stmt->fetchAll();

            $result = [];
            foreach ($groups as $g) {
                $uid = (int)$g['target_uid'];

                // 查该用户收到的所有问题 + 回答 + 提问者信息
                $stmt = $db->prepare(
                    "SELECT q.id, q.content AS question_content, q.author_uid AS asker_uid,
                            q.status, q.created_at AS question_time,
                            a.content AS answer_content, a.created_at AS answer_time,
                            u.contact_type AS asker_type, u.contact_value AS asker_value
                     FROM questions q
                     LEFT JOIN answers a ON a.question_id = q.id
                     LEFT JOIN users u ON u.uid = q.author_uid
                     WHERE q.target_uid = ? AND q.status >= 0
                     ORDER BY q.created_at DESC"
                );
                $stmt->execute([$uid]);
                $questions = $stmt->fetchAll();

                $qlist = [];
                foreach ($questions as $q) {
                    $qlist[] = [
                        'id'              => (int)$q['id'],
                        'content'         => h($q['question_content']),
                        'asker_uid'       => (int)$q['asker_uid'],
                        'asker_type'      => $q['asker_type'],
                        'asker_value'     => $q['asker_value'],
                        'status'          => (int)$q['status'],
                        'question_time'   => $q['question_time'],
                        'answer_content'  => $q['answer_content'] ? h($q['answer_content']) : null,
                        'answer_time'     => $q['answer_time'] ?? null,
                    ];
                }

                $result[] = [
                    'target_uid'      => $uid,
                    'target_contact'  => $g['contact_type'] . ': ' . $g['contact_value'],
                    'question_count'  => (int)$g['question_count'],
                    'pending_count'   => (int)$g['pending_count'],
                    'questions'       => $qlist,
                ];
            }

            jsonResponse(0, 'success', ['groups' => $result]);
            break;

        // ====================================================================
        // 回答管理 — 按回答者分组，显示对应问题和提问者
        // ====================================================================
        case 'list-answers':
            if ($method !== 'GET') jsonResponse(405, '方法不允许', null, 405);

            // 获取所有回答过的用户（回答者）
            $stmt = $db->prepare(
                "SELECT a.author_uid AS answerer_uid,
                        u.contact_type, u.contact_value,
                        COUNT(*) AS answer_count
                 FROM answers a
                 JOIN users u ON u.uid = a.author_uid
                 GROUP BY a.author_uid
                 ORDER BY a.author_uid DESC"
            );
            $stmt->execute();
            $groups = $stmt->fetchAll();

            $result = [];
            foreach ($groups as $g) {
                $uid = (int)$g['answerer_uid'];

                // 查该回答者的所有回答，连带问题和提问者
                $stmt = $db->prepare(
                    "SELECT a.id AS answer_id, a.content AS answer_content, a.created_at AS answer_time,
                            a.ip_address,
                            q.id AS question_id, q.content AS question_content, q.created_at AS question_time,
                            q.author_uid AS asker_uid,
                            asker.contact_type AS asker_type, asker.contact_value AS asker_value
                     FROM answers a
                     JOIN questions q ON q.id = a.question_id
                     LEFT JOIN users asker ON asker.uid = q.author_uid
                     WHERE a.author_uid = ?
                     ORDER BY a.created_at DESC"
                );
                $stmt->execute([$uid]);
                $answers = $stmt->fetchAll();

                $alist = [];
                foreach ($answers as $a) {
                    $alist[] = [
                        'answer_id'       => (int)$a['answer_id'],
                        'answer_content'  => h($a['answer_content']),
                        'answer_time'     => $a['answer_time'],
                        'ip_address'      => $a['ip_address'],
                        'question_id'     => (int)$a['question_id'],
                        'question_content' => h($a['question_content']),
                        'asker_uid'       => (int)$a['asker_uid'],
                        'asker_type'      => $a['asker_type'],
                        'asker_value'     => $a['asker_value'],
                    ];
                }

                $result[] = [
                    'answerer_uid'    => $uid,
                    'answerer_contact' => $g['contact_type'] . ': ' . $g['contact_value'],
                    'answer_count'    => (int)$g['answer_count'],
                    'answers'         => $alist,
                ];
            }

            jsonResponse(0, 'success', ['groups' => $result]);
            break;

        // ====================================================================
        // 删除问题（管理员直接删除）
        // ====================================================================
        case 'delete-question':
            if ($method !== 'POST') jsonResponse(405, '方法不允许', null, 405);
            $body = getJsonBody();
            if (!$body) jsonResponse(400, '请求体格式错误', null, 400);

            $qid = (int)($body['question_id'] ?? 0);
            if ($qid <= 0) jsonResponse(400, '问题ID错误', null, 400);

            $db->prepare('DELETE FROM answers WHERE question_id=?')->execute([$qid]);
            $stmt = $db->prepare('DELETE FROM questions WHERE id=?');
            $stmt->execute([$qid]);

            jsonResponse($stmt->rowCount() > 0 ? 0 : 404,
                         $stmt->rowCount() > 0 ? '已删除' : '问题不存在');
            break;

        // ====================================================================
        // 删除回答（管理员直接删除）
        // ====================================================================
        case 'delete-answer':
            if ($method !== 'POST') jsonResponse(405, '方法不允许', null, 405);
            $body = getJsonBody();
            if (!$body) jsonResponse(400, '请求体格式错误', null, 400);

            $aid = (int)($body['answer_id'] ?? 0);
            if ($aid <= 0) jsonResponse(400, '回答ID错误', null, 400);

            // 先查出对应的 question_id 以便更新状态
            $stmt = $db->prepare('SELECT question_id FROM answers WHERE id=?');
            $stmt->execute([$aid]);
            $ans = $stmt->fetch();

            $stmt = $db->prepare('DELETE FROM answers WHERE id=?');
            $stmt->execute([$aid]);

            if ($stmt->rowCount() > 0 && $ans) {
                $db->prepare('UPDATE questions SET status=1 WHERE id=?')->execute([$ans['question_id']]);
                jsonResponse(0, '回答已删除，问题状态已恢复为待回答');
            } else {
                jsonResponse(404, '回答不存在', null, 404);
            }
            break;

        default:
            jsonResponse(400, '未知操作', null, 400);
    }

} catch (Exception $e) {
    jsonResponse(500, '服务器内部错误: ' . $e->getMessage(), null, 500);
}
