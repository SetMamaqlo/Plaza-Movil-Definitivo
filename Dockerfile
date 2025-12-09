# Imagen base con Apache
FROM php:8.2-apache

# Instalar dependencias necesarias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    libpq-dev \
    libonig-dev \
    pkg-config \
    curl \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer (se requiere porque .dockerignore excluye vendor/)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Activar mod_rewrite
RUN a2enmod rewrite

# Puerto por defecto (Render lo reemplaza usando el entrypoint)
ENV PORT=10000

# Preparar carpeta de la app
WORKDIR /var/www/html

# Instalar dependencias PHP antes de copiar todo (mejor cache)
COPY composer.json composer.lock /var/www/html/
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar el resto del codigo del proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto dinamico
EXPOSE ${PORT}

# Copiar entrypoint
COPY Docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Usar el entrypoint personalizado
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Arrancar Apache
CMD ["apache2-foreground"]
