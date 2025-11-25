# Dockerfile
FROM php:8.2-apache

# Instalar dependencias necesarias para MySQL, PostgreSQL y herramientas comunes
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    libpq-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql

# Habilitar mod_rewrite si usas rutas amigables
RUN a2enmod rewrite

# Copiar entrypoint
COPY Docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copiar proyecto
COPY . /var/www/html/

# Permisos opcionales
RUN chown -R www-data:www-data /var/www/html

# Puerto para Render
EXPOSE 3000

# Ejecutar entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]