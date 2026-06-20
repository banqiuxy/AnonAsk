# AnonAsk — 向我提问
> My first Vibe Coding project

> 一个轻量的匿名问答平台。注册后获得个人链接，发到社交平台等待您的朋友向您提问，在收件箱里一一回答。前端完全匿名，后台记录完整。

**项目状态**：开源的个人演示项目。

---

## 📱 多平台支持

| 平台 | 状态 | 技术栈 |
|------|------|--------|
| 🌐 Web 端 | ✅ 已上线 | HTML + CSS + JavaScript + PHP 7.4 + MySQL |
| 📱 Android | ✅ 已出包 | Java + Material 3 (MD3) + Retrofit + Navigation Component |
| 🪟 Windows | ✅ 已出包 | Electron + HTML/CSS/JS + electron-builder |
| 🍎 iOS | ⏳ 开发中 | Swift / SwiftUI |
| 📲 HarmonyOS | ⏳ 开发中 | ArkTS / ArkUI |
| 🐧 Linux | ⏳ 开发中 | Electron（与 Windows 共用） |

所有平台共用同一套后端 API 接口（`api/*.php`），通过 HTTP JSON 通信。

---

## 这是什么？

AnonAsk 的工作方式：

1. **注册/登录** — 用手机号 / QQ号 / 微信号 + 密码注册
2. **分享链接** — 获得个人页面 `/u.php?uid=xxxxxxxxxxxx` 分享到社交平台
3. **别人向你提问** — 您的朋友点开链接 → 登录 → 匿名向你提问
4. **你回答案** → 在收件箱里看到问题，一一作答

**前端完全匿名**：所有问题和回答的前端展示均不显示任何用户信息（提问者、回答者）。但后台数据库记录完整，开发者可查。

---

## 核心逻辑

| 角色 | 做什么 | 前端可见性 |
|------|--------|-----------|
| 链接主人（你） | 分享链接，在收件箱回答问题 | 可见问题和回答内容 |
| 提问者 | 登录后向链接主人提问 | 仅可见自己的问题和回答 |
| 浏览者 | 打开链接查看问答 | 只读，可见问题和回答内容 |

**关键设计**：
- 所有用户必须登录才能提问或回答
- 前端永不展示任何用户标识（头像、昵称、UID）
- 后台完整记录 `author_uid` / `target_uid`，开发者可查

---

## 🌐 Web 端 — 项目结构

| 维度 | 说明 |
|------|------|
| 后端 | **PHP 7.4**（原生，无框架）|
| 前端 | HTML + CSS + JavaScript（纯静态）|
| 数据库 | **MySQL 5.6**（PDO 连接）|
| Web 服务器 | Nginx |
| 会话 | PHP Session（7 天有效期）|
| 频率限制 | IP + UID 双维度 |

```
Web_AnonAsk/
├── index.php                 # 首页入口（含路由 /dashboard）
├── u.php                     # 用户公开页路由 → pages/u.php
├── sql/schema.sql            # 数据库建表脚本
├── admin/                    # 管理后台
│   ├── config.php            # 管理员账号配置
│   ├── index.php             # 管理员登录页
│   ├── dashboard.php         # 管理中心（用户/问题/回答管理）
│   └── logout.php            # 注销
│
├── api/                      # 后端 API（HTTP JSON）
│   ├── auth.php              # 注册 / 登录 / 注销 / 检查状态
│   ├── question.php          # 提问 + 查询问答列表（公开/收件箱）
│   ├── answer.php            # 回答问题 / 删除问答
│   ├── admin.php             # 管理员专用（查/增/删用户和问答）
│   └── rate-limit.php        # 频率限制
│
├── includes/
│   ├── config.php            # 数据库/限流/密码规则配置
│   ├── db.php                # PDO 数据库连接
│   └── functions.php         # 会话管理 + 工具函数
│
├── pages/
│   ├── login.php             # 登录 / 注册页
│   ├── dashboard.php         # 用户收件箱（回答/删除问题）
│   ├── u.php                 # 用户公开页（问答列表 + 提问入口）
│   └── download.php          # 各平台客户端下载页
│
└── assets/
    ├── css/style.css         # 公共样式
    └── js/app.js             # 公共 JS（认证 / API / UI 工具）
```

