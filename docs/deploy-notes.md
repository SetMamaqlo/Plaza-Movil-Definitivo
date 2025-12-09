# Notas de despliegue (Render + Railway)

## Variables de entorno necesarias
- `APP_ENV=production`
- `BASE_URL=https://<tu-app>.onrender.com` (ajusta al dominio final)
- Credenciales de BD (Railway): usa **DB_URL** completo o bien los pares individuales `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- (Opcional) `DEBUG_KEY` solo para usar `tools/inspect_error.php` temporalmente.

## Build de Docker
- `.dockerignore` excluye `vendor/` y `.env`, por eso el `Dockerfile` ahora instala Composer dentro de la imagen. No copies el `.env` en la imagen; pon las vars en Render.

## Sincronizar esquema de BD en Railway
Los errores en logs (`precio_unitario`, etc.) indican que la BD remota no tiene todas las columnas del dump `agro_app.sql`. En Railway, ejecuta al menos:

```sql
-- Asegurar precio_unitario en detalle de pedido
ALTER TABLE pedido_detalle
    ADD COLUMN precio_unitario INT(10) NULL DEFAULT NULL AFTER cantidad;

-- Asegurar id_usuario en pedidos (si no existe)
ALTER TABLE pedidos
    ADD COLUMN id_usuario INT(11) NOT NULL AFTER id_pedido;
```

Si prefieres, importa de nuevo `agro_app.sql` completo para alinear todas las tablas (revisando datos existentes).

## Verificaciones rápidas
- `php dbtest.php` o abre `/dbtest.php` para confirmar que el contenedor lee las credenciales correctas.
- `/tools/inspect_error.php?ref=algo&key=<DEBUG_KEY>` muestra las vars de BD y prueba PDO (no lo dejes público).
