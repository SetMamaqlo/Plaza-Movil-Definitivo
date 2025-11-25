# Imagen base con Apache
FROM php:8.2-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    libpq-dev \
    libonig-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql

# Activar mod_rewrite
RUN a2enmod rewrite

# Puerto por defecto (Render lo reemplaza usando el entrypoint)
ENV PORT=10000

# Copiar entrypoint
COPY Docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copiar el código del proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto dinámico
EXPOSE ${PORT}

# Usar el entrypoint personalizado
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Arrancar Apache
CMD ["apache2-foreground"]