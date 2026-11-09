<?php
$tituloPagina = 'Mis pedidos';
require_once __DIR__ . '/includes/funciones.php';
requiereLogin();

$pdo = conectar();
$st = $pdo->prepare('SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY fecha DESC');
$st->execute([$_SESSION['cliente_id']]);
$pedidos = $st->fetchAll();

$detalleStmt = $pdo->prepare(
    'SELECT d.cantidad, d.precio_unitario, p.nombre
     FROM detalle_pedido d JOIN productos p ON p.id = d.producto_id
     WHERE d.pedido_id = ?'
);

$estados     = estadosPedido();
$clavesOrden = array_keys($estados);
$nuevo       = (int) ($_GET['nuevo'] ?? 0);

require __DIR__ . '/includes/header.php';
?>

<h1>Mis pedidos</h1>

<?php if (!$pedidos): ?>
    <div class="vacio">
        <p>Aún no has realizado ninguna compra.</p>
        <a href="index.php" class="btn btn--primario">Ir a la tienda</a>
    </div>
<?php endif; ?>

<?php foreach ($pedidos as $ped):
    $detalleStmt->execute([$ped['id']]);
    $items    = $detalleStmt->fetchAll();
    $posicion = array_search($ped['estado'], $clavesOrden, true);
?>
    <article class="pedido <?= (int) $ped['id'] === $nuevo ? 'pedido--nuevo' : '' ?>">
        <header class="pedido__cabecera">
            <div>
                <h2>Pedido #<?= (int) $ped['id'] ?></h2>
                <small><?= e(date('d/m/Y H:i', strtotime($ped['fecha']))) ?> | <?= e(nombreMetodoPago($ped['metodo_pago'])) ?></small>
            </div>
            <strong class="pedido__total"><?= dinero((float) $ped['total']) ?></strong>
        </header>

        <ol class="seguimiento" aria-label="Estado del pedido">
        <?php foreach ($estados as $clave => $etiqueta):
            $i = array_search($clave, $clavesOrden, true);
            $clase = $i < $posicion ? 'hecho' : ($i === $posicion ? 'actual' : '');
        ?>
            <li class="<?= $clase ?>"><span><?= e($etiqueta) ?></span></li>
        <?php endforeach; ?>
        </ol>

        <ul class="pedido__items">
        <?php foreach ($items as $it): ?>
            <li>
                <?= (int) $it['cantidad'] ?> x <?= e($it['nombre']) ?>
                <span><?= dinero($it['cantidad'] * (float) $it['precio_unitario']) ?></span>
            </li>
        <?php endforeach; ?>
        </ul>
    </article>
<?php endforeach; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
