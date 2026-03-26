FROM php:8.2-apache

COPY public/files.php /var/www/html/
COPY public/files.php.download.php /var/www/html/

RUN mkdir -p /var/www/html/files /var/www/html/.files \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80