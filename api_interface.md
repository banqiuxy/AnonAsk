# AnonAsk API 接口文档

> 本文档说明 AnonAsk 后端所有公开 API 接口，方便客户端（Android / iOS / Windows / HarmonyOS / Linux）及第三方开发者对接。

---

## 通用约定

### 基础地址

```
http://192.168.2.111/api/
```

### 请求方式

| 方法 | 用途 |
|------|------|
| `GET` | 查询数据 |
| `POST` | 提交 / 修改数据（JSON body）|

### 鉴权方式

使用 **PHP 会话（Session）** 鉴权。客户端需要：

1. 调用 `/api/auth.php?action=login` 或 `action=register` 登录 / 注册
2. 登录成功后服务端会在响应头 `Set-Cookie` 中下发 `PHPSESSID`
3. 后续请求请在 HTTP 头中附带 `Cookie: PHPSESSID=xxx`

> 如果不能用 Cookie，也可以实现 Token 机制——但目前版本仅支持 Session。
> Session 有效期 7 天。

### 通用返回格式

所有接口统一返回 JSON：

```json
{
    "code": 0,
    "msg": "操作成功",
    "data": { ... }
}
```

| 字段 | 类型 | 说明 |
|------|------|------|
| `code` | int | `0`=成功，其他=失败 |
| `msg` | string | 提示信息 |
| `data` | object/null | 返回数据 |

### HTTP 状态码

| 状态码 | 含义 |
|--------|------|
| 200 | 请求成功 |
| 400 | 参数错误 / 业务逻辑错误 |
| 401 | 未登录 |
| 403 | 无权限 / 频率限制 |
| 404 | 资源不存在 |
| 405 | 请求方法不允许 |
| 429 | 请求过于频繁（限流）|
| 500 | 服务器内部错误 |

---

## 一、认证 API — `api/auth.php`

### 1.1 注册

```
POST /api/auth.php?action=register
```

**请求体（JSON）：**

```json
{
    "contact_type": "phone",
    "contact_value": "13800138000",
    "password": "mypassword123"
}
```

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `contact_type` | string | ✅ | 联系方式类型：`phone` / `qq` / `wechat` |
| `contact_value` | string | ✅ | 手机号 / QQ号 / 微信号（≤100字符）|
| `password` | string | ✅ | 密码：6-20位，只能包含小写字母和数字 |

**成功响应：**

```json
{
    "code": 0,
    "msg": "注册成功",
    "data": {
        "uid": 202400000001
    }
}
```

**说明：**
- 注册成功后自动登录，下发 Session
- 同 IP 每 10 分钟最多注册 3 次
- 联系方式类型+值不能重复

---

### 1.2 登录

```
POST /api/auth.php?action=login
```

**请求体（JSON）：**

```json
{
    "contact_type": "phone",
    "contact_value": "13800138000",
    "password": "mypassword123"
}
```

参数同上。

**成功响应：**

```json
{
    "code": 0,
    "msg": "登录成功",
    "data": {
        "uid": 202400000001
    }
}
```

---

### 1.3 注销

```
POST /api/auth.php?action=logout
```

无请求体。清除当前 Session。

**响应：**

```json
{
    "code": 0,
    "msg": "已退出"
}
```

---

### 1.4 检查登录状态

```
GET /api/auth.php?action=check
```

**响应（已登录）：**

```json
{
    "code": 0,
    "msg": "已登录",
    "data": {
        "uid": 202400000001
    }
}
```

**响应（未登录）：**

```json
{
    "code": 0,
    "msg": "未登录",
    "data": {
        "uid": null
    }
}
```

---

## 二、问题 API — `api/question.php`

### 2.1 向某人提问（需登录）

```
POST /api/question.php?action=create
```

**请求体（JSON）：**

```json
{
    "target_uid": 202400000001,
    "content": "你是怎么学会编程的？"
}
```

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `target_uid` | int | ✅ | 被提问者的 UID |
| `content` | string | ✅ | 问题内容（≤2000字）|

**说明：**
- 当前登录用户向 `target_uid` 用户提问
- 不能向自己提问
- 同用户每 10 分钟最多提 10 个问题

**成功响应：**

```json
{
    "code": 0,
    "msg": "提问成功",
    "data": {
        "question_id": 42
    }
}
```

---

### 2.2 获取某用户收到的问答（公开，无需登录）

```
GET /api/question.php?action=list-for-user&uid=202400000001
```

**查询参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `uid` | int | ✅ | 目标用户的 UID |

**响应：**

```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "items": [
            {
                "id": 42,
                "question_content": "你是怎么学会编程的？",
                "question_time": "2026-05-17 15:30:00",
                "answer_content": "自学，多看官方文档和开源项目。",
                "answer_time": "2026-05-17 16:00:00",
                "is_answered": true
            }
        ]
    }
}
```

**说明：**
- 最多返回最近 50 条
- `answer_content` 为 `null` 表示尚未回答
- 前端展示时 **不应暴露任何用户信息**

---

### 2.3 获取我收到的问题（需登录）

```
GET /api/question.php?action=list-for-me&page=1&limit=20&filter=all
```

