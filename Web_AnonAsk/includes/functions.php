<?php
// ==================== 会话 & 认证 ====================

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function getLoginUid(): ?int {
    startSession();
    return $_SESSION['uid'] ?? null;
}

function requireLogin(): int {
    $uid = getLoginUid();
    if (!$uid) jsonResponse(401, '请先登录', null, 401);
    return $uid;
}

function setLoginSession(int $uid): void {
    startSession();
    $_SESSION['uid'] = $uid;
}

function logout(): void {
    startSession();
    unset($_SESSION['uid']);
    session_destroy();
}

// ==================== 通用工具 ====================

function jsonResponse(int $code, string $msg, $data = null, int $httpStatus = 200): void {
    http_response_code($httpStatus);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['code' => $code, 'msg' => $msg, 'data' => $data],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getClientIP(): string {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function h(?string $value): string {
    return $value === null ? '' : htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function isValidPassword(string $password): bool {
    $len = mb_strlen($password);
    return $len >= PASSWORD_MIN_LENGTH && $len <= PASSWORD_MAX_LENGTH && preg_match('/^[a-z0-9]+$/', $password) === 1;
}

function isValidUid($uid): bool {
    return $uid !== null && preg_match('/^\d{12,}$/', (string)$uid);
}

function getJsonBody(): ?array {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') return null;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}
