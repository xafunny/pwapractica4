<?php
$tituloPagina = 'Productos';
require __DIR__ . '/includes/header.php';

$productos = obtenerProductos();
?>

<section class="hero">
    <h1>Tecnología al mejor precio</h1>
    <p>Compra directamente a vendedores verificados. Envíos a todo el país.</p>
</section>

<div class="buscador">
    <input type="search" id="buscar" placeholder="Buscar producto o vendedor" aria-label="Buscar productos">
    <span id="resultados-texto" class="buscador__texto"><?= count($productos) ?> productos</span>
</div>

<section class="grilla" id="grilla-productos">
<?php foreach ($productos as $p): ?>
    <article class="tarjeta"
             data-nombre="<?= e(mb_strtolower($p['nombre'] . ' ' . $p['vendedor'])) ?>">
        <img src="<?= e($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>" loading="lazy">
        <div class="tarjeta__cuerpo">
            <h2><?= e($p['nombre']) ?></h2>
            <p class="tarjeta__desc"><?= e($p['descripcion']) ?></p>
            <p class="tarjeta__vendedor">
                Vendedor: <strong><?= e($p['vendedor']) ?></strong><br>
                <small>Tel. <?= e($p['vendedor_telefono']) ?> | <?= e($p['vendedor_correo']) ?></small>
            </p>
            <div class="tarjeta__pie">
                <span class="precio"><?= dinero((float) $p['precio']) ?></span>
                <?php if ((int) $p['stock'] > 0): ?>
                    <span class="stock <?= $p['stock'] <= 5 ? 'stock--bajo' : '' ?>">
                        <?= $p['stock'] <= 5 ? 'Quedan ' . (int) $p['stock'] : 'En stock' ?>
                    </span>
                <?php else: ?>
                    <span class="stock stock--agotado">Agotado</span>
                <?php endif; ?>
            </div>
            <button class="btn btn--primario btn-comprar"
                    data-id="<?= (int) $p['id'] ?>"
                    <?= (int) $p['stock'] <= 0 ? 'disabled' : '' ?>>
                Comprar
            </button>
        </div>
    </article>
<?php endforeach; ?>
</section>

<p id="sin-resultados" class="vacio" hidden>No encontramos productos con esa búsqueda.</p>

<?php require __DIR__ . '/includes/footer.php'; ?>
