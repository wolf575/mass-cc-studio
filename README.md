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
  -v /docker-data/mass-cc-studio/data:/data \
  -p 8080:80 \
  --name mass-cc-studio \
  --hostname mass-cc-studio \
  --restart unless-stopped \
  ghcr.io/wolf575/mass-cc-studio:latest
```

打开 <http://localhost:8080>，用 `admin` / `ChangeMe_2026!` 登录，首次登录后建议立即在“设置”页修改密码。

### 方式二：docker compose

```bash
docker compose up -d
```

默认在 `8080` 端口暴露，可用 `STUDIO_PORT` 覆盖：

```bash
STUDIO_PORT=9090 docker compose up -d
```

## 持久化

登录凭据、会话密钥、限流状态全部保存在挂载目录 `/data`（宿主机 `/docker-data/mass-cc-studio/data`，可自行调整路径）。删除该目录即恢复初始状态。会话签名密钥会在首次运行时自动生成并持久化，无需手动配置。

## 环境变量（可选）

不传任何环境变量即可直接使用。以下变量仅在需要时覆盖：

| 变量 | 默认值 | 说明 |
| --- | --- | --- |
| `AUTH_USERNAME` | `admin` | 初始登录用户名 |
| `AUTH_PASSWORD` | `ChangeMe_2026!` | 初始登录密码 |
| `SESSION_SECRET` | 自动生成 | 会话签名密钥，首次运行自动生成并持久化到 `/data` |
| `COOKIE_SECURE` | `0` | 设为 `1` 时 cookie 仅在 HTTPS 下发送 |
| `STUDIO_PORT` | `8080` | compose 模式映射到宿主机的外部端口 |

## 镜像

公开 GHCR 镜像：

- `ghcr.io/wolf575/mass-cc-studio:latest`
- `ghcr.io/wolf575/mass-cc-studio:1.0.0`

详细安装与构建说明见 [DOCKER.md](./DOCKER.md)。

## 安全提示

请勿在公网实例上保留默认密码，首次登录后立即在“设置”页修改。会话签名密钥会在首次运行时自动生成并持久化到 `/data`，无需手动配置。
