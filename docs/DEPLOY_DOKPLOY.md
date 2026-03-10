# Desplegar Services Fields en Dokploy

Guía paso a paso para desplegar esta aplicación Laravel (Vue + Inertia) en [Dokploy](https://dokploy.com/).

---

## Requisitos previos

- Un servidor con Docker y Dokploy instalado (o usar [Dokploy Cloud](https://dokploy.com)).
- Repositorio Git (GitHub, GitLab o Gitea) con el código de este proyecto.
- Dominio opcional (o usar la URL que asigne Dokploy).

---

## Opción A: Una sola aplicación + bases de datos gestionadas por Dokploy

Ideal si querés que Dokploy cree Postgres y Redis por vos.

### Paso 1: Crear el proyecto en Dokploy

1. Entrá al panel de Dokploy (ej: `https://tu-servidor:3000`).
2. **Project** → **Create Project**.
3. Nombre: `services-fields` (o el que prefieras).

### Paso 2: Crear la base de datos PostgreSQL

1. Dentro del proyecto → **Database** → **Add Database**.
2. Elegí **PostgreSQL**.
3. Nombre: `services-fields-db`.
4. Anotá:
   - **Host** (interno, ej: `postgres-xxx` o la IP del contenedor).
   - **Port**: `5432`.
   - **Database**, **Username**, **Password** (los que definas).
5. Deploy de la base de datos.

### Paso 3: Crear Redis (opcional pero recomendado)

1. **Database** → **Add Database** (o **Add Service** si Redis está en otra sección).
2. Elegí **Redis**.
3. Nombre: `services-fields-redis`.
4. Anotá **Host** y **Port** (ej: `6379`).

### Paso 4: Crear la aplicación Laravel

1. **Application** → **Add Application**.
2. **Source**: conectá tu repo (GitHub/GitLab/Gitea) y elegí el repositorio de `services-fields`.
   - **Branch**: la rama a desplegar (ej: `main`).
   - **Base Directory**: dejalo **vacío** o `/` (el proyecto está en la raíz del repo). Si ponés una subcarpeta que no exista, puede fallar el deploy con "Directory nonexistent".
3. **Build**:
   - **Build Type**: `Dockerfile`.
   - **Dockerfile Path**: `Dockerfile` (solo el nombre del archivo, no una ruta larga).
   - **Docker Context Path**: `.` (punto = raíz del repo).
4. **Deploy**:
   - **Port**: `8000` (el que expone `php artisan serve` en el Dockerfile).
5. Guardá.

### Paso 5: Variables de entorno

En la aplicación → pestaña **Environment** (o **Env**) agregá al menos:

```env
APP_NAME="Services Fields"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Generá una clave con: php artisan key:generate --show
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=

# Base de datos (usá los datos del Paso 2)
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=services_fields
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Redis (usá los datos del Paso 3)
REDIS_HOST=redis
REDIS_PORT=6379
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Migraciones en el arranque (el entrypoint las ejecuta si RUN_MIGRATE=true)
RUN_MIGRATE=true

# API InforAPI (opcional, para buscar CUIT por DNI)
INFORAPI_KEY=tu_clave_si_la_tenes
```

**Importante:** En Dokploy, si Postgres/Redis están en el mismo “stack”, el **host** suele ser el nombre del servicio (ej: `postgres`, `redis`). Si están en otro proyecto, usá la URL o host que te dé Dokploy (ej: `postgres-abc123.dokploy`).

### Paso 6: Conectar app con Postgres y Redis

- En la configuración de la **Application**, en **Networks** o **Dependencies**, asegurate de que la app esté en la misma red que Postgres y Redis (o que pueda resolver sus nombres de host).
- Si Dokploy no une redes automáticamente, puede que tengas que usar la **IP interna** o la **URL de conexión** que Dokploy muestre para la base de datos.

### Paso 7: Desplegar

1. **Deploy** (o **Redeploy**) de la aplicación.
2. Esperá a que el build termine (incluye `composer install`, `npm run build` y la imagen Docker).
3. Abrí la URL que te asigne Dokploy (o tu dominio si ya configuraste uno).

### Paso 8: Migraciones y primer uso

- Si en el **entrypoint** tenés `RUN_MIGRATE=true`, las migraciones se ejecutan al arrancar el contenedor.
- Si no, en **Application** → **Console** (o ejecutando un comando en el contenedor):

  ```bash
  php artisan migrate --force
  ```

Luego podés crear un usuario desde **Register** (si Breeze está con registro) o con:

```bash
php artisan tinker
# User::create([...]);
```

---

## Opción B: Docker Compose en Dokploy

Si preferís usar tu `docker-compose.yml` dentro de Dokploy:

1. En el proyecto → **Application** → **Add Application**.
2. **Source**: mismo repo.
3. **Build Type**: `Docker Compose`.
4. **Docker Compose path**: `docker-compose.yml` (o un archivo tipo `docker-compose.prod.yml` que no monte el código local).

En producción **no** conviene montar `.:/var/www/html` (código local). Podés:

- Usar un `docker-compose.prod.yml` que quite esos `volumes` y deje solo volúmenes con nombre (`storage_data`, `vendor_data`, etc.), **o**
- En Dokploy, si usa el compose actual, asegurarse de que el código se inyecte por el build (Dokploy suele clonar el repo y construir en ese contexto).

En el compose, definí todas las variables de entorno necesarias (igual que en la Opción A) para la app, Postgres y Redis.

---

## Resumen de puertos y servicios

| Servicio   | Puerto (interno) | Nota                          |
|-----------|-------------------|-------------------------------|
| Laravel   | 8000              | `php artisan serve --port=8000` |
| PostgreSQL| 5432              | Lo abre el contenedor de DB   |
| Redis     | 6379              | Lo abre el contenedor de Redis|

En Dokploy, solo necesitás exponer el **8000** de la app (y opcionalmente 5432/6379 si accedés desde fuera).

---

## Dominio y HTTPS

1. En la aplicación → **Settings** o **Domains**.
2. Añadí tu dominio (ej: `app.tudominio.com`).
3. Si Dokploy tiene integración con Let's Encrypt (o Traefik/Caddy), activá SSL para ese dominio.

---

## Problemas frecuentes

### "cannot create .../code/.env: Directory nonexistent"

Este error suele aparecer cuando Dokploy intenta escribir el archivo de variables de entorno (`.env`) en la carpeta `code` antes de que exista (por ejemplo, antes de clonar el repo o con una ruta mal configurada).

**Qué probar (en orden):**

1. **Base Directory vacío o raíz**  
   En la aplicación → **Settings** / **General**:
   - **Base Directory** (o "Build Path" / "Source Directory"): dejalo **vacío** o poné `/`.
   - Si tenés algo como `frontend` o `apps/web` y en tu repo la app está en la raíz, borralo.

2. **Rutas de Dockerfile correctas**  
   En la pestaña de **Build**:
   - **Dockerfile Path**: solo `Dockerfile` (sin barras ni rutas tipo `./Dockerfile` o `/Dockerfile`).
   - **Docker Context Path**: `.` (un punto).

3. **Eliminar y volver a crear la aplicación**  
   Borrá la aplicación en Dokploy y creala de nuevo (misma configuración). A veces la carpeta `code` queda en un estado raro en el primer deploy.

4. **Variables de entorno después del primer deploy**  
   En algunas versiones, si las variables se guardan antes de que exista el repo clonado, falla. Probá:
   - Crear la app **sin** cargar muchas variables.
   - Hacer un primer **Deploy** (aunque falle el build).
   - Luego agregar las variables de entorno y volver a desplegar.

5. **Actualizar Dokploy**  
   Si tu versión es antigua, actualizá a la última estable; este comportamiento ha tenido correcciones en versiones recientes.

Si sigue fallando, en el panel de Dokploy revisá los **logs** del paso "Initializing deployment" para ver en qué ruta intenta escribir y abrí un issue en [Dokploy/dokploy](https://github.com/Dokploy/dokploy) con ese mensaje.

---

### Otros

- **502 Bad Gateway**: La app no arrancó o no escucha en el puerto que Dokploy espera. Revisá que el contenedor use `--host=0.0.0.0` y puerto `8000`.
- **Error de conexión a base de datos**: Revisá `DB_HOST`, `DB_PORT`, usuario y contraseña. En entornos “managed”, el host suele ser el nombre del servicio (ej: `postgres`) o la URL que indique Dokploy.
- **Assets (JS/CSS) no cargan**: El Dockerfile ya incluye `npm run build`; asegurate de que el deploy use la imagen construida con ese Dockerfile y que `APP_URL` coincida con la URL real (para Vite/asset URLs).
- **APP_KEY vacía**: El entrypoint genera una si no existe; en producción es mejor setear `APP_KEY` en las variables de entorno (generada con `php artisan key:generate --show`).

Si compartís cómo está configurado tu Dokploy (si usás solo “Application” o también “Database” para Postgres/Redis), se puede afinar mejor los nombres de host y la red.
