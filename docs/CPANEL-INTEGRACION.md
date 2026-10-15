# Gestión de equipos como entrada principal

Los dos proyectos permanecen en el mismo alojamiento cPanel, cada uno con su
propia carpeta, base de datos, APP_KEY y sesión. El menú de Gestión de equipos
abre Escaleon; no implementa inicio de sesión único.

## Estructura propuesta

- Gestión existente: https://gestionequipos.geekstore.cl (sin cambiar su base de datos).
- Escaleon: https://escaleon.geekstore.cl (subdominio propuesto; todavía no creado).
- Código de Escaleon: /home/USUARIO/escaleon.
- Raíz pública del nuevo subdominio: /home/USUARIO/escaleon/public.

No exponer la carpeta raíz de Laravel como document root.

## Publicar Escaleon

1. Respaldar archivos y base de datos de Gestión de equipos.
2. Crear el subdominio y activar HTTPS en cPanel.
3. Subir Escaleon a su propia carpeta, sin sobrescribir Gestión de equipos.
4. Crear una base de datos y un usuario exclusivos para Escaleon.
5. Configurar su .env: APP_ENV=production, APP_DEBUG=false, APP_URL con el
   subdominio real, conexión de base de datos y credenciales Google/Microsoft/Gemini.
   Usar SESSION_COOKIE=escaleon_session y SESSION_SECURE_COOKIE=true.
   No compartir APP_KEY ni cookies entre aplicaciones.
6. Instalar dependencias con Composer usando la versión PHP requerida por composer.lock;
   subir public/build generado con npm run build y no subir public/hot ni node_modules.
7. Generar APP_KEY solo si la instalación de Escaleon es nueva. Si se importa una
   instalación existente, conservar su clave. Ejecutar migraciones únicamente sobre
   la base de datos de Escaleon. Nunca migrate:fresh sobre registros existentes.
8. Dar permisos de escritura al usuario PHP en storage y bootstrap/cache.
9. Registrar los callbacks HTTPS reales en Google Cloud y Microsoft Entra y actualizar
   GOOGLE_REDIRECT_URI y AZURE_REDIRECT_URI en el .env de Escaleon.
10. Ejecutar php artisan optimize:clear y php artisan config:cache.

## Actualizar Gestión de equipos

Los únicos archivos modificados para agregar el enlace son:

- config/services.php
- resources/views/layouts/navigation.blade.php

En el .env del servidor de Gestión de equipos agregar:

```dotenv
ESCALEON_URL=https://escaleon.geekstore.cl/login
```

Sustituir por la dirección real elegida. Ejecutar php artisan config:clear y
php artisan view:clear en Gestión de equipos. No subir el .env local, no cambiar
su base de datos y no ejecutar migraciones para este cambio de menú.

En local ESCALEON_URL=http://localhost:8000/login apunta a Escaleon mientras
se ejecuta en ese puerto. No copiar esa dirección a producción.

## Verificación

Probar el menú desde escritorio y móvil, inicio de sesión en cada aplicación,
permisos de Gestión de equipos, conexiones OAuth y renovación de datos en Escaleon.
El despliegue y las cuentas de producción todavía requieren configurar cPanel.
