# 🍗 El Rey del Crujiente - Sistema de Punto de Venta

Sistema de punto de venta (POS) diseñado específicamente para restaurantes, con soporte para gestión de pedidos, productos, combos, y control de caja en múltiples monedas (USD y Bolívares).

## 📋 Características

### 🛒 Gestión de Pedidos
- Crear pedidos para **comer en el sitio** o **para llevar**
- Asignación de pedidos a mesas específicas
- Búsqueda rápida de productos
- Agregar productos individuales o combos
- Visualización en tiempo real del total en USD y Bs

### 🍽️ Gestión de Productos
- Agregar, editar y eliminar productos
- Categorización de productos
- Control de precios en USD
- Gestión de disponibilidad

### 🧃 Sistema de Combos
- Crear combos personalizados con múltiples productos
- Cálculo automático de precios
- Edición y eliminación de combos
- Visualización de productos incluidos en cada combo

### 💱 Gestión de Tasa de Cambio
- Actualización de tasa BCV (Banco Central de Venezuela)
- Conversión automática USD ↔ Bs
- Histórico de tasas

### 📊 Reportes y Análisis
- **Ventas**: Listado completo de pedidos con filtros
- **Resumen de Caja**: Control de ingresos por método de pago
- **Productos Vendidos**: Análisis de productos más vendidos
- Visualización de totales en ambas monedas

### 👨‍🍳 Vista de Cocina
- Panel dedicado para visualizar pedidos pendientes
- Marcar productos como listos
- Actualización en tiempo real

## 🛠️ Tecnologías Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL / MariaDB
- **Servidor Local**: XAMPP

## 📦 Requisitos Previos

- [XAMPP](https://www.apachefriends.org/) (o cualquier stack LAMP/WAMP)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Navegador web moderno (Chrome, Firefox, Edge)

## 🚀 Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/jessmarin8813/ElReydelCrujiente.git
```

### 2. Mover a la Carpeta de XAMPP

Copia la carpeta del proyecto a tu directorio de XAMPP:

```bash
# Windows
C:\xampp\htdocs\ElReydelCrujiente

# Linux/Mac
/opt/lampp/htdocs/ElReydelCrujiente
```

### 3. Crear la Base de Datos

1. Abre **phpMyAdmin** en tu navegador: `http://localhost/phpmyadmin`
2. Crea una nueva base de datos llamada `elreydelcrujiente`
3. Importa el archivo SQL de esquema (si existe en la carpeta `sql/` o `database/`)

**Nota**: Si no tienes un archivo SQL, puedes usar el esquema de la versión 2 ubicado en:
```
ElReydelCrujiente_v2/database/schema.sql
```

### 4. Configurar la Conexión a la Base de Datos

Edita el archivo `api/conexion.php` con tus credenciales:

```php
<?php
$host = "localhost";
$usuario = "root";
$clave = "";  // Tu contraseña de MySQL
$bd = "elreydelcrujiente";
?>
```

### 5. Iniciar el Servidor

1. Abre el **Panel de Control de XAMPP**
2. Inicia los módulos **Apache** y **MySQL**
3. Accede al sistema en tu navegador:

```
http://localhost/ElReydelCrujiente/index.php
```

## 📱 Acceso desde Otros Dispositivos

El sistema muestra automáticamente la IP local para acceder desde otros dispositivos en la misma red:

```
http://[TU_IP_LOCAL]/ElReydelCrujiente/index.php
```

Ejemplo: `http://192.168.1.100/ElReydelCrujiente/index.php`

## 📖 Uso del Sistema

### Crear un Nuevo Pedido

1. Accede a **🧾 Nuevo Pedido**
2. Busca productos usando el buscador o selecciona combos
3. Agrega productos al carrito
4. Ingresa el nombre del pedido
5. Selecciona el tipo: "Comer en el sitio" o "Para llevar"
6. Si es para comer en el sitio, selecciona una mesa
7. Haz clic en **Enviar pedido**

### Gestionar Productos

1. Ve a **🍽️ Agregar Productos**
2. Completa el formulario con:
   - Nombre del producto
   - Precio en USD
   - Categoría
3. Haz clic en **Agregar Producto**

### Crear Combos

1. Accede a **🧃 Combos**
2. Haz clic en **Crear Nuevo Combo**
3. Asigna un nombre al combo
4. Selecciona los productos que incluirá
5. El precio se calcula automáticamente
6. Guarda el combo

### Actualizar Tasa de Cambio

1. Ve a **💱 Tasa BCV**
2. Ingresa la nueva tasa en Bs
3. Haz clic en **Actualizar Tasa**

### Ver Reportes

- **📊 Ventas**: Visualiza todos los pedidos realizados
- **💰 Resumen de Caja**: Consulta ingresos por método de pago
- **📦 Productos Vendidos**: Analiza qué productos se venden más

## 🗂️ Estructura del Proyecto

```
ElReydelCrujiente/
├── api/                    # Scripts PHP del backend
│   ├── conexion.php       # Configuración de base de datos
│   ├── pedido.php         # Gestión de pedidos
│   ├── productos.php      # Gestión de productos
│   ├── combos.php         # Gestión de combos
│   ├── tasa.php           # Gestión de tasa de cambio
│   └── ...
├── css/                   # Estilos
│   └── estilos.css
├── js/                    # JavaScript
│   └── app.js
├── ElReydelCrujiente_v2/  # Versión 2 (en desarrollo)
├── index.php              # Página principal
├── productos.html         # Gestión de productos
├── combos.html            # Gestión de combos
├── tasa.html              # Actualización de tasa
├── ventas.html            # Reporte de ventas
├── resumen_caja.html      # Resumen de caja
├── productos_vendidos.html # Productos vendidos
├── cocina.html            # Vista de cocina
└── README.md              # Este archivo
```

## 🔧 API Endpoints

El sistema utiliza los siguientes endpoints PHP:

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/pedido.php` | POST | Crear nuevo pedido |
| `/api/productos.php` | GET/POST | Listar/Agregar productos |
| `/api/combos.php` | GET/POST | Listar/Crear combos |
| `/api/tasa.php` | GET/POST | Obtener/Actualizar tasa BCV |
| `/api/ventas.php` | GET | Obtener reporte de ventas |
| `/api/resumen_caja.php` | GET | Obtener resumen de caja |

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Si deseas mejorar el proyecto:

1. Haz un Fork del repositorio
2. Crea una rama para tu feature (`git checkout -b feature/NuevaCaracteristica`)
3. Commit tus cambios (`git commit -m 'Agregar nueva característica'`)
4. Push a la rama (`git push origin feature/NuevaCaracteristica`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 👤 Autor

**Jesús Marín** - [@jessmarin8813](https://github.com/jessmarin8813)

## 📞 Soporte

Si tienes alguna pregunta o problema, por favor abre un [Issue](https://github.com/jessmarin8813/ElReydelCrujiente/issues) en GitHub.

---

⭐ Si este proyecto te fue útil, considera darle una estrella en GitHub!
