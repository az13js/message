# Docker 部署指南

## 快速开始

### 1. 基本部署

```bash
# 复制环境变量配置文件
cp .env.example .env

# 启动服务
docker-compose up -d
```

服务将在 http://localhost:8000 可访问。

### 2. 带配置的部署

编辑 `.env` 文件：

```bash
# 设置删除密钥（可选）
DELETE_KEY=your_secret_key

# 限制允许的文件类型（可选）
# 只允许图片和文档
ALLOWED_EXTENSIONS=jpg,png,gif,pdf,txt,doc,docx
```

然后启动：

```bash
docker-compose up -d
```

## 架构说明

### 单容器设计

本项目采用**单容器架构**，一个Docker容器内同时运行：

- **Apache Web服务器**：提供Web界面和API服务
- **后台下载服务**：处理文件下载任务

### 优势

✅ **简化部署** - 只需管理一个容器  
✅ **减少资源开销** - 避免多容器的基础开销  
✅ **易于维护** - 单一入口点，配置简单  
✅ **故障隔离** - 两个服务相互独立，一个崩溃不影响另一个  

## 配置选项

### 环境变量

| 变量名 | 描述 | 默认值 |
|--------|------|--------|
| `DELETE_KEY` | 删除文件时需要的密钥 | 空（允许任何人删除） |
| `ALLOWED_EXTENSIONS` | 允许上传的文件扩展名，逗号分隔 | 空（允许所有类型） |

### 端口配置

默认将容器的80端口映射到主机的8000端口。如需修改，请编辑 `docker-compose.yml` 文件中的 `ports` 配置。

### 数据持久化

数据存储在两个Docker卷中：
- `files_data`: 存储用户上传的文件
- `metadata_data`: 存储元数据和任务信息

## 容器内服务管理

### 启动模式

容器支持多种启动模式：

```bash
# 启动所有服务（默认）
docker run files-php start-services

# 仅启动Web服务
docker run files-php apache

# 仅启动下载服务
docker run files-php download
```

### 服务状态

容器启动后会自动：
1. 启动Apache Web服务器
2. 启动后台下载服务
3. 监控两个服务的运行状态

## 常用命令

```bash
# 查看服务状态
docker-compose ps

# 查看日志
docker-compose logs -f

# 重启服务
docker-compose restart

# 停止服务
docker-compose down

# 完全清理（包括数据卷）
docker-compose down -v
```

## 生产环境建议

1. **设置删除密钥**：在 `.env` 文件中设置 `DELETE_KEY`
2. **限制文件类型**：设置 `ALLOWED_EXTENSIONS` 限制上传类型
3. **使用HTTPS**：在生产环境中建议在前端使用反向代理（如Nginx）提供HTTPS
4. **定期备份**：备份 `files_data` 和 `metadata_data` 卷

## 故障排除

### 服务无法启动

检查日志：
```bash
docker-compose logs files-php
```

### 文件上传失败

确保有足够的磁盘空间，并检查权限设置。

### 后台下载服务不工作

1. 检查curl扩展是否已安装
2. 检查下载任务是否正确创建
3. 查看下载服务日志：
```bash
docker-compose logs files-php | grep "download"
```

### 容器重启后服务状态

容器重启后，两个服务会自动启动：
- Web服务立即可用
- 下载服务会继续处理未完成的任务