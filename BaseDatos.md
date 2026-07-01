-- ============================================================
-- BASE DE DATOS ECOMMERCE / TIENDA DE ROPA
-- PRODUCTO GENERAL + VARIANTES POR TALLA Y COLOR
-- INVENTARIO: FIFO + STOCK MINIMO / PUNTO DE REORDEN
-- COSTO: PROMEDIO PONDERADO + FIFO DE COSTO
-- ============================================================

-- ============================================================
-- USUARIOS Y ROLES
-- ============================================================

CREATE TABLE usuario (
id_usuario SERIAL PRIMARY KEY,
nombre VARCHAR(100) NOT NULL,
apellidos VARCHAR(100),
ci VARCHAR(30),
email VARCHAR(150),
telefono VARCHAR(30),
direccion TEXT,
foto TEXT,
estado VARCHAR(30),
usuario_sistema VARCHAR(100),
password VARCHAR(255),
fecha_nacimiento DATE
);

CREATE TABLE cliente (
id_cliente INTEGER PRIMARY KEY,
tipo_cliente VARCHAR(50),
limite_credito NUMERIC(12,2),
credito_disponible NUMERIC(12,2),
credito_utilizado NUMERIC(12,2),

    CONSTRAINT fk_cliente_usuario
        FOREIGN KEY (id_cliente) REFERENCES usuario(id_usuario)

);

CREATE TABLE vendedor (
id_vendedor INTEGER PRIMARY KEY,
comision_porcentaje NUMERIC(5,2),
total_ventas NUMERIC(12,2),

    CONSTRAINT fk_vendedor_usuario
        FOREIGN KEY (id_vendedor) REFERENCES usuario(id_usuario)

);

CREATE TABLE propietario (
id_propietario INTEGER PRIMARY KEY,
nivel_acceso VARCHAR(50),

    CONSTRAINT fk_propietario_usuario
        FOREIGN KEY (id_propietario) REFERENCES usuario(id_usuario)

);

-- ============================================================
-- PROVEEDORES Y CATEGORIAS
-- ============================================================

CREATE TABLE proveedor (
id SERIAL PRIMARY KEY,
nombre VARCHAR(150) NOT NULL,
nit VARCHAR(30),
telefono VARCHAR(30),
email VARCHAR(150),
direccion TEXT,
estado VARCHAR(30),
fecha_creacion DATE,
fecha_modificacion DATE
);

CREATE TABLE categoria (
id SERIAL PRIMARY KEY,
id_propietario INTEGER NOT NULL,
nombre VARCHAR(100) NOT NULL,
descripcion TEXT,
fecha_creacion DATE,
fecha_modificacion DATE,

    CONSTRAINT fk_categoria_propietario
        FOREIGN KEY (id_propietario) REFERENCES propietario(id_propietario)

);

-- ============================================================
-- TECNICAS DE INVENTARIO Y COSTO
-- ============================================================

CREATE TABLE tecnica_inventario (
id SERIAL PRIMARY KEY,
nombre VARCHAR(100) NOT NULL,
codigo VARCHAR(50) UNIQUE NOT NULL,
descripcion TEXT,
estado VARCHAR(30) DEFAULT 'ACTIVO'
);

CREATE TABLE tecnica_costo (
id SERIAL PRIMARY KEY,
nombre VARCHAR(100) NOT NULL,
codigo VARCHAR(50) UNIQUE NOT NULL,
descripcion TEXT,
estado VARCHAR(30) DEFAULT 'ACTIVO'
);

-- ============================================================
-- PRODUCTOS
-- producto = modelo general
-- producto_variante = talla/color/SKU/stock/costo
-- ============================================================

CREATE TABLE producto (
id SERIAL PRIMARY KEY,
id_categoria INTEGER NOT NULL,

    nombre VARCHAR(150) NOT NULL,
    codigo VARCHAR(50),
    imagen TEXT,
    qr TEXT,
    descripcion TEXT,

    marca VARCHAR(100),
    genero VARCHAR(30),

    precio_venta_base NUMERIC(12,2),
    precio_venta_mayorista_base NUMERIC(12,2),

    estado VARCHAR(30),
    fecha_creacion DATE,
    fecha_modificacion DATE,

    CONSTRAINT fk_producto_categoria
        FOREIGN KEY (id_categoria) REFERENCES categoria(id)

);

