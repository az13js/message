FROM acicn/php:7.4-pagoda
COPY public/ /var/www/public/
RUN  mkdir /var/www/public/message 
EXPOSE 80
