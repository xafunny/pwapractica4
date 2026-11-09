(function () {
    'use strict';

    var toastEl = document.getElementById('toast');
    var toastTimer = null;

    function mostrarToast(texto, esError) {
        if (!toastEl || !texto) return;
        toastEl.textContent = texto;
        toastEl.classList.toggle('error', !!esError);
        toastEl.classList.add('visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toastEl.classList.remove('visible');
        }, 2600);
    }

    function actualizarContador(cantidad) {
        var c = document.getElementById('contador-carrito');
        if (!c) return;
        c.textContent = cantidad;
        c.classList.remove('rebote');
        void c.offsetWidth;
        c.classList.add('rebote');
    }

    function llamarApi(accion, id, extra) {
        var datos = new URLSearchParams();
        datos.append('accion', accion);
        datos.append('id', id);
        if (extra) {
            Object.keys(extra).forEach(function (k) { datos.append(k, extra[k]); });
        }
        return fetch('api_carrito.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: datos.toString(),
            credentials: 'same-origin'
        }).then(function (r) {
            return r.json().then(function (json) {
                json.status = r.status;
                return json;
            });
        });
    }

    document.querySelectorAll('.btn-comprar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.disabled = true;
            llamarApi('agregar', btn.dataset.id)
                .then(function (res) {
                    if (res.ok) {
                        actualizarContador(res.cantidad);
                        mostrarToast(res.mensaje);
                    } else {
                        mostrarToast(res.mensaje || 'No se pudo agregar', true);
                    }
                })
                .catch(function () { mostrarToast('Error de conexión', true); })
                .then(function () { btn.disabled = false; });
        });
    });

    var buscador = document.getElementById('buscar');
    if (buscador) {
        var tarjetas = document.querySelectorAll('#grilla-productos .tarjeta');
        var textoRes = document.getElementById('resultados-texto');
        var sinRes = document.getElementById('sin-resultados');

        buscador.addEventListener('input', function () {
            var q = buscador.value.trim().toLowerCase();
            var visibles = 0;
            tarjetas.forEach(function (t) {
                var coincide = t.dataset.nombre.indexOf(q) !== -1;
                t.hidden = !coincide;
                if (coincide) visibles++;
            });
            textoRes.textContent = visibles + (visibles === 1 ? ' producto' : ' productos');
            sinRes.hidden = visibles !== 0;
        });
    }

    var tabla = document.getElementById('tabla-carrito');
    if (tabla) {
        function aplicarRespuesta(res) {
            if (!res.ok) {
                mostrarToast(res.mensaje || 'Error', true);
                return;
            }
            actualizarContador(res.cantidad);
            document.getElementById('total-carrito').textContent = res.total_fmt;

            var idsVigentes = {};
            res.lineas.forEach(function (l) {
                idsVigentes[l.id] = l;
                var f = tabla.querySelector('tr[data-id="' + l.id + '"]');
                if (f) {
                    f.querySelector('.subtotal').textContent = l.subtotal_fmt;
                    f.querySelector('.cantidad__input').value = l.cantidad;
                }
            });
            tabla.querySelectorAll('tbody tr').forEach(function (f) {
                if (!idsVigentes[f.dataset.id]) f.remove();
            });

            if (res.mensaje) mostrarToast(res.mensaje);

            if (res.lineas.length === 0) {
                document.getElementById('carrito-contenido').hidden = true;
                document.getElementById('carrito-vacio').hidden = false;
            }
        }

        function cambiarCantidad(fila, nuevaCantidad) {
            var stock = parseInt(fila.dataset.stock, 10);
            if (nuevaCantidad > stock) {
                mostrarToast('Solo hay ' + stock + ' unidades disponibles', true);
                nuevaCantidad = stock;
            }
            llamarApi('cambiar', fila.dataset.id, { cantidad: nuevaCantidad })
                .then(aplicarRespuesta)
                .catch(function () { mostrarToast('Error de conexión', true); });
        }

        tabla.addEventListener('click', function (ev) {
            var fila = ev.target.closest('tr[data-id]');
            if (!fila) return;

            var btnCant = ev.target.closest('.cantidad__btn');
            if (btnCant) {
                var input = fila.querySelector('.cantidad__input');
                var nueva = parseInt(input.value, 10) + parseInt(btnCant.dataset.delta, 10);
                cambiarCantidad(fila, nueva);
                return;
            }

            if (ev.target.closest('.btn-quitar')) {
                llamarApi('quitar', fila.dataset.id)
                    .then(aplicarRespuesta)
                    .catch(function () { mostrarToast('Error de conexión', true); });
            }
        });

        tabla.addEventListener('change', function (ev) {
            if (!ev.target.classList.contains('cantidad__input')) return;
            var fila = ev.target.closest('tr[data-id]');
            var valor = parseInt(ev.target.value, 10);
            cambiarCantidad(fila, isNaN(valor) ? 1 : valor);
        });
    }

    var radios = document.querySelectorAll('input[name="metodo_pago"]');
    if (radios.length) {
        function mostrarExtra() {
            radios.forEach(function (r) {
                var extra = document.getElementById('extra-' + r.value);
                if (extra) extra.hidden = !r.checked;
            });
        }
        radios.forEach(function (r) { r.addEventListener('change', mostrarExtra); });
        mostrarExtra();

        var tarjeta = document.querySelector('input[name="tarjeta_numero"]');
        if (tarjeta) {
            tarjeta.addEventListener('input', function () {
                var soloDigitos = tarjeta.value.replace(/\D/g, '').slice(0, 19);
                tarjeta.value = soloDigitos.replace(/(.{4})/g, '$1 ').trim();
            });
        }
    }

    function marcar(campo, valido) {
        campo.classList.toggle('invalido', !valido);
        return valido;
    }

    var formReg = document.getElementById('form-registro');
    if (formReg) {
        formReg.addEventListener('submit', function (ev) {
            var ok = true;
            ok = marcar(formReg.nombre, formReg.nombre.value.trim() !== '') && ok;
            ok = marcar(formReg.correo, /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formReg.correo.value)) && ok;
            ok = marcar(formReg.direccion, formReg.direccion.value.trim() !== '') && ok;
            ok = marcar(formReg.password, formReg.password.value.length >= 6) && ok;
            ok = marcar(formReg.password2, formReg.password2.value === formReg.password.value) && ok;
            if (!ok) {
                ev.preventDefault();
                mostrarToast('Revisa los campos marcados en rojo', true);
            }
        });
    }

    var formCheckout = document.getElementById('form-checkout');
    if (formCheckout) {
        formCheckout.addEventListener('submit', function (ev) {
            var ok = marcar(formCheckout.direccion, formCheckout.direccion.value.trim() !== '');
            var metodo = formCheckout.querySelector('input[name="metodo_pago"]:checked');
            if (metodo && metodo.value === 'tarjeta') {
                var digitos = formCheckout.tarjeta_numero.value.replace(/\D/g, '');
                ok = marcar(formCheckout.tarjeta_numero, digitos.length >= 13 && digitos.length <= 19) && ok;
            }
            if (!ok) {
                ev.preventDefault();
                mostrarToast('Revisa los campos marcados en rojo', true);
            }
        });
    }
})();
