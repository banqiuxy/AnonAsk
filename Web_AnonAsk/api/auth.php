<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/rate-limit.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {

    // ============================================================
    // 注册
    // ============================================================
    case 'register':
        if ($method !== 'POST') jsonResponse(405, '方法不允许', null, 405);

        $ip = getClientIP();
        if (!checkRateLimit('ip:'.$ip, 'register', RATE_LIMIT_REGISTER, RATE_LIMIT_WINDOW))
            jsonResponse(403, '注册过于频繁', null, 429);

        $body = getJsonBody();
        if (!$body) jsonResponse(400, '请求体格式错误', null, 400);

        $type  = trim($body['contact_type'] ?? '');
        $value = trim($body['contact_value'] ?? '');
        $pwd   = $body['password'] ?? '';

        if (!in_array($type, ['phone','qq','wechat'])) jsonResponse(400, '联系方式类型错误', null, 400);
        if ($value === '' || mb_strlen($value) > 100) jsonResponse(400, '联系方式不合法', null, 400);
        if (!isValidPassword($pwd)) jsonResponse(400, '密码需6-20位小写字母+数字', null, 400);

        try {
            $db = getDB();
            $stmt = $db->prepare('SELECT uid FROM users WHERE contact_type=? AND contact_value=? LIMIT 1');
            $stmt->execute([$type, $value]);
            if ($stmt->fetch()) jsonResponse(400, '该联系方式已注册，请登录', null, 400);

            $stmt = $db->prepare('INSERT INTO users (contact_type, contact_value, password_hash) VALUES (?,?,?)');
            $stmt->execute([$type, $value, password_hash($pwd, PASSWORD_DEFAULT)]);
            $uid = (int)$db->lastInsertId();
            setLoginSession($uid);

            jsonResponse(0, '注册成功', ['uid' => $uid]);
        } catch (Exception $e) {
            jsonResponse(500, '服务器内部错误', null, 500);
        }
        break;

    // ============================================================
    // 登录
    // ============================================================
    case 'login':
        if ($method !== 'POST') jsonResponse(405, '方法不允许', null, 405);

        $body = getJsonBody();
        if (!$body) jsonResponse(400, '请求体格式错误', null, 400);

        $type  = trim($body['contact_type'] ?? '');
        $value = trim($body['contact_value'] ?? '');
        $pwd   = $body['password'] ?? '';

        if (!in_array($type, ['phone','qq','wechat'])) jsonResponse(400, '联系方式类型错误', null, 400);
        if ($value === '') jsonResponse(400, '请输入联系方式', null, 400);
        if ($pwd === '') jsonResponse(400, '请输入密码', null, 400);

        try {
            $db = getDB();
            $stmt = $db->prepare('SELECT uid, password_hash FROM users WHERE contact_type=? AND contact_value=? LIMIT 1');
            $stmt->execute([$type, $value]);
            $user = $stmt->fetch();
            if (!$user) jsonResponse(400, '账号不存在，请先注册', null, 400);
            if (!password_verify($pwd, $user['password_hash'])) jsonResponse(400, '密码错误', null, 400);

            $stmt = $db->prepare('UPDATE users SET last_login=NOW() WHERE uid=?');
            $stmt->execute([$user['uid']]);

            setLoginSession((int)$user['uid']);
            jsonResponse(0, '登录成功', ['uid' => (int)$user['uid']]);
        } catch (Exception $e) {
            jsonResponse(500, '服务器内部错误', null, 500);
        }
        break;

    case 'logout':
        logout();
        jsonResponse(0, '已退出');
        break;

    case 'check':
        $uid = getLoginUid();
        jsonResponse(0, $uid ? '已登录' : '未登录', ['uid' => $uid]);
        break;

    default:
        jsonResponse(400, '未知操作', null, 400);
}
