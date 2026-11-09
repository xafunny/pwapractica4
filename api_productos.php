<?php
require_once __DIR__ . '/includes/funciones.php';
header('Content-Type: application/json; charset=utf-8');

$productos = array_map(function (array $p): array {
    return [
        'id'          => (int) $p['id'],
        'nombre'      => $p['nombre'],
        'descripcion' => $p['descripcion'],
        'precio'      => (float) $p['precio'],
        'imagen'      => $p['imagen'],
        'stock'       => (int) $p['stock'],
        'vendedor'    => $p['vendedor'],
        'correo'      => $p['vendedor_correo'],
        'telefono'    => $p['vendedor_telefono'],
    ];
}, obtenerProductos());

echo json_encode([
    'productos' => $productos,
    'carrito'   => carritoCantidadTotal(),
    'logueado'  => estaLogueado(),
], JSON_UNESCAPED_UNICODE);