CREATE TABLE producto_variante (
id SERIAL PRIMARY KEY,
id_producto INTEGER NOT NULL,

    sku VARCHAR(80) UNIQUE,

    talla VARCHAR(30),
    color VARCHAR(50),

    precio_compra NUMERIC(12,2),
    precio_venta NUMERIC(12,2),
    precio_venta_mayorista NUMERIC(12,2),

    stock_actual INTEGER DEFAULT 0,
    stock_minimo INTEGER DEFAULT 0,
    punto_reorden INTEGER DEFAULT 0,

    costo_promedio NUMERIC(12,2) DEFAULT 0,

    id_tecnica_inventario INTEGER,
    id_tecnica_costo INTEGER,

    estado VARCHAR(30) DEFAULT 'ACTIVO',
    fecha_creacion DATE,
    fecha_modificacion DATE,

    CONSTRAINT fk_producto_variante_producto
        FOREIGN KEY (id_producto) REFERENCES producto(id),

    CONSTRAINT fk_producto_variante_tecnica_inventario
        FOREIGN KEY (id_tecnica_inventario) REFERENCES tecnica_inventario(id),

    CONSTRAINT fk_producto_variante_tecnica_costo
        FOREIGN KEY (id_tecnica_costo) REFERENCES tecnica_costo(id)

);

-- ============================================================
-- PROMOCIONES
-- Promoción por categoría o producto general.
-- No se maneja promoción por variante.
-- ============================================================

CREATE TABLE promocion (
id SERIAL PRIMARY KEY,
id_propietario INTEGER NOT NULL,
nombre VARCHAR(100) NOT NULL,
descripcion TEXT,
porcentaje_descuento NUMERIC(5,2),
fecha_inicio DATE,
fecha_fin DATE,
estado VARCHAR(30),
fecha_creacion DATE,
fecha_modificacion DATE,

    CONSTRAINT fk_promocion_propietario
        FOREIGN KEY (id_propietario) REFERENCES propietario(id_propietario)

);

CREATE TABLE promocion_categoria (
id_promocion INTEGER NOT NULL,
id_categoria INTEGER NOT NULL,
aplica_mayorista BOOLEAN,
aplica_minorista BOOLEAN,

    PRIMARY KEY (id_promocion, id_categoria),

    CONSTRAINT fk_promocion_categoria_promocion
        FOREIGN KEY (id_promocion) REFERENCES promocion(id),

    CONSTRAINT fk_promocion_categoria_categoria
        FOREIGN KEY (id_categoria) REFERENCES categoria(id)

);

CREATE TABLE promocion_producto (
id_promocion INTEGER NOT NULL,
id_producto INTEGER NOT NULL,
aplica_mayorista BOOLEAN,
aplica_minorista BOOLEAN,

    PRIMARY KEY (id_promocion, id_producto),

    CONSTRAINT fk_promocion_producto_promocion
        FOREIGN KEY (id_promocion) REFERENCES promocion(id),

    CONSTRAINT fk_promocion_producto_producto
        FOREIGN KEY (id_producto) REFERENCES producto(id)

);

-- ============================================================
-- COMPRAS
-- La compra entra a una variante exacta.
-- Ejemplo: Polera Oversize / Negro / M
-- ============================================================

CREATE TABLE compra (
id SERIAL PRIMARY KEY,
id_propietario INTEGER NOT NULL,
id_proveedor INTEGER NOT NULL,
num_compra VARCHAR(50),
precio_total NUMERIC(12,2),
fecha_compra DATE,

    CONSTRAINT fk_compra_propietario
        FOREIGN KEY (id_propietario) REFERENCES propietario(id_propietario),

    CONSTRAINT fk_compra_proveedor
        FOREIGN KEY (id_proveedor) REFERENCES proveedor(id)

);

CREATE TABLE detalle_compra (
id_detalle SERIAL PRIMARY KEY,
id_compra INTEGER NOT NULL,
id_producto_variante INTEGER NOT NULL,

    cantidad INTEGER NOT NULL,
    precio NUMERIC(12,2),
    descuento NUMERIC(12,2),
    subtotal NUMERIC(12,2),

    CONSTRAINT fk_detalle_compra_compra
        FOREIGN KEY (id_compra) REFERENCES compra(id),

    CONSTRAINT fk_detalle_compra_producto_variante
        FOREIGN KEY (id_producto_variante) REFERENCES producto_variante(id)

);

-- ============================================================
-- LOTES DE INVENTARIO
-- Necesario para FIFO físico y FIFO de costo.
-- ============================================================

CREATE TABLE lote_inventario (
id SERIAL PRIMARY KEY,
id_producto_variante INTEGER NOT NULL,
id_detalle_compra INTEGER NOT NULL,

    fecha_ingreso DATE NOT NULL,
    cantidad_inicial INTEGER NOT NULL,
    cantidad_disponible INTEGER NOT NULL,

    costo_unitario NUMERIC(12,2) NOT NULL,
    estado VARCHAR(30) DEFAULT 'DISPONIBLE',

    CONSTRAINT fk_lote_producto_variante
        FOREIGN KEY (id_producto_variante) REFERENCES producto_variante(id),

    CONSTRAINT fk_lote_detalle_compra
        FOREIGN KEY (id_detalle_compra) REFERENCES detalle_compra(id_detalle)

);

