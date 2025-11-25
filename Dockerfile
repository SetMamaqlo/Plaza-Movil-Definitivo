FROM php:8.2-apache

# Instalar dependencias necesarias para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar tu proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Render asigna un puerto en la variable env $PORT
ENV PORT=10000

# Cambiar Apache para que use el puerto proporcionado por Render
RUN sed -i "s/80/\${PORT}/" /etc/apache2/ports.conf
RUN sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf

EXPOSE ${PORT}

CMD ["apache2-foreground"]