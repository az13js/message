#!/bin/bash
set -e

# 如果设置了环境变量，则创建配置文件
if [ -n "$DELETE_KEY" ] || [ -n "$ALLOWED_EXTENSIONS" ]; then
    echo "Creating configuration file..."
    
    # 创建临时配置文件
    cat > /tmp/config.php << 'EOF'
<?php
// Docker 配置文件 - 由环境变量生成
if (getenv('DELETE_KEY')) {
    define('DELETE_KEY', getenv('DELETE_KEY'));
}
if (getenv('ALLOWED_EXTENSIONS')) {
    $ext = getenv('ALLOWED_EXTENSIONS');
    $extArray = array_map('trim', explode(',', $ext));
    define('ALLOWED_EXTENSIONS', $extArray);
}
?>
EOF

    # 将配置合并到主文件中
    sed -i '/^define(\x27DELETE_KEY\x27, \x27\x27);$/{
        r /tmp/config.php
        d
    }' /var/www/html/files.php

    # 清理临时文件
    rm -f /tmp/config.php
fi

# 启动服务的函数
start_services() {
    echo "🚀 Starting files.php services..."
    
    # 启动Apache Web服务
    echo "📦 Starting Apache Web server..."
    apache2-foreground &
    APACHE_PID=$!
    
    # 等待Apache启动
    sleep 2
    
    # 启动后台下载服务
    echo "⬇️ Starting background download service..."
    php /var/www/html/files.php.download.php &
    DOWNLOAD_PID=$!
    
    # 创建服务状态监控
    trap "kill $APACHE_PID $DOWNLOAD_PID 2>/dev/null; exit" SIGTERM SIGINT
    
    # 等待所有进程
    wait
}

# 根据命令参数决定启动方式
case "${1:-start-services}" in
    start-services)
        start_services
        ;;
    apache)
        exec apache2-foreground
        ;;
    download)
        exec php /var/www/html/files.php.download.php
        ;;
    *)
        exec "$@"
        ;;
esac