-- ============================================================
-- CARRITO
-- El carrito apunta a producto_variante porque el cliente elige talla/color.
-- ============================================================

CREATE TABLE carrito (
id SERIAL PRIMARY KEY,
id_cliente INTEGER NOT NULL,
fecha_creacion DATE,
fecha_actualizacion DATE,

    CONSTRAINT fk_carrito_cliente
        FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)

);

CREATE TABLE carrito_detalle (
id_carrito INTEGER NOT NULL,
id_producto_variante INTEGER NOT NULL,

    cantidad INTEGER NOT NULL,
    precio_unitario NUMERIC(12,2),
    descuento NUMERIC(12,2),

    PRIMARY KEY (id_carrito, id_producto_variante),

    CONSTRAINT fk_carrito_detalle_carrito
        FOREIGN KEY (id_carrito) REFERENCES carrito(id),

    CONSTRAINT fk_carrito_detalle_producto_variante
        FOREIGN KEY (id_producto_variante) REFERENCES producto_variante(id)

);

-- ============================================================
-- PEDIDOS
-- El pedido_detalle también apunta a producto_variante.
-- ============================================================

CREATE TABLE pedido (
id SERIAL PRIMARY KEY,
id_cliente INTEGER NOT NULL,
id_carrito INTEGER NOT NULL,
numero_pedido VARCHAR(50),
fecha_pedido TIMESTAMP,
fecha_pago TIMESTAMP,
fecha_envio TIMESTAMP,
fecha_entrega TIMESTAMP,
fecha_recibido_conforme TIMESTAMP,
tipo_venta VARCHAR(50),
subtotal NUMERIC(12,2),
descuento NUMERIC(12,2),
total NUMERIC(12,2),
estado VARCHAR(50),
direccion_entrega TEXT,
observaciones TEXT,

    CONSTRAINT fk_pedido_cliente
        FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),

    CONSTRAINT fk_pedido_carrito
        FOREIGN KEY (id_carrito) REFERENCES carrito(id)

);

CREATE TABLE pedido_detalle (
id_detalle SERIAL PRIMARY KEY,
id_pedido INTEGER NOT NULL,
id_producto_variante INTEGER NOT NULL,

    cantidad INTEGER NOT NULL,
    precio_unitario NUMERIC(12,2),
    subtotal NUMERIC(12,2),
    descuento NUMERIC(12,2),

    CONSTRAINT fk_pedido_detalle_pedido
        FOREIGN KEY (id_pedido) REFERENCES pedido(id),

    CONSTRAINT fk_pedido_detalle_producto_variante
        FOREIGN KEY (id_producto_variante) REFERENCES producto_variante(id)

);

-- ============================================================
-- VENTAS
-- detalle_venta apunta a producto_variante.
-- Aquí se calcula costo y utilidad.
-- ============================================================

CREATE TABLE venta (
id SERIAL PRIMARY KEY,
id_vendedor INTEGER,
id_cliente INTEGER NOT NULL,
id_pedido INTEGER,
numero_venta VARCHAR(50),
tipo VARCHAR(50),
origen VARCHAR(50),
tipo_pago VARCHAR(50),
total NUMERIC(12,2),
estado VARCHAR(50),
observaciones TEXT,
fecha_venta DATE,

    CONSTRAINT fk_venta_vendedor
        FOREIGN KEY (id_vendedor) REFERENCES vendedor(id_vendedor),

    CONSTRAINT fk_venta_cliente
        FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),

    CONSTRAINT fk_venta_pedido
        FOREIGN KEY (id_pedido) REFERENCES pedido(id)

);

CREATE TABLE detalle_venta (
id_detalle SERIAL PRIMARY KEY,
id_venta INTEGER NOT NULL,
id_producto_variante INTEGER NOT NULL,

    cantidad INTEGER NOT NULL,

    precio_unitario NUMERIC(12,2),
    subtotal NUMERIC(12,2),
    descuento NUMERIC(12,2),

    costo_unitario NUMERIC(12,2),
    costo_total NUMERIC(12,2),
    utilidad_bruta NUMERIC(12,2),

    direccion TEXT,

    CONSTRAINT fk_detalle_venta_venta
        FOREIGN KEY (id_venta) REFERENCES venta(id),

    CONSTRAINT fk_detalle_venta_producto_variante
        FOREIGN KEY (id_producto_variante) REFERENCES producto_variante(id)

);

-- ============================================================
-- SALIDA DE LOTES
-- Registra de qué lote salió cada venta.
-- Necesario para FIFO.
-- ============================================================

