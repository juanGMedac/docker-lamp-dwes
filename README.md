# Entorno LAMP con Docker — DWES 2º DAW

Entorno de trabajo para **0613 Desarrollo Web en Entorno Servidor**. Todos usamos exactamente el mismo: mismas versiones, mismos puertos y mismas credenciales. Así un ejercicio que funciona en tu equipo funciona también en el del profesor cuando lo corrige.

```
  Navegador
     │  http://localhost:8080
     ▼
┌──────────────┐  .php  ┌──────────────┐  PDO   ┌──────────────┐
│  dwes-web    │ ─────► │  dwes-php    │ ─────► │  dwes-db     │
│  Apache 2.4  │  :9000 │  PHP-FPM 8.4 │  :3306 │  MySQL 8.4   │
└──────────────┘        └──────────────┘        └──────────────┘
                                                       ▲
                      http://localhost:8081  ┌──────────────┐
                                             │  dwes-pma    │
                                             │  phpMyAdmin  │
                                             └──────────────┘
```

Son cuatro contenedores separados: Apache recibe la petición, si es un `.php` se la pasa a PHP-FPM, y PHP habla con MySQL. Es la misma arquitectura que verás en un servidor real.

---

## 1. Requisitos

- **Docker Desktop** instalado y **abierto** (en Windows, con el motor WSL 2).
- Comprueba en una terminal que responde:

```bash
docker --version
docker compose version
```

> No necesitas XAMPP. Si lo tienes, no hace falta desinstalarlo: este entorno usa puertos distintos y no choca con él.

---

## 2. Puesta en marcha (la primera vez)

1. Descarga el entorno, de una de estas dos formas:
   - **Con Git** (recomendado):

     ```bash
     git clone https://github.com/juanGMedac/docker-lamp-dwes.git
     cd docker-lamp-dwes
     ```

   - **Sin Git:** en esta página, botón verde **Code → Download ZIP**, y descomprime la carpeta donde quieras (por ejemplo, en `Documentos`).
2. Abre una terminal **dentro de la carpeta** `docker-lamp-dwes` (en VS Code: *Archivo → Abrir carpeta* y luego *Terminal → Nuevo terminal*).
3. Ejecuta:

```bash
docker compose up -d --build --wait
```

La primera vez tarda unos minutos: descarga las imágenes y prepara la base de datos. Cuando termine verás los cuatro contenedores en estado `Healthy` o `Started`.

4. Abre **http://localhost:8080**. Debe aparecer la página *Entorno DWES* con la conexión a la base de datos en verde. Si algo sale en rojo, ve al apartado 8.

Los días siguientes basta con `docker compose up -d`.

---

## 3. Direcciones y credenciales

| Qué | Dónde |
|---|---|
| Tus páginas | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 (entra solo, sin contraseña) |
| Base de datos por defecto | `dwes` |
| Usuario de MySQL | `dwes` / contraseña `dwes` |
| Usuario administrador | `root` / contraseña `root` |

Son contraseñas de clase, solo válidas en tu equipo: los puertos solo escuchan en `localhost`, nadie de la red puede entrar.

---

## 4. Dónde va mi código

Todo lo que pongas en la carpeta **`www/`** lo sirve Apache. Crea **una carpeta por ejercicio o proyecto**:

```
www/
├── index.php            ← página de diagnóstico (no la borres)
├── ud2-ejercicio1/
│   └── index.php        → http://localhost:8080/ud2-ejercicio1/
└── ud5-tienda/
    └── index.php        → http://localhost:8080/ud5-tienda/
```

Los cambios se ven al recargar el navegador. No hace falta reiniciar nada.

---

## 5. Conectar a la base de datos desde PHP

> **Regla de oro:** desde PHP el servidor de base de datos se llama **`db`** y el puerto es **`3306`**. Nunca `localhost`, nunca `3307`.

`localhost` dentro del contenedor de PHP es el propio contenedor de PHP, donde no hay ningún MySQL. Por eso falla.

```php
<?php
$dsn = 'mysql:host=db;port=3306;dbname=dwes;charset=utf8mb4';

$pdo = new PDO($dsn, 'dwes', 'dwes', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();
```

El usuario `dwes` puede crear sus propias bases de datos, así que puedes tener una por ejercicio (`tienda`, `biblioteca`...): solo cambia `dbname=` en el DSN.

---

## 6. Conectar desde programas de tu equipo

Para MySQL Workbench, DBeaver o la extensión de bases de datos de VS Code **sí** se usa tu equipo como servidor:

| Campo | Valor |
|---|---|
| Host | `127.0.0.1` |
| Puerto | `3307` |
| Usuario / contraseña | `dwes` / `dwes` (o `root` / `root`) |

| Desde... | Host | Puerto |
|---|---|---|
| Tu código PHP | `db` | `3306` |
| Programas de tu equipo | `127.0.0.1` | `3307` |

---

## 7. Comandos del día a día

Todos se ejecutan desde la carpeta `docker-lamp-dwes`.

| Para... | Comando |
|---|---|
| Arrancar | `docker compose up -d` |
| Parar (no se pierde nada) | `docker compose stop` |
| Ver qué está funcionando | `docker compose ps` |
| Ver errores de PHP | `docker compose logs php` |
| Ver errores de Apache o MySQL | `docker compose logs web` / `docker compose logs db` |
| Ejecutar un script PHP por consola | `docker compose exec php php ud2-ejercicio1/script.php` |
| Abrir la consola de MySQL | `docker compose exec db mysql -u dwes -pdwes` |
| Reconstruir tras tocar `docker/` | `docker compose up -d --build` |

