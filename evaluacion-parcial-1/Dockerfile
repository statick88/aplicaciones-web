FROM php:8.5-apache

RUN docker-php-ext-install mysqli pdo_mysql

WORKDIR /var/www/html
COPY public/ /var/www/html/
COPY src/ /var/www/src/

HEALTHCHECK --interval=10s --timeout=5s --start-period=15s --retries=6 CMD php -r '$context = stream_context_create(["http" => ["timeout" => 3, "ignore_errors" => false]]); exit(@file_get_contents("http://127.0.0.1/", false, $context) === false ? 1 : 0);'

EXPOSE 80
