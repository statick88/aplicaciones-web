# =====================================================================
# Semana 4 — imagen de la aplicación Laravel
#
# El host no tiene Composer ni la extensión pdo_mysql de PHP, así que
# todo el toolchain de la semana vive en contenedor, igual que en las
# semanas 2 (PHP/MariaDB) y 3 (.NET/MySQL).
#
# Base php:8.5-cli para coincidir con la plataforma con la que se
# resolvió composer.lock (Laravel 13 exige PHP >= 8.3).
# =====================================================================
FROM php:8.5-cli

# Extensiones: pdo_mysql para hablar con MySQL (requisito de la
# asignatura), mbstring y zip para que Composer y Laravel funcionen.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libonig-dev \
        libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && rm -rf /var/lib/apt/lists/*

# Composer oficial, copiado desde su imagen para no instalarlo en el host.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Puerto único de la semana (semana2: 8080, semana3: 8081).
EXPOSE 8082

# El servidor de desarrollo de Laravel escuchando en todas las interfaces
# del contenedor para que compose pueda publicar el puerto 8082.
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8082"]
