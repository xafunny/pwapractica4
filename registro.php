<?php
$tituloPagina = 'Registrarse';
require_once __DIR__ . '/includes/funciones.php';

$errores = [];
$datos   = ['nombre' => '', 'correo' => '', 'direccion' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf'] ?? null)) {
        $errores[] = 'Sesión expirada. Intenta de nuevo.';
    }

    $datos['nombre']    = trim($_POST['nombre'] ?? '');
    $datos['correo']    = trim($_POST['correo'] ?? '');
    $datos['direccion'] = trim($_POST['direccion'] ?? '');
    $password           = $_POST['password'] ?? '';
    $password2          = $_POST['password2'] ?? '';

    if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 100) {
        $errores[] = 'Ingresa tu nombre (máximo 100 caracteres).';
    }
    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }
    if ($datos['direccion'] === '' || mb_strlen($datos['direccion']) > 200) {
        $errores[] = 'Ingresa tu dirección de entrega (máximo 200 caracteres).';
    }
    if (strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if ($password !== $password2) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
        $pdo = conectar();
        $st = $pdo->prepare('SELECT id FROM clientes WHERE correo = ?');
        $st->execute([$datos['correo']]);
        if ($st->fetch()) {
            $errores[] = 'Ya existe una cuenta con ese correo.';
        } else {
            $st = $pdo->prepare('INSERT INTO clientes (nombre, correo, direccion, password) VALUES (?, ?, ?, ?)');
            $st->execute([
                $datos['nombre'],
                $datos['correo'],
                $datos['direccion'],
                password_hash($password, PASSWORD_DEFAULT),
            ]);
            session_regenerate_id(true);
            $_SESSION['cliente_id']     = (int) $pdo->lastInsertId();
            $_SESSION['cliente_nombre'] = $datos['nombre'];
            setFlash('exito', 'Cuenta creada. Ya puedes comprar.');
            header('Location: index.php');
            exit;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="formulario">
    <h1>Crear cuenta</h1>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta--error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" novalidate id="form-registro">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" required maxlength="100">

        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" value="<?= e($datos['correo']) ?>" required>

        <label for="direccion">Dirección de entrega</label>
        <input type="text" id="direccion" name="direccion" value="<?= e($datos['direccion']) ?>" required maxlength="200">

        <label for="password">Contraseña (mínimo 6 caracteres)</label>
        <input type="password" id="password" name="password" required minlength="6">

        <label for="password2">Repite la contraseña</label>
        <input type="password" id="password2" name="password2" required minlength="6">

        <button type="submit" class="btn btn--primario btn--bloque">Registrarme</button>
    </form>

    <p class="formulario__alt">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
