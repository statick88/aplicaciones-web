FROM php:8.5-apache

RUN docker-php-ext-install mysqli pdo_mysql

WORKDIR /var/www/html
COPY public/ /var/www/html/
COPY src/ /var/www/src/
EXPOSE 80
