(function () {
    'use strict';

    var DEMO = [
        { id: 1, nombre: 'Laptop Pro 14"', descripcion: 'Procesador de 12 núcleos, 16 GB de RAM y SSD de 512 GB. Ideal para programar y estudiar.', precio: 899.99, imagen: 'images/laptop.svg', stock: 8, vendedor: 'TecnoMundo Quito', correo: 'ventas@tecnomundo.ec', telefono: '0991234567' },
        { id: 2, nombre: 'Smartphone X12', descripcion: 'Pantalla AMOLED de 6.5", cámara de 108 MP y batería de 5000 mAh.', precio: 549.50, imagen: 'images/phone.svg', stock: 15, vendedor: 'Digital Store EC', correo: 'contacto@digitalstore.ec', telefono: '0987654321' },
        { id: 3, nombre: 'Auriculares Bluetooth', descripcion: 'Cancelación de ruido activa y hasta 30 horas de batería.', precio: 79.90, imagen: 'images/headphones.svg', stock: 25, vendedor: 'Gadget Hub', correo: 'hola@gadgethub.ec', telefono: '0976543210' },
        { id: 4, nombre: 'Teclado Mecánico RGB', descripcion: 'Switches azules, retroiluminación RGB y construcción en aluminio.', precio: 64.00, imagen: 'images/keyboard.svg', stock: 20, vendedor: 'TecnoMundo Quito', correo: 'ventas@tecnomundo.ec', telefono: '0991234567' },
        { id: 5, nombre: 'Mouse Inalámbrico', descripcion: 'Sensor óptico de 16000 DPI, ergonómico y recargable por USB-C.', precio: 29.99, imagen: 'images/mouse.svg', stock: 40, vendedor: 'Digital Store EC', correo: 'contacto@digitalstore.ec', telefono: '0987654321' },
        { id: 6, nombre: 'Monitor 27" 4K', descripcion: 'Panel IPS UHD, 60 Hz, HDR10 y entradas HDMI/DisplayPort.', precio: 329.00, imagen: 'images/monitor.svg', stock: 6, vendedor: 'Gadget Hub', correo: 'hola@gadgethub.ec', telefono: '0976543210' },
        { id: 7, nombre: 'Smartwatch Fit', descripcion: 'Monitor de ritmo cardíaco, GPS integrado y resistencia al agua 5 ATM.', precio: 119.00, imagen: 'images/watch.svg', stock: 18, vendedor: 'Digital Store EC', correo: 'contacto@digitalstore.ec', telefono: '0987654321' },
        { id: 8, nombre: 'Disco SSD 1TB', descripcion: 'Unidad de estado sólido NVMe con lecturas de hasta 3500 MB/s.', precio: 89.90, imagen: 'images/ssd.svg', stock: 30, vendedor: 'TecnoMundo Quito', correo: 'ventas@tecnomundo.ec', telefono: '0991234567' }
    ];

    var modo = 'demo';
    var productos = [];
    var carritoLocal = {};
    var CLAVE = 'tt_carrito';

    var grilla = document.getElementById('grilla-productos');
    var aviso = document.getElementById('aviso-modo');
    var contador = document.getElementById('contador-carrito');
    var toastEl = document.getElementById('toast');
    var toastTimer = null;

    function esc(t) {
        var d = document.createElement('div');
        d.textContent = t == null ? '' : String(t);
        return d.innerHTML.replace(/"/g, '&quot;');
    }

    function dinero(n) {
        return '$' + Number(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function toast(texto, error) {
        toastEl.textContent = texto;
        toastEl.classList.toggle('error', !!error);
        toastEl.classList.add('visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.classList.remove('visible'); }, 2600);
    }

    function setContador(n) {
        contador.textContent = n;
        contador.classList.remove('rebote');
        void contador.offsetWidth;
        contador.classList.add('rebote');
    }

    function porId(id) {
        return productos.filter(function (p) { return p.id === id; })[0];
    }

    function cargarLocal() {
        try { carritoLocal = JSON.parse(localStorage.getItem(CLAVE)) || {}; }
        catch (e) { carritoLocal = {}; }
    }
    function guardarLocal() {
        try { localStorage.setItem(CLAVE, JSON.stringify(carritoLocal)); } catch (e) { }
    }
    function cantidadLocal() {
        return Object.keys(carritoLocal).reduce(function (s, k) { return s + carritoLocal[k]; }, 0);
    }
    function totalLocal() {
        return Object.keys(carritoLocal).reduce(function (s, k) {
            var p = porId(parseInt(k, 10));
            return s + (p ? p.precio * carritoLocal[k] : 0);
        }, 0);
    }

    function render() {
        grilla.innerHTML = productos.map(function (p) {
            var stock;
            if (p.stock <= 0) stock = '<span class="stock stock--agotado">Agotado</span>';
            else if (p.stock <= 5) stock = '<span class="stock stock--bajo">Quedan ' + p.stock + '</span>';
            else stock = '<span class="stock">En stock</span>';

            return '<article class="tarjeta" data-nombre="' + esc((p.nombre + ' ' + p.vendedor).toLowerCase()) + '">' +
                '<img src="' + esc(p.imagen) + '" alt="' + esc(p.nombre) + '" loading="lazy">' +
                '<div class="tarjeta__cuerpo">' +
                '<h2>' + esc(p.nombre) + '</h2>' +
                '<p class="tarjeta__desc">' + esc(p.descripcion) + '</p>' +
                '<p class="tarjeta__vendedor">Vendedor: <strong>' + esc(p.vendedor) + '</strong><br>' +
                '<small>Tel. ' + esc(p.telefono) + ' | ' + esc(p.correo) + '</small></p>' +
                '<div class="tarjeta__pie"><span class="precio">' + dinero(p.precio) + '</span>' + stock + '</div>' +
                '<button class="btn btn--primario btn-comprar" data-id="' + p.id + '"' + (p.stock <= 0 ? ' disabled' : '') + '>Comprar</button>' +
                '</div></article>';
        }).join('');
        document.getElementById('resultados-texto').textContent =
            productos.length + (productos.length === 1 ? ' producto' : ' productos');
    }

    grilla.addEventListener('click', function (ev) {
        var btn = ev.target.closest('.btn-comprar');
        if (!btn) return;
        var id = parseInt(btn.dataset.id, 10);

        if (modo === 'servidor') {
            btn.disabled = true;
            var datos = new URLSearchParams();
            datos.append('accion', 'agregar');
            datos.append('id', id);
            fetch('api_carrito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: datos.toString(),
                credentials: 'same-origin'
            }).then(function (r) { return r.json(); })
              .then(function (res) {
                  if (res.ok) { setContador(res.cantidad); toast(res.mensaje); }
                  else toast(res.mensaje || 'No se pudo agregar', true);
              })
              .catch(function () { toast('Error de conexión', true); })
              .then(function () { btn.disabled = false; });
            return;
        }

        var p = porId(id);
        var actual = carritoLocal[id] || 0;
        if (actual + 1 > p.stock) { toast('No hay más stock de ' + p.nombre, true); return; }
        carritoLocal[id] = actual + 1;
        guardarLocal();
        setContador(cantidadLocal());
        toast(p.nombre + ' agregado al carrito');
    });

    document.getElementById('buscar').addEventListener('input', function (ev) {
        var q = ev.target.value.trim().toLowerCase();
        var visibles = 0;
        grilla.querySelectorAll('.tarjeta').forEach(function (t) {
            var ok = t.dataset.nombre.indexOf(q) !== -1;
            t.hidden = !ok;
            if (ok) visibles++;
        });
        document.getElementById('resultados-texto').textContent =
            visibles + (visibles === 1 ? ' producto' : ' productos');
        document.getElementById('sin-resultados').hidden = visibles !== 0;
    });

    var panel = document.getElementById('panel-carrito');
    var fondo = document.getElementById('panel-fondo');
    var lineas = document.getElementById('panel-lineas');

    function pintarPanel() {
        var ids = Object.keys(carritoLocal);
        if (!ids.length) {
            lineas.innerHTML = '<p class="vacio">Tu carrito está vacío.</p>';
        } else {
            lineas.innerHTML = ids.map(function (k) {
                var p = porId(parseInt(k, 10));
                if (!p) return '';
                return '<div class="linea" data-id="' + p.id + '">' +
                    '<img src="' + esc(p.imagen) + '" alt="">' +
                    '<div class="linea__info"><strong>' + esc(p.nombre) + '</strong>' +
                    '<span>' + dinero(p.precio * carritoLocal[k]) + '</span></div>' +
                    '<div class="cantidad">' +
                    '<button type="button" class="cantidad__btn" data-delta="-1" aria-label="Disminuir">-</button>' +
                    '<span class="cantidad__num">' + carritoLocal[k] + '</span>' +
                    '<button type="button" class="cantidad__btn" data-delta="1" aria-label="Aumentar">+</button>' +
                    '</div>' +
                    '<button type="button" class="btn-quitar" data-quitar="1" aria-label="Quitar">&times;</button></div>';
            }).join('');
        }
        document.getElementById('panel-total').textContent = dinero(totalLocal());
    }

    function abrirPanel() { pintarPanel(); panel.hidden = false; fondo.hidden = false; }
    function cerrarPanel() { panel.hidden = true; fondo.hidden = true; }

    document.getElementById('enlace-carrito').addEventListener('click', function (ev) {
        if (modo === 'demo') { ev.preventDefault(); abrirPanel(); }
    });
    document.getElementById('cerrar-panel').addEventListener('click', cerrarPanel);
    fondo.addEventListener('click', cerrarPanel);
    document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') cerrarPanel(); });

    lineas.addEventListener('click', function (ev) {
        var linea = ev.target.closest('.linea');
        if (!linea) return;
        var id = parseInt(linea.dataset.id, 10);
        var p = porId(id);

        if (ev.target.closest('[data-quitar]')) {
            delete carritoLocal[id];
        } else {
            var b = ev.target.closest('.cantidad__btn');
            if (!b) return;
            var nueva = (carritoLocal[id] || 0) + parseInt(b.dataset.delta, 10);
            if (nueva < 1) delete carritoLocal[id];
            else if (nueva > p.stock) { toast('Solo hay ' + p.stock + ' unidades', true); return; }
            else carritoLocal[id] = nueva;
        }
        guardarLocal();
        setContador(cantidadLocal());
        pintarPanel();
    });

    document.getElementById('panel-comprar').addEventListener('click', function () {
        if (!cantidadLocal()) { toast('Tu carrito está vacío', true); return; }
        carritoLocal = {};
        guardarLocal();
        setContador(0);
        pintarPanel();
        cerrarPanel();
        toast('Compra simulada. Para guardar pedidos, ejecuta el sitio con PHP y MySQL.');
    });

    fetch('api_productos.php', { credentials: 'same-origin' })
        .then(function (r) {
            if (!r.ok) throw new Error('sin servidor');
            return r.json();
        })
        .then(function (data) {
            if (!data || !Array.isArray(data.productos) || !data.productos.length) throw new Error('sin datos');
            modo = 'servidor';
            productos = data.productos;
            contador.textContent = data.carrito || 0;
            aviso.textContent = 'Conectado a la base de datos MySQL';
            aviso.classList.add('aviso-modo--ok');
            document.querySelectorAll('.solo-servidor').forEach(function (a) { a.hidden = false; });
        })
        .catch(function () {
            modo = 'demo';
            productos = DEMO;
            cargarLocal();
            contador.textContent = cantidadLocal();
            aviso.textContent = 'Modo demostración: datos de ejemplo y carrito guardado en este navegador. ' +
                'Para usar MySQL, inicio de sesión y pedidos, abre el sitio desde XAMPP o Laragon.';
        })
        .then(render);
})();
