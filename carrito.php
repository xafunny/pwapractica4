<?php
$tituloPagina = 'Carrito';
require __DIR__ . '/includes/header.php';

$lineas = carritoDetalle();
?>

<h1>Tu carrito</h1>

<?php if (!$lineas): ?>
    <div class="vacio" id="carrito-vacio">
        <p>Tu carrito está vacío.</p>
        <a href="index.php" class="btn btn--primario">Ver productos</a>
    </div>
<?php else: ?>
    <div id="carrito-contenido">
        <div class="tabla-wrap">
            <table class="tabla" id="tabla-carrito">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($lineas as $l): ?>
                    <tr data-id="<?= $l['id'] ?>" data-stock="<?= $l['stock'] ?>">
                        <td class="tabla__producto">
                            <img src="<?= e($l['imagen']) ?>" alt="">
                            <span><?= e($l['nombre']) ?></span>
                        </td>
                        <td><?= dinero($l['precio']) ?></td>
                        <td>
                            <div class="cantidad">
                                <button type="button" class="cantidad__btn" data-delta="-1" aria-label="Disminuir">-</button>
                                <input type="number" class="cantidad__input" min="1" max="<?= $l['stock'] ?>" value="<?= $l['cantidad'] ?>" aria-label="Cantidad">
                                <button type="button" class="cantidad__btn" data-delta="1" aria-label="Aumentar">+</button>
                            </div>
                        </td>
                        <td class="subtotal"><?= dinero($l['subtotal']) ?></td>
                        <td><button type="button" class="btn-quitar" aria-label="Quitar">&times;</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="resumen">
            <p>Total: <strong id="total-carrito"><?= dinero(carritoTotal()) ?></strong></p>
            <a href="index.php" class="btn">Seguir comprando</a>
            <a href="checkout.php" class="btn btn--primario">Finalizar compra</a>
        </div>
    </div>

    <div class="vacio" id="carrito-vacio" hidden>
        <p>Tu carrito está vacío.</p>
        <a href="index.php" class="btn btn--primario">Ver productos</a>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
