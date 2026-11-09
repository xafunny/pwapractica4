<?php
$tituloPagina = 'Finalizar compra';
require_once __DIR__ . '/includes/funciones.php';
requiereLogin();

$lineas = carritoDetalle();
if (!$lineas) {
    setFlash('error', 'Tu carrito está vacío.');
    header('Location: carrito.php');
    exit;
}

$pdo = conectar();
$st = $pdo->prepare('SELECT nombre, correo, direccion FROM clientes WHERE id = ?');
$st->execute([$_SESSION['cliente_id']]);
$cliente = $st->fetch();

$errores = [];
$metodosValidos = ['tarjeta', 'transferencia', 'contra_entrega'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf'] ?? null)) {
        $errores[] = 'Sesión expirada. Intenta de nuevo.';
    }

    $metodo    = $_POST['metodo_pago'] ?? '';
    $direccion = trim($_POST['direccion'] ?? '');

    if (!in_array($metodo, $metodosValidos, true)) {
        $errores[] = 'Elige un método de pago.';
    }
    if ($direccion === '' || mb_strlen($direccion) > 200) {
        $errores[] = 'Ingresa una dirección de entrega válida.';
    }
    if ($metodo === 'tarjeta') {
        $numero = preg_replace('/\D/', '', $_POST['tarjeta_numero'] ?? '');
        if (strlen($numero) < 13 || strlen($numero) > 19) {
            $errores[] = 'El número de tarjeta no es válido.';
        }
    }

    if (!$errores) {
        try {
            $pdo->beginTransaction();

            $total = 0.0;
            foreach ($lineas as $l) {
                $s = $pdo->prepare('SELECT stock, precio FROM productos WHERE id = ? FOR UPDATE');
                $s->execute([$l['id']]);
                $fila = $s->fetch();
                if (!$fila || (int) $fila['stock'] < $l['cantidad']) {
                    throw new RuntimeException('Stock insuficiente de "' . $l['nombre'] . '".');
                }
                $total += $l['cantidad'] * (float) $fila['precio'];
            }

            $ins = $pdo->prepare('INSERT INTO pedidos (cliente_id, total, metodo_pago, estado) VALUES (?, ?, ?, ?)');
            $ins->execute([$_SESSION['cliente_id'], $total, $metodo, 'pendiente']);
            $pedidoId = (int) $pdo->lastInsertId();

            $det = $pdo->prepare('INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)');
            $baja = $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?');
            foreach ($lineas as $l) {
                $det->execute([$pedidoId, $l['id'], $l['cantidad'], $l['precio']]);
                $baja->execute([$l['cantidad'], $l['id']]);
            }

            $pdo->prepare('UPDATE clientes SET direccion = ? WHERE id = ?')->execute([$direccion, $_SESSION['cliente_id']]);

            $pdo->commit();
            unset($_SESSION['carrito']);
            setFlash('exito', 'Compra realizada. Tu pedido #' . $pedidoId . ' fue registrado.');
            header('Location: pedidos.php?nuevo=' . $pedidoId);
            exit;
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errores[] = $ex instanceof RuntimeException
                ? $ex->getMessage()
                : 'No se pudo procesar el pedido. Intenta de nuevo.';
        }
    }
}

require __DIR__ . '/includes/header.php';
$metodoSel = $_POST['metodo_pago'] ?? 'tarjeta';
?>

<h1>Finalizar compra</h1>

<?php foreach ($errores as $err): ?>
    <div class="alerta alerta--error"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="checkout">
    <form method="post" class="formulario formulario--ancho" id="form-checkout" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

        <label for="direccion">Dirección de entrega</label>
        <input type="text" id="direccion" name="direccion" value="<?= e($_POST['direccion'] ?? $cliente['direccion']) ?>" required maxlength="200">

        <fieldset class="metodos">
            <legend>Método de pago</legend>

            <label class="metodo">
                <input type="radio" name="metodo_pago" value="tarjeta" <?= $metodoSel === 'tarjeta' ? 'checked' : '' ?>>
                <span>Tarjeta de crédito/débito</span>
            </label>
            <div class="metodo__extra" id="extra-tarjeta">
                <input type="text" name="tarjeta_numero" inputmode="numeric" autocomplete="off" placeholder="Número de tarjeta" maxlength="23">
                <small>Simulación académica: no se realiza ningún cobro real. No ingreses datos reales de tu tarjeta.</small>
            </div>

            <label class="metodo">
                <input type="radio" name="metodo_pago" value="transferencia" <?= $metodoSel === 'transferencia' ? 'checked' : '' ?>>
                <span>Transferencia bancaria</span>
            </label>
            <div class="metodo__extra" id="extra-transferencia" hidden>
                <small>Recibirás los datos bancarios del vendedor por correo para completar el pago.</small>
            </div>

            <label class="metodo">
                <input type="radio" name="metodo_pago" value="contra_entrega" <?= $metodoSel === 'contra_entrega' ? 'checked' : '' ?>>
                <span>Pago contra entrega</span>
            </label>
            <div class="metodo__extra" id="extra-contra_entrega" hidden>
                <small>Pagas en efectivo cuando recibas el producto.</small>
            </div>
        </fieldset>

        <button type="submit" class="btn btn--primario btn--bloque">Confirmar compra | <?= dinero(carritoTotal()) ?></button>
    </form>

    <aside class="resumen-lateral">
        <h2>Resumen</h2>
        <ul>
        <?php foreach ($lineas as $l): ?>
            <li>
                <span><?= (int) $l['cantidad'] ?> x <?= e($l['nombre']) ?></span>
                <strong><?= dinero($l['subtotal']) ?></strong>
            </li>
        <?php endforeach; ?>
        </ul>
        <p class="resumen-lateral__total">Total <strong><?= dinero(carritoTotal()) ?></strong></p>
    </aside>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
