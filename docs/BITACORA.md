# 📋 Bitácora de Auditoría - Guía de Uso

## Descripción

La bitácora de auditoría es un sistema completo de registro de eventos y acciones dentro del sistema Tienda Elena. Permite al propietario:

- 🔍 **Rastrear todas las acciones** (login, logout, creación, edición, eliminación)
- 📊 **Filtrar eventos** por fecha, tipo de acción, entidad, usuario
- 📥 **Exportar registros** a CSV para análisis
- 👁️ **Ver detalles** de cambios realizados
- 🔐 **Mantener seguridad** con registro de IP y User Agent

---

## Acceso

1. **URL**: `http://localhost/bitacora`
2. **Disponible para**: Propietario (role_id: 1)
3. **Ubicación en menú**: Sidebar izquierdo → Bitácora (icon: 📚)

---

## Funcionalidades Principales

### 📋 Listado de Eventos

**Tabla con columnas:**

- ⏰ Fecha y hora exacta
- 👤 Usuario que realizó la acción (o "Sistema")
- 🎯 Tipo de acción (login, create, update, delete, logout, etc.)
- 📦 Tipo de entidad (Usuario, Producto, Venta, etc.)
- 📝 Descripción del evento
- 🌐 Dirección IP
- 👁️ Botón para ver detalles

**Paginación:** 50 registros por página

### 🔎 Filtros Avanzados

**Disponibles:**

- **Desde/Hasta**: Rango de fechas
- **Acción**: Lista de acciones registradas (login, create, update, delete, etc.)
- **Entidad**: Tipo de registro afectado (Usuario, Producto, Venta, etc.)
- **Búsqueda**: Texto libre en usuario, descripción o nombre de entidad

**Botones:**

- ✅ **Filtrar**: Aplica los filtros seleccionados
- 🗑️ **Limpiar**: Reinicia los filtros (visible si hay filtros activos)

### 📥 Exportar a CSV

- **Botón**: "Exportar CSV" (arriba a la derecha)
- **Archivo**: `bitacora_YYYY-MM-DD_HH-mm-ss.csv`
- **Contenido**: Los mismos registros mostrados con filtros aplicados
- **Columnas**: ID, Usuario, Acción, Entidad, Entidad ID, Descripción, IP, Fecha

### 👁️ Ver Detalles de un Evento

1. Haz clic en el botón **"Ver"** en la fila del evento
2. Se abrirá una página con:
    - **ID del evento**
    - **Acción** (con badge coloreado)
    - **Tipo de entidad**
    - **ID de entidad**
    - **Nombre de entidad**
    - **Descripción completa**
    - **Cambios realizados** (JSON formateado - solo para updates)
    - **Información del usuario** (nombre, email, rol)
    - **Datos técnicos** (IP, User Agent, fecha/hora completa)

---

## Eventos Registrados Automáticamente

### 🔐 Eventos de Autenticación

**Login:**

- ✅ Se registra automáticamente al iniciar sesión
- 📌 Captura: Usuario, IP, User Agent, fecha/hora
- 🏷️ Acción: `login`

**Logout:**

- ✅ Se registra automáticamente al cerrar sesión
- 📌 Captura: Usuario, IP, User Agent, fecha/hora
- 🏷️ Acción: `logout`

### 📦 Eventos de Datos (Usando el Trait)

Para registrar automáticamente CRUD en un modelo, agregar el trait:

```php
use App\Traits\RegistraBitacora;

class Producto extends Model
{
    use RegistraBitacora;
    // ...
}
```

**Eventos automáticos:**

- 🆕 **Create**: Cuando se crea un nuevo registro
    - 🏷️ Acción: `create`
    - 📝 Información: Nombre/descripción de lo creado

- ✏️ **Update**: Cuando se actualiza un registro
    - 🏷️ Acción: `update`
    - 📝 Captura: Cambios (valor anterior → nuevo)

- 🗑️ **Delete**: Cuando se elimina un registro
    - 🏷️ Acción: `delete`
    - 📝 Información: Datos completos del registro eliminado

### 📝 Registrar Eventos Manualmente

En controladores:

