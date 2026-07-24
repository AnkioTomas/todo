# Todo 待办管理系统

> **带 MCP 接口的 Web 端待办列表**

一个简洁的个人待办系统：多列表、智能视图（我的一天 / 重要 / 已计划）、任务详情与备注，并通过 **MCP（Model Context Protocol）** 把同一套业务能力暴露给 AI 客户端（Cursor / Claude Desktop 等），让 Agent 直接读写你的待办。

---

## 功能概览

- **多列表管理**：新建 / 重命名 / 删除列表；默认列表不可删
- **智能视图**：我的一天、重要、已计划、指定列表
- **任务能力**：标题、备注、截止日期、重要标记、加入「我的一天」、完成 / 取消完成
- **详情面板**：点选任务后侧边编辑，改动即时保存
- **MCP 接口**：一键复制 MCP 配置，Bearer Token 鉴权；工具覆盖列表与任务的增删改查 / 完成
- **Web 安装向导**：首次部署通过浏览器填表完成配置，无需手动改配置文件
- **登录与账号**：安装自动生成 admin；支持修改密码；可选 OIDC SSO

---

## 系统要求

| 组件 | 版本 / 说明 |
| --- | --- |
| PHP | >= 8.3，需要 `mbstring`、`pdo`、`curl`、`gd`；MySQL 部署另需 `pdo_mysql`，SQLite 部署另需 `pdo_sqlite` |
| 数据库 | **SQLite**（默认）或 **MySQL >= 5.7 / MariaDB >= 10.2**（`utf8mb4`） |
| Web 服务器 | Nginx 或 Apache（必须支持 URL 重写） |

---

## 部署形态

### 必需

| 服务 | 用途 |
| --- | --- |
| SQLite 或 MySQL / MariaDB | 业务数据库 |
| PHP >= 8.3 | 运行后端 |
| Nginx / Apache | Web 服务器，需 URL 重写 |

---

## 通用安装步骤

### 1. 获取代码

```bash
git clone https://github.com/AnkioTomas/todo.git todo
cd todo
git submodule update --init --recursive
```

### 2. 准备数据库

- **SQLite（默认）**：无需预建库，安装向导会自动生成。
- **MySQL / MariaDB**：事先建空库 + 账号：

```sql
CREATE DATABASE `todo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'todo'@'%' IDENTIFIED BY '改成你自己的强密码';
GRANT ALL PRIVILEGES ON `todo`.* TO 'todo'@'%';
FLUSH PRIVILEGES;
```

> 不需要手工建表，系统首次使用会自动建表并升级 schema。

### 3. 准备配置文件

```bash
cp src/example.config.php src/config.php
```

> `config.php` 已被 `.gitignore` 排除。后续配置由安装向导自动写入。

### 4. 配置 Nginx

工作目录指向 `src/public`，并加 URL 重写：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/todo/src/public;
    index index.php;

    location / {
        rewrite ^(.*)$ /index.php/$1 last;
    }

    location ~ \.php(/|$) {
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        include        fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param  PATH_INFO       $fastcgi_path_info;
    }
}
```

确保 `src/runtime` 目录对 PHP 进程可写（缓存、日志、初始密码都写这里）。

### 5. 运行安装向导

打开 `http://your-domain.com`，系统检测到未安装会自动跳转到安装页面。

在安装向导中填写：

- **数据库类型**：SQLite（默认）或 MySQL / MariaDB
- **数据库连接**：选 MySQL 时填主机、端口、账号、密码、库名；选 SQLite 时可忽略主机账号
- **系统名称**：显示在页面标题的名称

提交后系统会：

1. 写入配置到 `config.php`
2. 自动建表并生成管理员账号
3. 返回初始管理员用户名和密码

用返回的凭据登录即可。

---

## 用户名密码

本系统**没有注册页**，管理员账号在安装时自动生成：

- 用户名固定：`admin`
- 密码：随机 16 位十六进制串，安装完成后会显示在页面上，同时写入 `src/runtime/admin_password.txt`

登录后立刻去**右上角用户菜单 → 修改密码**，把初始随机密码换成你自己的。限制：

- 新密码最少 8 位
- 新用户名只能是 5–10 位的小写字母数字
- 修改成功会强制踢下线，需要用新凭据重新登录

