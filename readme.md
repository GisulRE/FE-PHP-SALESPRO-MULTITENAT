# Documentación del Proyecto

Este proyecto utiliza **Laravel 8** y **PHP 7.4**.

## Requisitos

- PHP >= 7.4
- Composer
- MySQL o cualquier base de datos soportada por Laravel
- Node.js y npm (opcional, para assets frontend)

## Instalación

1. Clona el repositorio:
  ```bash
  git clone <url-del-repositorio>
  cd <nombre-del-proyecto>
  ```

2. Instala dependencias de PHP:
  ```bash
  composer install
  ```

3. Copia el archivo de entorno y configura tus variables:
  ```bash
  cp .env.example .env
  # Edita .env según tu entorno
  ```

4. Genera la clave de la aplicación:
  ```bash
  php artisan key:generate
  ```

5. (Opcional) Instala dependencias frontend:
  ```bash
  npm install && npm run dev
  ```

## Uso solo de uso local no producción

- Levanta el servidor de desarrollo omitir este paso en servidor gisul:
  ```bash
  php artisan serve
  ```
- Accede a `http://localhost:8000` en tu navegador.

## Estructura Básica

- **app/**: Lógica de la aplicación (Controladores, Modelos, etc.)
- **routes/web.php**: Rutas web.
- **resources/views/**: Vistas Blade.
- **public/**: Archivos públicos (index.php, assets).

## Comandos Útiles

- Limpiar cachés:
  ```bash
  php artisan cache:clear
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```

- Ejecutar pruebas:
  ```bash
  php artisan test
  ```

## Notas

- No se incluyen instrucciones de migraciones.
- Consulta la [documentación oficial de Laravel 8](https://laravel.com/docs/8.x) para más detalles.