```php
use App\Models\Bitacora;

// Registro simple
Bitacora::registrar(
    'accion',           // 'crear', 'editar', 'eliminar', etc.
    'Entidad',          // 'Producto', 'Usuario', 'Venta', etc.
    $id,                // ID del registro afectado
    'Nombre Entidad',   // Nombre/descripción de lo afectado
    $cambios,           // Array con cambios (para updates)
    'Descripción adicional'
);

// Ejemplo práctico
Bitacora::registrar(
    'generar_reporte',
    'Reporte',
    null,
    'Reporte de Ventas',
    null,
    'Propietario generó reporte de ventas de octubre 2026'
);
```

---

## Colores en la Interfaz

### Badges de Acción

- 🟢 **Green**: `login`
- 🟡 **Yellow**: `logout`
- 🔵 **Blue**: `create`, `download`
- 🟣 **Purple**: `update`
- 🔴 **Red**: `delete`
- ⚪ **Gray**: Otros

### Badges de Entidad

- 🔵 **Indigo**: `Usuario`
- 🔷 **Light Blue**: `Producto`, `Promocion`
- 🟢 **Green**: `Venta`, `Pago`
- 🟡 **Yellow**: `Credito`
- ⚪ **Gray**: Otros

---

## 📊 Estadísticas API

**Endpoint**: `GET /api/bitacora/estadisticas`

**Respuesta JSON:**

```json
{
    "total_eventos": 1250,
    "eventos_hoy": 45,
    "eventos_mes": 450,
    "acciones_frecuentes": [
        { "accion": "login", "total": 180 },
        { "accion": "create", "total": 120 }
    ],
    "entidades_frecuentes": [
        { "entidad": "Usuario", "total": 95 },
        { "entidad": "Producto", "total": 87 }
    ],
    "usuarios_activos": 12
}
```

---

## 🔒 Consideraciones de Seguridad

1. **Acceso**: Solo Propietario puede acceder a la bitácora
2. **Datos capturados**: IP, User Agent (sin almacenar contraseñas)
3. **Cambios**: Solo valores anteriores y nuevos (no información sensible)
4. **Auditoría completa**: Todo evento queda registrado con timestamp exacto

---

## 📋 Ejemplos de Uso

### Caso 1: Investigar cambios en un producto

1. Ir a Bitácora
2. Filtrar: **Entidad** = "Producto"
3. Búsqueda: Nombre del producto
4. Haz clic en "Ver" en un evento de `update`
5. Mira los **Cambios Realizados** para ver qué se modificó

### Caso 2: Rastrear actividad de un usuario

1. Ir a Bitácora
2. Filtrar: **Acción** = "todas"
3. Búsqueda: Nombre del usuario
4. Visualiza todas sus acciones ordenadas por fecha

### Caso 3: Auditoría de borrados

1. Ir a Bitácora
2. Filtrar: **Acción** = "delete"
3. Rango de fechas: Período de interés
4. Exportar CSV para documentación

### Caso 4: Análisis de seguridad

1. Ir a Bitácora
2. Filtrar: **Acción** = "login"
3. Rango: Últimas 24 horas
4. Verifica IPs sospechosas (datos técnicos en "Ver")

---

## ⚙️ Configuración Técnica

### Modelos que registran automáticamente

_(Agregar trait `RegistraBitacora` según necesidad)_

**Recomendados:**

- User (cambios en usuarios)
- Producto (cambios en catálogo)
- Venta (registros de ventas)
- Credito (cambios en créditos)
- Pago (registros de pago)

### Listeners configurados

- `App\Listeners\RegistrarLoginBitacora` → `Login` event
- `App\Listeners\RegistrarLogoutBitacora` → `Logout` event

### Tabla en BD

- **Tabla**: `bitacora`
- **Registros**: Sin límite (considerar limpieza periódica)
- **Índices**: user_id, accion, entidad, created_at

---

## 📞 Soporte

Para registrar eventos adicionales o personalizar la bitácora:

1. **Registrar en controlador**: Usar `Bitacora::registrar()`
2. **Registrar automáticamente**: Agregar trait a modelo
3. **Registrar evento personalizado**: Crear listener

---

**Última actualización**: Junio 28, 2026
**Versión**: 1.0.0