### Cargar un script `.sql`

- **Lo más fácil:** phpMyAdmin → pestaña *Importar*.
- **Por consola**, en macOS, Linux o Git Bash:

  ```bash
  docker compose exec -T db mysql -u root -proot < tienda.sql
  ```

- **Por consola**, en PowerShell (no admite `<`):

  ```powershell
  Get-Content tienda.sql | docker compose exec -T db mysql -u root -proot
  ```

Los `.sql` que pongas en `db/init/` se cargan solos, pero **solo la primera vez** que se crea la base de datos (o después de un reinicio completo, apartado 10).

---

## 8. Problemas frecuentes

**"port is already allocated", "address already in use" o "Ports are not available"**
Otro programa usa ese puerto. Abre `.env`, cambia el puerto afectado (por ejemplo `WEB_PORT=8090`) y ejecuta `docker compose up -d`. Para ver qué programa lo ocupa en Windows: `netstat -ano | findstr :8080`.

**"Cannot connect to the Docker daemon" o "error during connect"**
Docker Desktop no está abierto. Ábrelo, espera a que el icono quede en verde y repite.

**`SQLSTATE[HY000] [2002] No such file or directory`**
Has puesto `localhost` como host. Cambia a `host=db`.

**`SQLSTATE[HY000] [2002] Connection refused` o `getaddrinfo for db failed`**
- MySQL todavía está arrancando: espera unos segundos o arranca siempre con `--wait`.
- Estás ejecutando PHP en tu equipo, no en el contenedor. Usa `docker compose exec php php script.php`.
- Comprueba con `docker compose ps` que `dwes-db` está funcionando.

**`SQLSTATE[HY000] [1045] Access denied for user`**
- Revisa usuario y contraseña en tu código (`dwes` / `dwes`).
- Si has cambiado las credenciales en `.env`, **no tiene efecto**: MySQL solo las lee al crear la base de datos la primera vez. Deja `.env` como estaba o haz un reinicio completo (apartado 10).

**`SQLSTATE[HY000] [1049] Unknown database`**
Esa base de datos no existe todavía. Créala en phpMyAdmin o carga su script `.sql`.

**Página en blanco o error 500**
Mira el error real con `docker compose logs php`.

**Error 404**
La URL debe incluir la carpeta del proyecto: `http://localhost:8080/ud5-tienda/`, no `http://localhost:8080/`. Y en Linux, mayúsculas y minúsculas cuentan: `Tienda` y `tienda` son carpetas distintas.

**Las tildes y las eñes salen raras**
Comprueba tres cosas: `charset=utf8mb4` en el DSN, `<meta charset="UTF-8">` en el HTML y que el archivo está guardado en UTF-8 (lo indica VS Code abajo a la derecha).

**"Permission denied" al subir archivos o escribir ficheros (solo Linux)**
Pon tu UID y GID en `.env` (los obtienes con `id -u` e `id -g`) y ejecuta `docker compose up -d --build`.

**He tocado `php.ini` o un `Dockerfile` y no cambia nada**
Hay que reconstruir: `docker compose up -d --build`.

---

## 9. Depurar con Xdebug y VS Code

1. Instala en VS Code la extensión **PHP Debug** (de Xdebug).
2. En `.env` pon `XDEBUG_MODE=debug` y ejecuta `docker compose up -d`.
3. Abre la carpeta `docker-lamp-dwes` en VS Code y crea `.vscode/launch.json`:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Xdebug (Docker)",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}/www"
      }
    }
  ]
}
```

4. Pulsa **F5**, pon un punto de interrupción y recarga la página en el navegador.

Cuando acabes, vuelve a `XDEBUG_MODE=off` y `docker compose up -d`: con Xdebug activo todo va algo más lento.

---

## 10. Empezar de cero

Si la base de datos ha quedado mal o has cambiado las credenciales:

```bash
docker compose down -v
docker compose up -d --build --wait
```

> **Atención:** `-v` **borra todas tus bases de datos**. Exporta antes desde phpMyAdmin lo que quieras conservar. Tu código de `www/` no se toca.

Sin `-v`, `docker compose down` solo elimina los contenedores y conserva los datos.

---

## 11. Si venías del entorno anterior (`docker-lamp`)

1. Si tienes bases de datos que quieras conservar, expórtalas desde el phpMyAdmin antiguo.
2. En la carpeta del entorno antiguo: `docker compose down`.
3. Copia tus carpetas de proyecto a `www/` del entorno nuevo (no copies el `index.php` ni la carpeta `assets` del antiguo).
4. Arranca el nuevo entorno (apartado 2) e importa tus bases de datos.
5. En tu código, cambia usuario y contraseña de la conexión por `dwes` / `dwes`. El host sigue siendo `db`.

---

## 12. Qué hay en cada archivo

```
docker-lamp-dwes/
├── docker-compose.yml      Define los cuatro contenedores
├── .env                    Puertos y credenciales
├── docker/
│   ├── apache/             Imagen de Apache y su configuración
│   └── php/                Imagen de PHP, php.ini y Xdebug
├── db/init/                Scripts .sql que se cargan al crear la BD
└── www/                    TU CÓDIGO
```

Versiones: Apache 2.4 · PHP 8.4 (FPM) con PDO, mysqli, intl, zip, gd, Xdebug y Composer · MySQL 8.4 LTS · phpMyAdmin 5.2.
