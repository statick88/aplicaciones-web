FROM php:8.5-apache

WORKDIR /var/www/html

# Extensión mysqli: la base de datos vive en el servicio `db` de compose.yaml.
RUN docker-php-ext-install mysqli

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY index.php style.css favicon.png logo-uniandes.png /var/www/html/
COPY config/ /var/www/html/config/
COPY models/ /var/www/html/models/
COPY interfaces/ /var/www/html/interfaces/
COPY repositories/ /var/www/html/repositories/
COPY controllers/ /var/www/html/controllers/
COPY views/ /var/www/html/views/
COPY tests/ /var/www/html/tests/

RUN chown -R www-data:www-data /var/www \
    && find /var/www -type d -exec chmod 755 {} \; \
    && find /var/www -type f -exec chmod 644 {} \;

EXPOSE 80

HEALTHCHECK --interval=10s --timeout=3s --start-period=5s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/") === false ? 1 : 0);'
