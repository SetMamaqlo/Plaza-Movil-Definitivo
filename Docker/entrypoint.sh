#!/usr/bin/env bash
set -e

# Si $PORT está definido y no es 80, reemplaza la configuración de Apache
# para que escuche en $PORT (Render pasa un PORT en env)
if [ -n "$PORT" ]; then
  # Reemplaza Listen 80 con Listen $PORT
  sed -ri "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf || true
  # Reemplaza VirtualHost *:80 con *:$PORT
  sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf || true
fi

# Ejecuta comando por defecto (apache2-foreground) pasado por CMD
exec "$@"
