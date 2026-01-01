<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Pedido - El Rey del Crujiente</title>
  <link rel="stylesheet" href="css/estilos.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <nav>
    <a href="index.php">🧾 Nuevo Pedido</a> |
    <a href="productos.html">🍽️ Agregar Productos</a> |
    <a href="tasa.html">💱 Tasa BCV</a> |
    <a href="ventas.html">📊 Ventas</a> |
    <a href="deudores.html">⚠️ Deudores</a> |
    <a href="resumen_caja.html">💰 Resumen de Caja</a> |
    <a href="productos_vendidos.html">📦 Productos Vendidos</a> |
    <a href="combos.html">🧃 Combos</a> |
    <a href="perfil.html">🔒 Mi Perfil</a>
  </nav>

  <div class="panel" id="carrito">
    <h3>🛒 Pedido</h3>
    <ul id="listaCarrito"></ul>
    <div id="totalCarrito">
  <p><strong>Total:</strong></p>
  <p>💵 <span id="totalUSD">0.00</span> USD</p>
  <p>🇻🇪 <span id="totalBs">0.00</span> Bs</p>
</div>
    <label>Nombre del pedido:
      <input type="text" id="nombrePedido" placeholder="Ej: Juan Pérez, Evento, etc.">
    </label>
    <label>Tipo de pedido:
      <select id="tipoPedido">
        <option value="en_sitio">Comer en el sitio</option>
        <option value="para_llevar">Para llevar</option>
      </select>
    </label>
    <div id="grupoMesas">
      <div id="mesas"></div>
    </div>
    <button onclick="enviarPedido()">Enviar pedido</button>
  </div>

  <div class="panel" id="buscador">
    <h3>🔍 Buscar producto</h3>
    <input type="text" id="inputBusqueda" placeholder="Ej: lomo de cerdo" oninput="buscarProducto()">
    <div id="resultadosBusqueda"></div>
  </div>

  <div class="panel" id="combos">
    <h3>⚡ Menú de combos</h3>
    <div id="listaCombos"></div>
  </div>
<div id="editorComboModal" style="display:none;" class="panel">
  <h3>✏️ Editar Combo</h3>
  <input type="hidden" id="editorComboId">
  <label>Nombre: <input type="text" id="editorComboNombre"></label>
  <div id="editorComboProductos"></div>
  <p><strong>Precio estimado:</strong> $<span id="editorComboPrecioCalculado">0.00</span></p>
  <button onclick="guardarEdicionCombo()">Guardar cambios</button>
  <button onclick="document.getElementById('editorComboModal').style.display='none'">Cancelar</button>
</div>

  <script src="js/app.js"></script>
  <?php
$ip = getHostByName(getHostName());
echo "Accede a este sistema desde otros dispositivos usando: http://$ip/elreydelcrujiente/index.php"
?>

</body>
</html>
