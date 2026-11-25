FROM php:8.5-apache
RUN docker-php-ext-install mysqli
COPY . /var/www/html/
RUN mkdir -p /var/lib/php/sessions && chown www-data:www-data /var/lib/php/sessions
