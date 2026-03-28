FROM php:8.2-apache

# 安装curl扩展所需的依赖
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# 安装curl扩展
RUN docker-php-ext-install curl

# 复制应用文件
COPY public/files.php /var/www/html/
COPY public/files.php.download.php /var/www/html/
COPY docker-entrypoint.sh /usr/local/bin/

# 设置入口点脚本权限
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 创建必要的目录并设置权限
RUN mkdir -p /var/www/html/files /var/www/html/.files \
    && chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite headers

# 设置工作目录
WORKDIR /var/www/html

# 设置入口点
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["start-services"]

# 暴露端口
EXPOSE 80