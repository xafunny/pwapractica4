# Tienda Tech — Clase Práctica 4 (Programación Web Avanzada · UBE)

Sistema de venta de productos con **HTML, CSS, JavaScript, PHP y MySQL**.

## Funcionalidades

**Ejercicio base**
- Base de datos con las tablas `productos`, `vendedores` y `clientes`.
- Catálogo con imagen, descripción, precio y datos del vendedor, leído desde MySQL con PHP.
- Botón **Comprar** en cada producto.
- Estilos CSS responsive.

**Adiciones (mejoras del enunciado)**
- **Inicio de sesión** y registro de clientes (contraseñas con `password_hash`).
- **Carro de compras** dinámico con JavaScript (`fetch`, sin recargar la página).
- **Métodos de pago**: tarjeta (simulada), transferencia y contra entrega.
- **Seguimiento de pedidos**: línea de tiempo con los estados pendiente → preparando → enviado → entregado.
- Extras: búsqueda en vivo, control de stock, validación en cliente y servidor, protección CSRF.

## Estructura

```
index.html           Catálogo para abrir en el navegador (doble clic o servidor)
api_productos.php    Productos en JSON desde MySQL (lo usa index.html)
index.php            Catálogo de productos (versión PHP completa)
carrito.php          Carro de compras
checkout.php         Datos de entrega y método de pago
pedidos.php          Mis pedidos + seguimiento
login.php / registro.php / logout.php
api_carrito.php      Endpoint AJAX del carro (JSON)
config.php           Conexión PDO y sesión
database.sql         Tablas y datos de ejemplo
includes/            header.php, footer.php, funciones.php
images/              Ilustraciones de cada producto (SVG)
css/style.css
js/app.js            Scripts de las páginas PHP
js/tienda-html.js    Script de index.html
```

## Imágenes

Cada producto usa su propia imagen en `images/` y la ruta se guarda en la columna `imagen` de la tabla `productos`. Para usar fotos reales, copia tus archivos a `images/` y cambia esa ruta en phpMyAdmin (por ejemplo `images/laptop.jpg`).

## index.html: dos modos

- **Doble clic (file://)**: modo demostración. Muestra los 8 productos de ejemplo y un carro guardado en el navegador (localStorage). No usa PHP ni MySQL.
- **Con XAMPP/Laragon/hosting**: `index.html` pide los productos a `api_productos.php` y los muestra desde MySQL; el botón Comprar usa el carro de sesión PHP y se habilitan login y pedidos.

> Si abres `http://localhost/<carpeta>/` sin nombre de archivo, Apache sirve `index.php` antes que `index.html`. Para ver el HTML en servidor, escribe `index.html` en la URL.

## Instalación local (XAMPP / Laragon)

1. Copia esta carpeta dentro de `htdocs` (XAMPP) o `www` (Laragon).
2. Abre **phpMyAdmin** → pestaña **Importar** → selecciona `database.sql` → Continuar.
   (Esto crea la base `tienda_tech`.)
3. Revisa `config.php`. Por defecto: usuario `root`, contraseña vacía.
4. Abre `http://localhost/<nombre-carpeta>/index.php`.

## Publicar en un hosting gratuito (InfinityFree, 000webhost, etc.)

1. En el panel del hosting crea una base de datos MySQL y anota: **host, nombre, usuario y contraseña**.
2. Entra a su phpMyAdmin e importa `database.sql`.
   - Si el hosting ya te asignó un nombre de base de datos, **borra las dos primeras líneas** del SQL
     (`CREATE DATABASE ...` y `USE tienda_tech;`) antes de importar, y selecciona tu base primero.
3. Edita `config.php` con los datos reales del hosting (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
4. Sube todos los archivos a la carpeta `htdocs` (con FileZilla o el administrador de archivos).

## Subir a GitHub

```bash
git init
git add .
git commit -m "Clase práctica 4: tienda de productos"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/pwapractica4.git
git push -u origin main
```

> Antes de subir a GitHub, **no publiques contraseñas reales** de tu hosting en `config.php`.

## Flujo para probar

1. Registrarse → 2. Agregar productos con **Comprar** → 3. Ir a **Carrito** y ajustar cantidades →
4. **Finalizar compra** y elegir método de pago → 5. Ver el estado en **Mis pedidos**.

Para ver cómo avanza el seguimiento, cambia el campo `estado` de un pedido en la tabla `pedidos` desde phpMyAdmin.
