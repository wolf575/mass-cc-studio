# mass-cc-studio

一个自托管的信用卡工具站，把 **MASS 检测器** 与 **CC 生成器 / 校验器** 合并到同一个 PHP 应用里：

- 检测器：输入 `卡号|月|年|CVV`，按结构 + 测试 BIN + 启发式评分返回 `live` / `unknown` / `die` 与原因。
- 生成器：按品牌、BIN、长度、有效期、CVV 生成 Luhn 有效的合成测试卡，支持 Text / CSV / JSON / PIPE。
- 独立校验与批量校验。
- 离线 BIN / IIN 查询。
- 全站登录保护，支持修改用户名和密码。

本项目基于现有 `MASS-CC-CHECKER` 的 PHP 8.2 + Apache 结构，并把 `CC-Checker-Validator-Generator` 的 Node.js 逻辑移植为 PHP。原始生成器项目不会被修改。

## 默认凭据

- 用户名：`admin`
- 密码：`ChangeMe_2026!`

首次登录后建议立即在“设置”页修改。

## 快速开始

### 方式一：docker run

```bash
docker run -d \
  --name mass-cc-studio \
  -p 8080:80 \
  -e AUTH_USERNAME=admin \
  -e "AUTH_PASSWORD=ChangeMe_2026!" \
  -e SESSION_SECRET=change-this-session-secret \
  -v mass-cc-studio-data:/var/www/html/data \
  ghcr.io/wolf575/mass-cc-studio:latest
```

打开 <http://localhost:8080>。

### 方式二：docker compose

```bash
docker compose up -d
```

默认同样在 `8080` 端口暴露，可用 `STUDIO_PORT` 覆盖：

```bash
STUDIO_PORT=9090 docker compose up -d
```

## 环境变量

| 变量 | 默认值 | 说明 |
| --- | --- | --- |
| `AUTH_USERNAME` | `admin` | 初始登录用户名 |
| `AUTH_PASSWORD` | `ChangeMe_2026!` | 初始登录密码 |
| `SESSION_SECRET` | `change-this-session-secret` | 会话签名密钥，生产环境务必修改 |
| `COOKIE_SECURE` | `0` | 设置为 `1` 时 cookie 仅在 HTTPS 下发送 |
| `STUDIO_PORT` | `8080` | compose 模式映射到宿主机的外部端口 |

密码与限流状态保存在挂载的 `./data`（或命名卷 `mass-cc-studio-data`）中。

## 镜像

公开 GHCR 镜像：

- `ghcr.io/wolf575/mass-cc-studio:latest`
- `ghcr.io/wolf575/mass-cc-studio:1.0.0`

详细安装与构建说明见 [DOCKER.md](./DOCKER.md)。

## 安全提示

请勿在公网实例上保留默认密码。`SESSION_SECRET` 泄露会允许伪造登录会话；请使用足够随机的值并通过环境变量注入。
