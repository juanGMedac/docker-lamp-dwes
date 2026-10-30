<?php
declare(strict_types=1);

/*
 * Página de inicio y diagnóstico del entorno DWES.
 * - Comprueba PHP, extensiones y conexión a MySQL.
 * - Lista las carpetas de proyecto que hay dentro de www/.
 * No borrar: es lo primero que hay que mirar cuando algo no funciona.
 */

if (isset($_GET['phpinfo'])) {
    phpinfo();
    exit;
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

// ---- Extensiones necesarias en el módulo ----
$extensiones = ['pdo_mysql', 'mysqli', 'intl', 'mbstring', 'json', 'session', 'xdebug'];

// ---- Prueba de conexión a MySQL con PDO ----
$dbHost = getenv('DB_HOST') ?: 'db';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'dwes';
$dbUser = getenv('DB_USER') ?: 'dwes';
$dbPass = getenv('DB_PASS') ?: 'dwes';

$conexionOk = false;
$infoBd     = '';
try {
    $pdo = new PDO(
        "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    $stmt = $pdo->prepare('SELECT VERSION() AS version, CURRENT_USER() AS usuario, ? AS prueba');
    $stmt->execute(['sentencia preparada OK']);
    $fila = $stmt->fetch();
    $conexionOk = true;
    $infoBd = "MySQL {$fila['version']} · usuario {$fila['usuario']} · {$fila['prueba']}";
} catch (PDOException $ex) {
    $infoBd = $ex->getMessage();
}

// ---- Carpetas de proyecto dentro de www/ ----
$proyectos = array_filter(
    scandir(__DIR__),
    fn(string $f): bool => $f[0] !== '.' && is_dir(__DIR__ . '/' . $f)
);

$pmaUrl = getenv('PMA_URL') ?: 'http://localhost:8081';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entorno DWES</title>
    <style>
        :root { --ok: #1a7f37; --ko: #cf222e; --borde: #d0d7de; --suave: #f6f8fa; }
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; max-width: 860px; margin: 2rem auto; padding: 0 1rem; color: #1f2328; }
        h1 { margin-bottom: .2rem; }
        .sub { color: #656d76; margin-top: 0; }
        section { border: 1px solid var(--borde); border-radius: 8px; padding: 1rem 1.2rem; margin: 1rem 0; }
        h2 { font-size: 1.1rem; margin-top: 0; }
        table { border-collapse: collapse; width: 100%; }
        td { padding: .35rem .5rem; border-bottom: 1px solid var(--suave); vertical-align: top; }
        td:first-child { width: 40%; color: #656d76; }
        .ok { color: var(--ok); font-weight: 600; }
        .ko { color: var(--ko); font-weight: 600; }
        code { background: var(--suave); padding: .1rem .35rem; border-radius: 4px; }
        ul.proyectos { columns: 2; padding-left: 1.2rem; }
        a { color: #0969da; }
    </style>
</head>
<body>
    <h1>Entorno DWES</h1>
    <p class="sub">0613 Desarrollo Web en Entorno Servidor · 2º DAW</p>

    <section>
        <h2>Servidor</h2>
        <table>
            <tr><td>PHP</td><td><?= e(PHP_VERSION) ?> (<?= e(PHP_SAPI) ?>)</td></tr>
            <tr><td>Servidor web</td><td><?= e($_SERVER['SERVER_SOFTWARE'] ?? 'desconocido') ?></td></tr>
            <tr><td>Zona horaria</td><td><?= e(date_default_timezone_get()) ?> · <?= e(date('d/m/Y H:i:s')) ?></td></tr>
            <tr><td>Xdebug</td><td><?= e(getenv('XDEBUG_MODE') ?: 'off') ?></td></tr>
            <tr><td>Más detalle</td><td><a href="?phpinfo=1">phpinfo()</a></td></tr>
        </table>
    </section>

    <section>
        <h2>Extensiones</h2>
        <table>
            <?php foreach ($extensiones as $ext): ?>
                <tr>
                    <td><?= e($ext) ?></td>
                    <td><?= extension_loaded($ext) ? '<span class="ok">cargada</span>' : '<span class="ko">no cargada</span>' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <section>
        <h2>Base de datos</h2>
        <table>
            <tr>
                <td>Conexión PDO</td>
                <td>
                    <?= $conexionOk ? '<span class="ok">correcta</span>' : '<span class="ko">error</span>' ?><br>
                    <?= e($infoBd) ?>
                </td>
            </tr>
            <tr><td>Desde PHP</td><td>host <code><?= e($dbHost) ?></code> · puerto <code><?= e($dbPort) ?></code> · BD <code><?= e($dbName) ?></code> · usuario <code><?= e($dbUser) ?></code></td></tr>
            <tr><td>phpMyAdmin</td><td><a href="<?= e($pmaUrl) ?>" target="_blank"><?= e($pmaUrl) ?></a></td></tr>
        </table>
    </section>

    <section>
        <h2>Mis proyectos (carpetas en <code>www/</code>)</h2>
        <?php if ($proyectos): ?>
            <ul class="proyectos">
                <?php foreach ($proyectos as $p): ?>
                    <li><a href="<?= e(rawurlencode($p)) ?>/"><?= e($p) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Todavía no hay ninguno. Crea una carpeta dentro de <code>www/</code> (por ejemplo <code>ud2-ejercicio1</code>) y recarga.</p>
        <?php endif; ?>
    </section>
</body>
</html>
