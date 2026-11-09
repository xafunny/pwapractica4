<?php
require_once __DIR__ . '/funciones.php';
$flash = getFlash();
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina ?? 'Inicio') ?> | <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="cabecera">
    <div class="contenedor cabecera__fila">
        <a href="index.php" class="logo">Tienda<span>Tech</span></a>
        <nav class="menu">
            <a href="index.php" class="<?= $paginaActual === 'index.php' ? 'activo' : '' ?>">Productos</a>
            <?php if (estaLogueado()): ?>
                <a href="pedidos.php" class="<?= $paginaActual === 'pedidos.php' ? 'activo' : '' ?>">Mis pedidos</a>
                <span class="menu__usuario"><?= e(explode(' ', $_SESSION['cliente_nombre'])[0]) ?></span>
                <a href="logout.php">Salir</a>
            <?php else: ?>
                <a href="login.php" class="<?= $paginaActual === 'login.php' ? 'activo' : '' ?>">Iniciar sesión</a>
                <a href="registro.php" class="<?= $paginaActual === 'registro.php' ? 'activo' : '' ?>">Registrarse</a>
            <?php endif; ?>
            <a href="carrito.php" class="btn-carrito <?= $paginaActual === 'carrito.php' ? 'activo' : '' ?>">
                Carrito <span id="contador-carrito" class="contador"><?= carritoCantidadTotal() ?></span>
            </a>
        </nav>
    </div>
</header>

<main class="contenedor principal">
<?php if ($flash): ?>
    <div class="alerta alerta--<?= e($flash['tipo']) ?>" role="alert"><?= e($flash['mensaje']) ?></div>
<?php endif; ?>
