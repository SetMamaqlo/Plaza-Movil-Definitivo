# Dockerfile
FROM php:8.2-apache

# Instala extensiones comunes si las necesitas (PDO_MYSQL ejemplo)
RUN apt-get update && \
    apt-get install -y libzip-dev unzip git && \
    docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite (si usas rutas amigables)
RUN a2enmod rewrite

# Copiar el entrypoint (antes de copiar todo para que no invalides cache si cambias código)
COPY Docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copia el código al contenedor
COPY . /var/www/html/

# Ajusta permisos (opcional)
RUN chown -R www-data:www-data /var/www/html

# Puerto por defecto — lo dejamos para que el entrypoint lo remapee si Render establece $PORT
ENV PORT=10000

# Ejecutar entrypoint (reemplaza el puerto en config Apache y arranca)
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
