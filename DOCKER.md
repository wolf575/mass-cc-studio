# Docker 安装说明

## 从 GHCR 运行

### docker run

```bash
docker run -d \
  -v /docker-data/mass-cc-studio/data:/data \
  -p 8080:80 \
  --name mass-cc-studio \
  --hostname mass-cc-studio \
  --restart unless-stopped \
  ghcr.io/wolf575/mass-cc-studio:latest
```

访问 <http://localhost:8080>，使用 `admin` / `ChangeMe_2026!` 登录。

### docker compose

在项目目录执行：

```bash
docker compose up -d
```

自定义端口：

```bash
STUDIO_PORT=9090 docker compose up -d
```

停止并删除容器：

```bash
docker compose down
```

## 数据持久化

所有运行数据保存在宿主机目录 `/docker-data/mass-cc-studio/data`（容器内 `/data`），包括：

- 登录凭据（修改用户名 / 密码后写入 `auth.json`）
- 会话签名密钥（首次运行自动生成，写入 `.session_secret`）
- 登录与 API 限流状态

> 使用 host 目录挂载时，请确保该目录可被容器内的 `www-data`(uid 33) 写入：
> `chown -R 33:33 /docker-data/mass-cc-studio/data`

删除该目录即可恢复初始状态（默认凭据）。

## 从源码构建

```bash
docker build -t mass-cc-studio:local .
```

用 compose 构建并运行：

```bash
docker compose up -d --build
```

## 环境变量（可选）

不传任何环境变量即可使用。需要时可用以下变量覆盖：

| 变量 | 默认值 | 说明 |
| --- | --- | --- |
| `AUTH_USERNAME` | `admin` | 初始登录用户名 |
| `AUTH_PASSWORD` | `ChangeMe_2026!` | 初始登录密码 |
| `SESSION_SECRET` | 自动生成 | 会话签名密钥（覆盖后不再自动生成） |
| `COOKIE_SECURE` | `0` | 设为 `1` 时 cookie 仅 HTTPS 下发送 |
| `STUDIO_PORT` | `8080` | compose 模式映射到宿主机的外部端口 |

## 镜像标签

- `ghcr.io/wolf575/mass-cc-studio:latest`
- `ghcr.io/wolf575/mass-cc-studio:1.0.0`

## 故障排查

查看日志：

```bash
docker logs mass-cc-studio
```

进入容器检查 PHP 文件语法：

```bash
docker exec -it mass-cc-studio sh -c 'for f in /var/www/html/*.php; do php -l "$f"; done'
```