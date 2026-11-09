<?php
require_once __DIR__ . '/../config.php';

function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function dinero(float $valor): string
{
    return '$' . number_format($valor, 2, '.', ',');
}

function estaLogueado(): bool
{
    return isset($_SESSION['cliente_id']);
}

function requiereLogin(): void
{
    if (!estaLogueado()) {
        $_SESSION['volver_a'] = basename($_SERVER['PHP_SELF']);
        header('Location: login.php');
        exit;
    }
}

function setFlash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrfValido(?string $token): bool
{
    return isset($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

function obtenerProductos(): array
{
    $sql = 'SELECT p.*, v.nombre AS vendedor, v.correo AS vendedor_correo, v.telefono AS vendedor_telefono
            FROM productos p
            JOIN vendedores v ON v.id = p.vendedor_id
            ORDER BY p.id';
    return conectar()->query($sql)->fetchAll();
}

function obtenerProductosPorIds(array $ids): array
{
    if (!$ids) {
        return [];
    }
    $ids = array_map('intval', $ids);
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $st = conectar()->prepare("SELECT * FROM productos WHERE id IN ($marcadores)");
    $st->execute($ids);
    return $st->fetchAll();
}

function carrito(): array
{
    return $_SESSION['carrito'] ?? [];
}

function carritoCantidadTotal(): int
{
    return array_sum(carrito());
}

function carritoDetalle(): array
{
    $items = carrito();
    $lineas = [];
    foreach (obtenerProductosPorIds(array_keys($items)) as $p) {
        $cant = (int) $items[$p['id']];
        $lineas[] = [
            'id'       => (int) $p['id'],
            'nombre'   => $p['nombre'],
            'imagen'   => $p['imagen'],
            'precio'   => (float) $p['precio'],
            'stock'    => (int) $p['stock'],
            'cantidad' => $cant,
            'subtotal' => $cant * (float) $p['precio'],
        ];
    }
    return $lineas;
}

function carritoTotal(): float
{
    return array_sum(array_column(carritoDetalle(), 'subtotal'));
}

function estadosPedido(): array
{
    return [
        'pendiente'  => 'Pedido recibido',
        'preparando' => 'Preparando',
        'enviado'    => 'En camino',
        'entregado'  => 'Entregado',
    ];
}

function nombreMetodoPago(string $clave): string
{
    return [
        'tarjeta'        => 'Tarjeta de crédito/débito',
        'transferencia'  => 'Transferencia bancaria',
        'contra_entrega' => 'Pago contra entrega',
    ][$clave] ?? $clave;
}
