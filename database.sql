CREATE DATABASE IF NOT EXISTS tienda_tech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tienda_tech;

DROP TABLE IF EXISTS detalle_pedido;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS vendedores;
DROP TABLE IF EXISTS clientes;

CREATE TABLE vendedores (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  nombre    VARCHAR(100) NOT NULL,
  correo    VARCHAR(120) NOT NULL UNIQUE,
  telefono  VARCHAR(20)  NOT NULL
) ENGINE=InnoDB;

CREATE TABLE productos (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(120) NOT NULL,
  descripcion  TEXT NOT NULL,
  precio       DECIMAL(10,2) NOT NULL,
  imagen       VARCHAR(255) NOT NULL,
  stock        INT NOT NULL DEFAULT 10,
  vendedor_id  INT NOT NULL,
  CONSTRAINT fk_prod_vend FOREIGN KEY (vendedor_id) REFERENCES vendedores(id)
) ENGINE=InnoDB;

CREATE TABLE clientes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(100) NOT NULL,
  correo     VARCHAR(120) NOT NULL UNIQUE,
  direccion  VARCHAR(200) NOT NULL,
  password   VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE pedidos (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id   INT NOT NULL,
  total        DECIMAL(10,2) NOT NULL,
  metodo_pago  ENUM('tarjeta','transferencia','contra_entrega') NOT NULL,
  estado       ENUM('pendiente','preparando','enviado','entregado') NOT NULL DEFAULT 'pendiente',
  fecha        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ped_cli FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB;

CREATE TABLE detalle_pedido (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id        INT NOT NULL,
  producto_id      INT NOT NULL,
  cantidad         INT NOT NULL,
  precio_unitario  DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_det_ped  FOREIGN KEY (pedido_id)   REFERENCES pedidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_det_prod FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

INSERT INTO vendedores (nombre, correo, telefono) VALUES
  ('TecnoMundo Quito', 'ventas@tecnomundo.ec', '0991234567'),
  ('Digital Store EC', 'contacto@digitalstore.ec', '0987654321'),
  ('Gadget Hub', 'hola@gadgethub.ec', '0976543210');

INSERT INTO productos (nombre, descripcion, precio, imagen, stock, vendedor_id) VALUES
  ('Laptop Pro 14"', 'Procesador de 12 núcleos, 16 GB de RAM y SSD de 512 GB. Ideal para programar y estudiar.', 899.99, 'images/laptop.svg', 8, 1),
  ('Smartphone X12', 'Pantalla AMOLED de 6.5", cámara de 108 MP y batería de 5000 mAh.', 549.50, 'images/phone.svg', 15, 2),
  ('Auriculares Bluetooth', 'Cancelación de ruido activa y hasta 30 horas de batería.', 79.90, 'images/headphones.svg', 25, 3),
  ('Teclado Mecánico RGB', 'Switches azules, retroiluminación RGB y construcción en aluminio.', 64.00, 'images/keyboard.svg', 20, 1),
  ('Mouse Inalámbrico', 'Sensor óptico de 16000 DPI, ergonómico y recargable por USB-C.', 29.99, 'images/mouse.svg', 40, 2),
  ('Monitor 27" 4K', 'Panel IPS UHD, 60 Hz, HDR10 y entradas HDMI/DisplayPort.', 329.00, 'images/monitor.svg', 6, 3),
  ('Smartwatch Fit', 'Monitor de ritmo cardíaco, GPS integrado y resistencia al agua 5 ATM.', 119.00, 'images/watch.svg', 18, 2),
  ('Disco SSD 1TB', 'Unidad de estado sólido NVMe con lecturas de hasta 3500 MB/s.', 89.90, 'images/ssd.svg', 30, 1);
