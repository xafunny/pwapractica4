<?php
require_once __DIR__ . '/includes/funciones.php';
header('Content-Type: application/json; charset=utf-8');

function responder(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['ok' => false, 'mensaje' => 'Método no permitido'], 405);
}

$accion     = $_POST['accion'] ?? '';
$idProducto = (int) ($_POST['id'] ?? 0);

$st = conectar()->prepare('SELECT id, nombre, stock FROM productos WHERE id = ?');
$st->execute([$idProducto]);
$producto = $st->fetch();

if (!$producto) {
    responder(['ok' => false, 'mensaje' => 'Producto no encontrado'], 404);
}

$carrito = carrito();
$actual  = $carrito[$idProducto] ?? 0;
$mensaje = '';

switch ($accion) {
    case 'agregar':
        if ($actual + 1 > (int) $producto['stock']) {
            responder(['ok' => false, 'mensaje' => 'No hay más stock disponible de ' . $producto['nombre']], 409);
        }
        $carrito[$idProducto] = $actual + 1;
        $mensaje = $producto['nombre'] . ' agregado al carrito';
        break;

    case 'cambiar':
        $cantidad = (int) ($_POST['cantidad'] ?? 1);
        if ($cantidad < 1) {
            unset($carrito[$idProducto]);
        } else {
            $carrito[$idProducto] = min($cantidad, (int) $producto['stock']);
        }
        break;

    case 'quitar':
        unset($carrito[$idProducto]);
        $mensaje = 'Producto eliminado del carrito';
        break;

    default:
        responder(['ok' => false, 'mensaje' => 'Acción inválida'], 400);
}

$_SESSION['carrito'] = $carrito;

responder([
    'ok'        => true,
    'mensaje'   => $mensaje,
    'cantidad'  => carritoCantidadTotal(),
    'total'     => carritoTotal(),
    'total_fmt' => dinero(carritoTotal()),
    'lineas'    => array_map(fn($l) => [
        'id'           => $l['id'],
        'cantidad'     => $l['cantidad'],
        'subtotal_fmt' => dinero($l['subtotal']),
    ], carritoDetalle()),
]);
