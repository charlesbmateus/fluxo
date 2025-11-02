# Usar PHP 8.3-FPM oficial
FROM php:8.3-fpm

ARG user=laravel
ARG uid=1000

# Instalar dependencias y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libicu-dev libonig-dev imagemagick libmagickwand-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring intl zip exif pcntl bcmath \
    && pecl install imagick xdebug \
    && docker-php-ext-enable imagick xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar Xdebug
RUN echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
 && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario y grupo con UID y GID específicos
RUN groupadd -g 1000 $user \
    && useradd -u $uid -ms /bin/bash -g $user $user

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos y establecer permisos
COPY --chown=$user:$user . /var/www/html

# Cambiar a usuario no root
USER $user

# Exponer puerto PHP-FPM
EXPOSE 9000

# Comando por defecto para arrancar PHP-FPM
CMD ["php-fpm"]