> 忘记密码的最快做法：直接 `DROP TABLE` 用户相关表让系统重新生成 admin，或者手动用 `password_hash()` 改 `password` 字段。

如果有自建 SSO（OIDC），在 `config.php` 中将 `login.ssoEnable` 改为 `true`，并填写 `ssoProviderUrl`、`ssoClientId`、`ssoClientSecret`。

---

## MCP 接入（可选）

登录后，左侧抽屉点 **「复制 MCP 配置」**，会生成带 Token 的配置，可直接贴进 Cursor / Claude Desktop 等客户端。

示例结构：

```json
{
  "mcpServers": {
    "todo": {
      "url": "https://your-domain.com/mcp",
      "headers": {
        "Authorization": "Bearer <你的 token>"
      }
    }
  }
}
```

暴露的工具：

| 工具 | 作用 |
| --- | --- |
| `list_lists` | 列出清单 |
| `create_list` | 新建清单 |
| `list_tasks` | 按视图 / 列表查任务 |
| `create_task` | 新建任务 |
| `update_task` | 更新任务字段 |
| `complete_task` | 标记完成 / 取消完成 |
| `delete_task` | 删除任务 |

Token 按用户隔离；需要轮换时调用重置接口，或在业务里重新生成。HTTP API 与 MCP 共用同一套 `TodoOps`，不会出现两套逻辑漂移。

---

## 典型工作流

```
浏览器 Web 端 ←→ 同一套 TodoOps ←→ MCP / AI Agent
       │                              │
       └────────── 同一数据库 ─────────┘
```

- 人在 Web 上改任务，Agent 立刻能读到
- Agent 通过 MCP 建任务 / 标完成，Web 刷新即见

---

## 故障排查

**登录页一直报错：**

- 验证码识别有误，刷新一下
- 查看 `src/runtime/log/` 是否记录了密码错误 / 验证码错误

**数据库连不上：**

- 优先确认安装向导选的是 SQLite 还是 MySQL
- MySQL 主机名以面板/环境显示为准（本机常用 `127.0.0.1`；容器化可能是 `mysql`）
- 确认账号有 `todo` 库的全部权限

**MCP 报 Unauthorized：**

- 确认 `Authorization: Bearer <token>` 头正确（也兼容纯 `Auth` 头或 `?token=`）
- Token 是否已重置；重新「复制 MCP 配置」再贴一次
- 站点是否可被 MCP 客户端访问（本机 / 内网 / HTTPS）

**安装后再次进入安装页：**

- 确认 `src/config.php` 里 `installed` 为 `true`
- 确认 PHP 进程对 `config.php` / `runtime` 可写

---

## 目录结构

```
todo/
├── src/
│   ├── app/
│   │   ├── controller/todo/    # 页面、任务/列表 API、MCP 入口
│   │   ├── database/           # Model / DAO（Task、TodoList、McpAuth）
│   │   ├── mcp/todo/           # MCP Tool 实现
│   │   ├── todo/               # TodoOps（HTTP 与 MCP 共用业务）
│   │   ├── static/             # 前端资源（MDUI、框架、组件）
│   │   ├── view/               # 模板
│   │   └── Application.php     # 应用入口与路由
│   ├── nova/                   # Nova 框架 + 插件（submodule）
│   ├── public/                 # Web 入口，Nginx root 指向这里
│   ├── runtime/                # 缓存、日志、admin_password.txt
│   ├── example.config.php      # 配置模板，复制为 config.php 使用
│   ├── installer.php           # 安装向导步骤与字段
│   └── config.php              # 实际配置（.gitignore 排除）
├── tests/                      # 测试目录
├── nginx.conf                  # Nginx rewrite 参考
├── package.json                # 项目元信息
├── nova.phar                   # CLI 工具
└── README.md
```

---

## 技术栈

- **后端**：PHP 8.3 + Nova 框架 + SQLite / MySQL
- **前端**：MDUI 2.x + Pjax
- **AI 接入**：MCP（nova-mcp 插件），HTTP 流式协议
- **鉴权**：登录插件（会话 + 可选 OIDC）+ MCP Bearer Token

---

## 许可证

MIT License

## 贡献

欢迎 Issue / PR：

- PHP：PSR-12 / `php-cs-fixer.dist.php`
- JS：ES6+
- Commit：写清楚「为什么改」，不只是「改了什么」