CREATE TABLE salida_lote (
id SERIAL PRIMARY KEY,
id_detalle_venta INTEGER NOT NULL,
id_lote INTEGER NOT NULL,

    cantidad INTEGER NOT NULL,
    costo_unitario_aplicado NUMERIC(12,2) NOT NULL,
    costo_total NUMERIC(12,2) NOT NULL,

    CONSTRAINT fk_salida_lote_detalle_venta
        FOREIGN KEY (id_detalle_venta) REFERENCES detalle_venta(id_detalle),

    CONSTRAINT fk_salida_lote_lote
        FOREIGN KEY (id_lote) REFERENCES lote_inventario(id)

);

-- ============================================================
-- MOVIMIENTOS DE INVENTARIO
-- Todo movimiento afecta a una variante exacta.
-- ============================================================

CREATE TABLE movimiento_inventario (
id SERIAL PRIMARY KEY,

    id_producto_variante INTEGER NOT NULL,
    id_tecnica_inventario INTEGER NOT NULL,
    id_tecnica_costo INTEGER NOT NULL,

    tipo_movimiento VARCHAR(50),
    motivo TEXT,

    cantidad INTEGER,
    costo_unitario NUMERIC(12,2),
    costo_total NUMERIC(12,2),

    stock_anterior INTEGER,
    stock_resultante INTEGER,

    fecha DATE,
    observacion TEXT,

    CONSTRAINT fk_movimiento_producto_variante
        FOREIGN KEY (id_producto_variante) REFERENCES producto_variante(id),

    CONSTRAINT fk_movimiento_tecnica_inventario
        FOREIGN KEY (id_tecnica_inventario) REFERENCES tecnica_inventario(id),

    CONSTRAINT fk_movimiento_tecnica_costo
        FOREIGN KEY (id_tecnica_costo) REFERENCES tecnica_costo(id)

);

-- ============================================================
-- METODOS DE PAGO Y PAGO CONTADO
-- ============================================================

CREATE TABLE metodo_pago (
id SERIAL PRIMARY KEY,
nombre VARCHAR(100) NOT NULL,
descripcion TEXT
);

CREATE TABLE pago_contado (
id SERIAL PRIMARY KEY,
id_venta INTEGER NOT NULL,
id_metodo_pago INTEGER NOT NULL,
fecha DATE,
monto_pago NUMERIC(12,2),
interes_mora_cobrado NUMERIC(12,2),
recargo_extra NUMERIC(12,2),
observaciones TEXT,

    CONSTRAINT fk_pago_contado_venta
        FOREIGN KEY (id_venta) REFERENCES venta(id),

    CONSTRAINT fk_pago_contado_metodo_pago
        FOREIGN KEY (id_metodo_pago) REFERENCES metodo_pago(id)

);

-- ============================================================
-- CREDITO Y CUOTAS
-- ============================================================

CREATE TABLE credito (
id SERIAL PRIMARY KEY,
id_venta INTEGER NOT NULL,
monto_credito NUMERIC(12,2),
monto_pendiente NUMERIC(12,2),
monto_pagado NUMERIC(12,2),
fecha_otorgamiento DATE,
fecha_vencimiento DATE,
interes NUMERIC(12,2),
estado VARCHAR(30),
cuotas_total INTEGER,
dias_mora INTEGER,
fecha_creacion DATE,
fecha_actualizacion DATE,

    CONSTRAINT fk_credito_venta
        FOREIGN KEY (id_venta) REFERENCES venta(id)

);

CREATE TABLE pago_cuota (
id SERIAL PRIMARY KEY,
id_credito INTEGER NOT NULL,
id_metodo_pago INTEGER NOT NULL,
numero_cuota INTEGER,
interes_cuota NUMERIC(12,2),
fecha_vencimiento DATE,
fecha_pago DATE,
monto_pagado NUMERIC(12,2),
dias_mora INTEGER,
monto_pendiente NUMERIC(12,2),
estado VARCHAR(30),
fecha_creacion DATE,
fecha_actualizacion DATE,

    CONSTRAINT fk_pago_cuota_credito
        FOREIGN KEY (id_credito) REFERENCES credito(id),

    CONSTRAINT fk_pago_cuota_metodo_pago
        FOREIGN KEY (id_metodo_pago) REFERENCES metodo_pago(id)

);

-- ============================================================
-- REPORTES Y NOTIFICACIONES
-- ============================================================

CREATE TABLE reporte (
id SERIAL PRIMARY KEY,
id_usuario INTEGER NOT NULL,
tipo_reporte VARCHAR(100),
fecha_generacion DATE,
parametros TEXT,
fecha_creacion DATE,
fecha_actualizacion DATE,

    CONSTRAINT fk_reporte_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)

);

CREATE TABLE notificacion (
id SERIAL PRIMARY KEY,
id_usuario INTEGER NOT NULL,
tipo VARCHAR(100),
titulo VARCHAR(150),
mensaje TEXT,
fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
estado VARCHAR(30) DEFAULT 'NO_LEIDA',

    CONSTRAINT fk_notificacion_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)

);
