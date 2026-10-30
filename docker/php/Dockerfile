FROM php:8.4-fpm

ARG UID=1000
ARG GID=1000

# Configuración de DESARROLLO: muestra los errores en pantalla
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Extensiones: PDO y mysqli para MySQL, intl, zip, gd (imágenes) y Xdebug
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql mysqli intl zip gd xdebug

# Composer (autoload PSR-4 y dependencias, si se necesitan)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# En Linux, PHP escribe en ./www con el mismo usuario que el alumno
# (evita los "Permission denied" al subir archivos o crear logs)
RUN groupmod -o -g ${GID} www-data \
    && usermod -o -u ${UID} -g ${GID} www-data

COPY php.ini /usr/local/etc/php/conf.d/zz-dwes.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

WORKDIR /var/www/html