**查询参数：**

| 参数 | 类型 | 必填 | 默认 | 说明 |
|------|------|------|------|------|
| `page` | int | 否 | 1 | 页码 |
| `limit` | int | 否 | 20 | 每页条数（1-50）|
| `filter` | string | 否 | `all` | 筛选：`all` / `pending`（待回答）/ `answered`（已回答）|

**响应：**

```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "total": 5,
        "page": 1,
        "limit": 20,
        "has_more": false,
        "items": [
            {
                "id": 42,
                "content": "你是怎么学会编程的？",
                "status": 1,
                "created_at": "2026-05-17 15:30:00",
                "answer_content": null,
                "answer_time": null
            }
        ]
    }
}
```

**`status` 说明：**
- `1` — 待回答（pending）
- `0` — 已回答（answered）
- `-1` — 已删除（不会被返回）

---

## 三、回答 API — `api/answer.php`

### 3.1 回答问题（需登录，且必须是问题的主人）

```
POST /api/answer.php?action=create
```

**请求体（JSON）：**

```json
{
    "question_id": 42,
    "content": "自学，多看官方文档和开源项目。"
}
```

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `question_id` | int | ✅ | 要回答的问题 ID |
| `content` | string | ✅ | 回答内容（≤5000字）|

**说明：**
- 只能回答 **别人向自己提的** 问题
- 一个问题只能回答一次
- 回答后问题状态自动变为 `answered`
- 同用户每 10 分钟最多回答 20 次

**成功响应：**

```json
{
    "code": 0,
    "msg": "回答成功"
}
```

---

### 3.2 删除问答（需登录，且必须是问题主人）

```
POST /api/answer.php?action=delete
```

**请求体（JSON）：**

```json
{
    "question_id": 42
}
```

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `question_id` | int | ✅ | 要删除的问题 ID |

**说明：**
- 会同时删除问题和对应的回答
- 只能删除自己收到的问题

**成功响应：**

```json
{
    "code": 0,
    "msg": "已删除"
}
```

---

## 四、错误码速查

| code | 说明 |
|------|------|
| 0 | 成功 |
| 400 | 参数错误 / 业务逻辑错误 |
| 401 | 未登录，需先调用 `auth.php` 登录 |
| 403 | 权限不足或操作过于频繁 |
| 404 | 用户/问题不存在或已删除 |
| 405 | 请求方法不对（用了 GET 而接口需要 POST，反之亦然）|
| 429 | 频率限制触发，请稍后再试 |
| 500 | 服务器内部错误 |

---

## 五、频率限制

| 动作 | 窗口 | 上限 |
|------|------|------|
| 注册 | 10 分钟 | 3 次 / IP |
| 提问 | 10 分钟 | 10 次 / 用户 + IP |
| 回答 | 10 分钟 | 20 次 / 用户 + IP |

双维度限流（UID + IP），任一维度超过上限即被限流。

---

## 六、数据模型

### 用户表（users）

| 字段 | 类型 | 说明 |
|------|------|------|
| `uid` | BIGINT | 12位纯数字主键，自增起始 202400000001 |
| `contact_type` | ENUM | `phone` / `qq` / `wechat` |
| `contact_value` | VARCHAR(100) | 联系方式值 |
| `password_hash` | VARCHAR(255) | bcrypt 密码哈希 |
| `created_at` | DATETIME | 注册时间 |

### 问题表（questions）

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | BIGINT | 自增主键 |
| `target_uid` | BIGINT | 问题发给谁（链接主人）|
| `author_uid` | BIGINT | 谁问的（前端不展示）|
| `content` | TEXT | 问题内容 |
| `status` | TINYINT | `1`=待回答，`0`=已回答，`-1`=已删除 |
| `created_at` | DATETIME | 提问时间 |

### 回答表（answers）

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | BIGINT | 自增主键 |
| `question_id` | BIGINT | 对应问题 ID（唯一约束，一个问题只能一条回答）|
| `author_uid` | BIGINT | 回答者（链接主人，前端不展示）|
| `content` | TEXT | 回答内容 |
| `ip_address` | VARCHAR(45) | 回答者 IP（仅风控）|
| `created_at` | DATETIME | 回答时间 |

---

## 七、常见对接示例

### 注册 + 登录流程（客户端）

```
1. POST /api/auth.php?action=register
   Body: { contact_type, contact_value, password }
   → 返回 uid，服务端下发 Session（PHPSESSID Cookie）

2. 保存 Session，后续所有请求附带 Cookie
```

### 获取某人问答列表并展示

```
1. GET /api/question.php?action=list-for-user&uid=202400000001
   → 返回问题列表

2. 前端渲染：显示 question_content，如有 answer_content 也显示
   不显示任何用户信息（不显示提问者、回答者）
```

### 收件箱工作流

```
1. GET /api/question.php?action=list-for-me&filter=pending
   → 获取待回答的问题列表

2. 用户点击回答 → POST /api/answer.php?action=create
   Body: { question_id, content }
   → 回答成功

3. 刷新列表 → 问题变为 answered 状态
```

---

*文档版本：v1 · 最后更新：2026-05-17*
