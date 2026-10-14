# Docker 安装说明

## 从 GHCR 运行

### docker run

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

如需同时删除持久化数据卷，请谨慎执行：

```bash
docker compose down -v
```

## 从源码构建

### 构建镜像

```bash
docker build -t mass-cc-studio:local .
```

### 用 compose 构建并运行

```bash
docker compose up -d --build
```

### 配置覆盖示例

创建 `.env` 文件：

```dotenv
STUDIO_PORT=8080
AUTH_USERNAME=admin
AUTH_PASSWORD=ChangeMe_2026!
SESSION_SECRET=replace-with-a-long-random-string
COOKIE_SECURE=0
```

然后执行：

```bash
docker compose up -d
```

## 数据持久化

- compose 默认挂载 `./data` 到容器内 `/var/www/html/data`。
- 该目录保存凭据文件和登录 / API 限流状态。
- 修改用户名或密码后，凭据会写入此目录，因此会覆盖环境变量中的初始值。

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
