#!/bin/bash

# 文件管理服务启动脚本

echo "🚀 启动文件管理服务..."

# 检查docker-compose是否存在
if ! command -v docker-compose &> /dev/null; then
    echo "❌ docker-compose 未安装，请先安装 Docker Compose"
    exit 1
fi

# 复制环境变量模板（如果不存在）
if [ ! -f .env ]; then
    echo "📝 创建环境变量配置文件..."
    cp .env.example .env
fi

# 启动服务
echo "🐳 启动 Docker 容器..."
docker-compose up -d

# 等待服务启动
echo "⏳ 等待服务启动..."
sleep 5

# 检查服务状态
if docker-compose ps | grep -q "Up"; then
    echo "✅ 服务启动成功！"
    echo "🌐 访问地址: http://localhost:8000"
    echo ""
    echo "📖 使用说明:"
    echo "  - 上传文件: 点击页面上的上传按钮"
    echo "  - 下载文件: 点击文件列表中的下载链接"
    echo "  - 后台下载: 在后台下载区域添加下载任务"
    echo "  - 删除文件: 点击删除按钮（需要删除密钥）"
    echo ""
    echo "💡 服务架构:"
    echo "  - 单容器设计，同时运行Web服务和下载服务"
    echo "  - 数据持久化存储，容器重启不影响文件"
    echo ""
    echo "🔧 管理命令:"
    echo "  docker-compose logs -f        # 查看日志"
    echo "  docker-compose restart        # 重启服务"
    echo "  docker-compose down           # 停止服务"
    echo "  docker-compose down -v        # 完全清理（包括数据）"
else
    echo "❌ 服务启动失败，请检查日志:"
    docker-compose logs
    exit 1
fi