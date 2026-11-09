<?php
$tituloPagina = 'Iniciar sesión';
require_once __DIR__ . '/includes/funciones.php';

if (estaLogueado()) {
    header('Location: index.php');
    exit;
}

$error  = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrfValido($_POST['csrf'] ?? null)) {
        $error = 'Sesión expirada. Intenta de nuevo.';
    } else {
        $st = conectar()->prepare('SELECT * FROM clientes WHERE correo = ?');
        $st->execute([$correo]);
        $cliente = $st->fetch();

        if ($cliente && password_verify($password, $cliente['password'])) {
            session_regenerate_id(true);
            $_SESSION['cliente_id']     = (int) $cliente['id'];
            $_SESSION['cliente_nombre'] = $cliente['nombre'];

            $destino = $_SESSION['volver_a'] ?? 'index.php';
            unset($_SESSION['volver_a']);
            $permitidas = ['index.php', 'carrito.php', 'checkout.php', 'pedidos.php'];
            if (!in_array($destino, $permitidas, true)) {
                $destino = 'index.php';
            }
            setFlash('exito', 'Bienvenido de nuevo, ' . $cliente['nombre'] . '.');
            header('Location: ' . $destino);
            exit;
        }
        $error = 'Correo o contraseña incorrectos.';
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="formulario">
    <h1>Iniciar sesión</h1>

    <?php if ($error): ?>
        <div class="alerta alerta--error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" value="<?= e($correo) ?>" required autofocus>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn btn--primario btn--bloque">Entrar</button>
    </form>

    <p class="formulario__alt">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