---

## 📱 Android 客户端 — `Android-anonask/`

| 维度 | 说明 |
|------|------|
| 语言 | **Java** |
| 最低 SDK | Android 10 (API 29) |
| UI | **Material 3 (MD3)**，XML 布局 |
| 网络 | **Retrofit** + OkHttp + Gson |
| 架构 | Navigation Component + Fragment + ViewPager2 |
| 构建 | Gradle + Version Catalog |

```
Android-anonask/
├── app/
│   ├── build.gradle              # 依赖配置（MD3 / Retrofit / Navigation 等）
│   ├── src/main/
│   │   ├── AndroidManifest.xml
│   │   ├── java/com/banqiu/anonask/
│   │   │   ├── MainActivity.java        # 唯一 Activity
│   │   │   ├── api/
│   │   │   │   ├── ApiService.java      # Retrofit 接口定义
│   │   │   │   └── RetrofitClient.java  # Retrofit 单例
│   │   │   ├── model/Models.java        # 请求/响应数据模型
│   │   │   └── fragment/
│   │   │       ├── SplashFragment.java    # 引导页
│   │   │       ├── LoginFragment.java     # 登录/注册
│   │   │       ├── MainTabsFragment.java  # 主页 Tab 容器
│   │   │       ├── HomeFragment.java      # 首页
│   │   │       ├── InboxFragment.java     # 收件箱
│   │   │       └── UserQAFragment.java   # 用户问答页
│   │   └── res/
│   │       ├── layout/                  # XML 布局
│   │       ├── drawable/                # 图标/选择器
│   │       ├── navigation/nav_graph.xml # 导航图
│   │       ├── menu/                    # 底部导航菜单
│   │       └── values/                  # 主题/字符串
│   └── proguard-rules.pro
├── gradle/
│   └── libs.versions.toml              # 版本目录
└── build.gradle.kts
```

> 后端地址在 `app/build.gradle` 的 `API_BASE_URL` 中配置。

---

## 🪟 Windows 客户端 — `Windows-anonask/`

| 维度 | 说明 |
|------|------|
| 框架 | **Electron** |
| 语言 | JavaScript（Node.js）|
| 页面 | HTML + CSS + 原生 JS |
| 打包 | electron-builder（NSIS 安装包）|
| 跨平台 | 同一套代码可构建 Windows / macOS / Linux |

```
Windows-anonask/
├── main.js              # Electron 主进程
├── preload.js           # 预加载脚本（安全隔离）
├── package.json         # 依赖 + 构建配置
├── themes.js            # 主题切换
├── renderer/            # 渲染进程（前端）
│   ├── index.html       # 主窗口
│   ├── app.js           # 业务逻辑
│   ├── style.css        # 样式
│   ├── settings.html    # 设置页
│   ├── settings.js      # 设置逻辑
│   └── settings.css     # 设置页样式
└── release/             # 构建输出
    ├── win-unpacked/    # 免安装版
    └── AnonAsk Setup 1.0.0.exe  # 安装包
```

---

## 数据库表

| 表 | 说明 |
|----|------|
| `users` | 用户（UID / 联系方式 / 密码）|
| `questions` | 问题（`target_uid` 发给谁，`author_uid` 谁问的）|
| `answers` | 回答（`question_id` 对应问题，`author_uid` 回答者）|
| `rate_limits` | 频率限制日志 |

---

## 功能范围

- ✅ 注册 / 登录（手机号 / QQ号 / 微信号 + 密码）
- ✅ 12 位纯数字 UID
- ✅ 个人链接页 `/u.php?uid={uid}`
- ✅ 向对方匿名提问（需登录）
- ✅ 收件箱回答问题
- ✅ 删除问答
- ✅ 前端完全匿名（不显示任何用户信息）
- ✅ IP + UID 双维度防刷

---

## 本地运行

1. 导入 `sql/schema.sql` 到 MySQL
2. 修改 `includes/config.php` 中的数据库连接信息
3. 配置 Nginx 指向项目根目录
4. 访问首页完成注册即可使用
