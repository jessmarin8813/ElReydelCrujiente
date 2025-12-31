/**
 * app.js - Versión integrada y corregida
 * - Unifica nombres de campo (prefiere *_usd) con fallback legacy.
 * - Normaliza respuestas de verificar_pago.
 * - Verificar_pago llama con id y pedido_id para compatibilidad.
 * - Registrar pago actualiza fila usando campos preferentes.
 * - Soft-reset tras crear pedido y lock anti-dobles en envío.
 */

/* =========================
   I. Utilidades globales
   ========================= */
   async function crearCombo() {
  const nombre = document.getElementById('nombreCombo')?.value?.trim();
  if (!nombre) {
    alert('Debes ingresar un nombre para el combo.');
    return;
  }

  const productos = [];
  document.querySelectorAll('#productosDisponibles input[type=number]').forEach(input => {
    const cantidad = parseFloat(input.value) || 0;
    const precioUnit = parseFloat(input.dataset.precio) || 0;
    if (cantidad > 0) {
      productos.push({
        producto_id: input.id.replace('combo-cantidad-', ''), // 👈 clave correcta
        cantidad: cantidad,
        precio: precioUnit
      });
    }
  });

  if (productos.length === 0) {
    alert('Debes seleccionar al menos un producto con cantidad mayor a 0.');
    return;
  }

  try {
    const res = await fetch('api/crear_combo.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nombre, productos })
    });

    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch(e) { data = { _raw: text }; }

    if (!res.ok) {
      const msg = data?.error || data?._raw || `HTTP ${res.status}`;
      alert('❌ ' + msg);
      throw new Error(msg);
    }

    alert(data.mensaje || 'Combo creado con éxito');
    if (typeof cargarCombos === 'function') cargarCombos();
  } catch (err) {
    console.error('crearCombo error:', err);
    alert('Error al crear combo: ' + err.message);
  }
}
window.crearCombo = crearCombo;

//fin crear combo

//Inicio recalcular precio combo
function recalcularPrecioCombo() {
  let total = 0;
  document.querySelectorAll('#productosDisponibles input[type=number]').forEach(input => {
    const cantidad = parseFloat(input.value) || 0;
    const precioUnit = parseFloat(input.dataset.precio) || 0;
    total += cantidad * precioUnit;
  });

  const precioEl = document.getElementById("precioCombo");
  if (precioEl) precioEl.textContent = total.toFixed(2);
}


async function cargarProductosParaCombo() {
  const cont = document.getElementById('productosDisponibles');
  if (!cont) {
    console.warn('[cargarProductosParaCombo] no existe #productosDisponibles');
    return;
  }
  cont.innerHTML = '<p>Cargando productos...</p>';
  try {
    const res = await fetch('api/productos.php', { cache: 'no-store' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const productos = await res.json();
    if (!Array.isArray(productos) || productos.length === 0) {
      cont.innerHTML = '<p>No hay productos disponibles.</p>';
      return;
    }
    let html = '<table><tr><th>Producto</th><th>Cantidad</th></tr>';
    productos.forEach(p => {
      const precio = Number(p.precio || 0).toFixed(2);
      html += `<tr>
  <td>${escapeHtml(p.nombre || 'Sin nombre')} ($${precio})</td>
  <td><input type="number" id="combo-cantidad-${p.id}" step="0.01" min="0" placeholder="Cantidad" data-precio="${precio}" oninput="recalcularPrecioCombo()"></td>
</tr>`;
    });
    html += '</table>';
    cont.innerHTML = html;
  } catch (err) {
    cont.innerHTML = `<p>Error al cargar productos: ${escapeHtml(err.message || String(err))}</p>`;
    console.error('cargarProductosParaCombo error:', err);
  }
}
window.cargarProductosParaCombo = cargarProductosParaCombo;

function escapeHtml(str) {
  if (str === null || typeof str === 'undefined') return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
window.escapeHtml = escapeHtml;

async function cargarProductos() {
  const tbody = document.getElementById('tablaProductos');
  if (!tbody) {
    console.warn('[productos] No existe elemento #tablaProductos en este HTML');
    return;
  }

  tbody.innerHTML = '<tr><td colspan="4">Cargando productos...</td></tr>';

  try {
    const res = await fetch('api/productos.php', { cache: 'no-store' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    if (!Array.isArray(data) || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4">No hay productos registrados.</td></tr>';
      return;
    }

    tbody.innerHTML = '';
    data.forEach(prod => {
      const tr = document.createElement('tr');

      const tdNombre = document.createElement('td');
      const inputNombre = document.createElement('input');
      inputNombre.type = 'text';
      inputNombre.value = prod.nombre ?? '';
      inputNombre.dataset.id = prod.id;
      inputNombre.dataset.campo = 'nombre';
      tdNombre.appendChild(inputNombre);

      const tdPrecio = document.createElement('td');
      const inputPrecio = document.createElement('input');
      inputPrecio.type = 'number';
      inputPrecio.step = '0.01';
      inputPrecio.value = parseFloat(prod.precio || 0).toFixed(2);
      inputPrecio.dataset.id = prod.id;
      inputPrecio.dataset.campo = 'precio';
      tdPrecio.appendChild(inputPrecio);

      const tdEstado = document.createElement('td');
      const selectEstado = document.createElement('select');
      selectEstado.dataset.id = prod.id;
      selectEstado.dataset.campo = 'estado';
      const optAct = document.createElement('option'); optAct.value = 'activo'; optAct.text = 'Activo';
      const optIna = document.createElement('option'); optIna.value = 'inactivo'; optIna.text = 'Inactivo';
      if ((prod.estado ?? '') === 'activo') optAct.selected = true; else if ((prod.estado ?? '') === 'inactivo') optIna.selected = true;
      selectEstado.appendChild(optAct); selectEstado.appendChild(optIna);
      tdEstado.appendChild(selectEstado);

      const tdAccion = document.createElement('td');
      const btnGuardar = document.createElement('button');
      btnGuardar.type = 'button';
      btnGuardar.textContent = 'Guardar';
      btnGuardar.addEventListener('click', () => guardarProducto(prod.id));
      tdAccion.appendChild(btnGuardar);

      tr.appendChild(tdNombre);
      tr.appendChild(tdPrecio);
      tr.appendChild(tdEstado);
      tr.appendChild(tdAccion);

      tbody.appendChild(tr);
    });
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="4">Error al cargar productos: ${escapeHtml(err.message || String(err))}</td></tr>`;
    console.error('cargarProductos error:', err);
  }
}
window.cargarProductos = cargarProductos;

async function guardarProducto(id) {
  const nombreEl = document.querySelector(`input[data-id="${id}"][data-campo="nombre"]`);
  const precioEl = document.querySelector(`input[data-id="${id}"][data-campo="precio"]`);
  const estadoEl = document.querySelector(`select[data-id="${id}"][data-campo="estado"]`);
  const nombre = nombreEl?.value?.trim() || '';
  const precio = parseFloat(precioEl?.value) || 0;
  const estado = estadoEl?.value || 'activo';

  if (!nombre || precio <= 0) {
    alert('Nombre y precio válidos son obligatorios.');
    return;
  }

  try {
    const res = await fetch('api/editar_producto.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, nombre, precio, estado })
    });

    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch(e) { data = { _raw: text }; }

    if (!res.ok) {
      const msg = data?.error || data?._raw || `HTTP ${res.status}`;
      alert('❌ ' + msg);
      throw new Error(msg);
    }

    alert(data.mensaje || data.error || 'Producto guardado');
    if (typeof cargarProductos === 'function') cargarProductos();
  } catch (err) {
    console.error('guardarProducto error:', err);
  }
}
window.guardarProducto = guardarProducto;

function normalizeButtonText(rawText, isPagado) {
  if (isPagado) return 'Abrir (Pagado)';
  if (!rawText) return 'Abrir';
  const txt = String(rawText).trim().toLowerCase();
  if (txt === 'pagado' || txt === 'paid' || txt === 'pagada') return 'Abrir (Pagado)';
  if (txt === 'cerrar' || txt === 'close') return 'Cerrar';
  return 'Abrir';
}
window.normalizeButtonText = normalizeButtonText;

function actualizarBoton(id, texto, isPagado = false) {
  const fila = document.querySelector(`#tablaVentas tr[data-id="${id}"]`);
  if (!fila) return;
  const boton = fila.querySelector('button');
  if (!boton) return;
  const nuevo = normalizeButtonText(texto, isPagado);
  boton.textContent = nuevo;
  boton.disabled = false;
  boton.removeAttribute('aria-disabled');
}
window.actualizarBoton = actualizarBoton;

function formatearCantidad(num) {
  const n = Number(num);
  return Number.isInteger(n) ? n.toString() : n.toFixed(3).replace(/\.?0+$/, '');
}

/* =========================
   II. Estado global y flags
   ========================= */
let mesaSeleccionada = null;
let carrito = [];
let tasaBs = 0;
let pedidoAbierto = null;
window.pedidoAbierto = window.pedidoAbierto || null;

window.__initMainOnce = false;
window.__initNewOnce = false;
let __cargarCombosRunning = false;

/* =========================
   III. Cargas y utilidades UI
   ========================= */
function cargarTasa() {
  fetch('api/tasa.php')
    .then(res => res.json())
    .then(data => {
      tasaBs = parseFloat(data.valor) || 0;
      actualizarTotal();
    })
    .catch(() => { tasaBs = tasaBs || 0; });
}

function actualizarTotal() {
  let totalUSD = 0;
  carrito.forEach(p => { totalUSD += (p.precio || 0) * (p.cantidad || 0); });
  const totalBs = totalUSD * tasaBs;
  const usdSpan = document.getElementById('totalUSD');
  const bsSpan = document.getElementById('totalBs');
  if (usdSpan) usdSpan.textContent = totalUSD.toFixed(2);
  if (bsSpan) bsSpan.textContent = totalBs.toFixed(2);
}

function renderCarrito() {
  const ul = document.getElementById('listaCarrito');
  if (!ul) return;
  ul.innerHTML = '';
  carrito.forEach((p, index) => {
    const precioUSD = parseFloat(p.precio) || 0;
    const precioBs = precioUSD * tasaBs;
    const subtotalUSD = precioUSD * (p.cantidad || 0);
    const subtotalBs = subtotalUSD * tasaBs;
    const li = document.createElement('li');
    li.innerHTML = `
      <div class="item-carrito">
        <strong>${escapeHtml(p.nombre)}</strong><br>
        $${precioUSD.toFixed(2)} | Bs ${precioBs.toFixed(2)} x
        <input type="number" value="${p.cantidad}" min="0.01" step="0.01" onchange="actualizarCantidad(${index}, this.value)">
        = $${subtotalUSD.toFixed(2)} | Bs ${subtotalBs.toFixed(2)}
        <button onclick="eliminarDelCarrito(${index})">❌</button>
      </div>
    `;
    ul.appendChild(li);
  });
  actualizarTotal();
}

/* =========================
   IV. Mesas y pedidos UI
   ========================= */
function cargarMesas() {
  const div = document.getElementById('mesas');
  if (!div) return;
  fetch('api/mesas.php')
    .then(res => res.json())
    .then(data => {
      div.innerHTML = '<h3>Mesas</h3>';
      data.forEach(mesa => {
        const btn = document.createElement('button');
        btn.innerText = `${mesa.nombre} (${mesa.estado})`;
        btn.className = mesa.estado === 'libre' ? 'green' : 'red';
        btn.onclick = () => { mesaSeleccionada = mesa.id; alert("Mesa seleccionada: " + mesa.nombre); };
        div.appendChild(btn);
      });
    })
    .catch(err => console.error('Error cargarMesas:', err));
}

/* =========================
   V. Ventas / listar / detalle
   ========================= */
async function cargarVentas() {
  const estado = document.getElementById('filtroEstado')?.value || '';
  const desde = document.getElementById('filtroDesde')?.value || '';
  const hasta = document.getElementById('filtroHasta')?.value || '';
  const params = new URLSearchParams();
  if (estado) params.append('estado', estado);
  if (desde && hasta) { params.append('desde', desde); params.append('hasta', hasta); }

  try {
    const res = await fetch('api/listar_pedidos.php?' + params.toString());
    if (!res.ok) throw new Error('Error al cargar ventas');
    const data = await res.json();
    const ventas = Array.isArray(data) ? data : (data.ventas || []);
    const tbody = document.getElementById('tablaVentas');
    if (!tbody) return;
    tbody.innerHTML = '';

    const EPS = 0.01;
    ventas.forEach(p => {
      const fila = document.createElement('tr');
      fila.dataset.id = p.id;

      // compat: preferimos total_pedido_usd y total_pagado_usd, si no están caer a legacy
      const totalPedido = (typeof p.total_pedido_usd !== 'undefined') ? parseFloat(p.total_pedido_usd) :
                          (typeof p.total_pedido !== 'undefined' ? parseFloat(p.total_pedido) :
                           (typeof p.total !== 'undefined' ? parseFloat(p.total) : 0));
      const totalPagado = (typeof p.total_pagado_usd !== 'undefined') ? parseFloat(p.total_pagado_usd) :
                          (typeof p.total_pagado !== 'undefined' ? parseFloat(p.total_pagado) : 0);

      const isPagadoCalcBackend = (typeof p.is_pagado_calc !== 'undefined') ? Boolean(p.is_pagado_calc) : null;
      const isPagadoCalc = (isPagadoCalcBackend === null) ? ((totalPagado + EPS) >= totalPedido) : isPagadoCalcBackend;

      fila.classList.toggle('resaltado-pagado', isPagadoCalc);
      fila.classList.toggle('resaltado-deudor', !isPagadoCalc);

      const tdId = document.createElement('td'); tdId.textContent = String(p.id || ''); fila.appendChild(tdId);
      const tdNombre = document.createElement('td'); tdNombre.innerHTML = escapeHtml(p.nombre || '—'); fila.appendChild(tdNombre);
      const tdMesa = document.createElement('td'); tdMesa.innerHTML = escapeHtml(p.mesa ?? '—'); fila.appendChild(tdMesa);

      const tdEstado = document.createElement('td'); tdEstado.className = 'estado';
      tdEstado.textContent = isPagadoCalc ? 'pagado' : (p.estado || 'deudor');
      fila.appendChild(tdEstado);

      const tdTotal = document.createElement('td'); tdTotal.className = 'total';
      tdTotal.appendChild(document.createTextNode(`$${totalPedido.toFixed(2)} `));
      const divPagado = document.createElement('div');
      divPagado.className = 'total-pagado';
      divPagado.style.fontSize = '0.85em';
      divPagado.style.opacity = '0.9';
      divPagado.textContent = `Pagado: $${totalPagado.toFixed(2)}`;
      divPagado.style.color = isPagadoCalc ? 'green' : '';
      tdTotal.appendChild(divPagado);
      fila.appendChild(tdTotal);

      const tdAccion = document.createElement('td');
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = isPagadoCalc ? 'Abrir (Pagado)' : 'Abrir';
      btn.addEventListener('click', () => {
        try { if (typeof verDetalle === 'function') verDetalle(p.id); else console.warn('verDetalle no está definido'); }
        catch (e) { console.error('Error al ejecutar verDetalle:', e); }
      });
      tdAccion.appendChild(btn);
      fila.appendChild(tdAccion);

      fila.dataset.totalPedido = totalPedido.toFixed(2);
      fila.dataset.totalPagado = totalPagado.toFixed(2);
      fila.dataset.estadoBackend = p.estado || '';
      if (typeof p.is_pagado_calc !== 'undefined') fila.dataset.isPagadoCalc = p.is_pagado_calc ? '1' : '0';

      tbody.appendChild(fila);
    });

    if (desde && hasta && new Date(desde) <= new Date(hasta)) {
      try {
        const res2 = await fetch(`api/resumen_caja.php?desde=${encodeURIComponent(desde)}&hasta=${encodeURIComponent(hasta)}`);
        if (res2.ok) {
          const resumenData = await res2.json();
          const resumen = document.getElementById('resumenCaja');
          if (resumen) {
            let html = '<h4>Resumen de caja</h4><ul>';
            resumenData.forEach(r => { html += `<li>${escapeHtml(r.metodo)}: $${parseFloat(r.total).toFixed(2)}</li>`; });
            html += '</ul>';
            resumen.innerHTML = html;
          }
        } else console.warn('No se pudo cargar resumen de caja');
      } catch (err) { console.error('Error resumen caja:', err); }
    }
  } catch (err) {
    alert('No se pudo cargar las ventas.');
    console.error(err);
  }
}

/* =========================
   VI. Detalle y verificación
   ========================= */
function verDetalle(id) {
  const detalle = document.getElementById('detalleVenta');
  if (!detalle) { console.warn('No existe #detalleVenta'); return; }

  if (pedidoAbierto === id) {
    pedidoAbierto = null;
    window.pedidoAbierto = null;
    const dp = document.getElementById('detalleProductos'); if (dp) dp.innerHTML = '';
    const ep = document.getElementById('estadoPago'); if (ep) ep.innerText = '';
    const bp = document.getElementById('botonPago'); if (bp) bp.disabled = false;
    const ma = document.getElementById('menuAdicional'); if (ma) ma.innerHTML = '';
    actualizarBoton(id, 'Abrir');
    return;
  }

  if (pedidoAbierto !== null) actualizarBoton(pedidoAbierto, 'Abrir');

  pedidoAbierto = id;
  window.pedidoAbierto = pedidoAbierto;
  actualizarBoton(id, 'Cerrar');

  fetch(`api/detalle_pedido.php?id=${id}`)
    .then(res => res.json())
    .then(data => {
      const div = document.getElementById('detalleProductos'); if (!div) return;
      let html = '<table><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th><th>Acción</th></tr>';
      let totalDolares = 0;
      data.forEach(p => {
        const precio = parseFloat(p.precio) || 0;
        const cantidad = parseFloat(p.cantidad) || 0;
        const subtotal = precio * cantidad;
        totalDolares += subtotal;
        html += `<tr>
          <td>${escapeHtml(p.nombre)}</td>
          <td><input type="number" value="${formatearCantidad(cantidad)}" step="any" min="0.001" data-producto="${p.producto_id}"></td>
          <td>$${precio.toFixed(2)}</td>
          <td>$${subtotal.toFixed(2)}</td>
          <td><button onclick="eliminarProductoPedido(${id}, ${p.producto_id})">❌</button></td>
        </tr>`;
      });
      const totalBs = totalDolares * tasaBs;
      html += `</table>
        <p><strong>Total en $:</strong> $${totalDolares.toFixed(2)}</p>
        <p><strong>Total en Bs:</strong> Bs ${totalBs.toFixed(2)}</p>
        <button onclick="guardarCambios()">Guardar cambios</button>`;
      div.innerHTML = html;
    })
    .catch(err => console.error('Error detalle pedido:', err));

  // llamar verificar_pago con id y pedido_id (compatibilidad)
  fetch(`api/verificar_pago.php?id=${encodeURIComponent(id)}&pedido_id=${encodeURIComponent(id)}`)
  .then(res => res.json())
  .then(data => {
    const estadoPago = document.getElementById('estadoPago');
    const botonPago = document.getElementById('botonPago');
    if (!estadoPago || !botonPago) return;

    const t = normalizarTotales(data);
    const EPS = 0.01;
    const pagadoCalc = (t.totalPagadoUsd + EPS) >= t.totalPedidoUsd;
    const tasa = (data.tasa_bcv !== undefined) ? Number(data.tasa_bcv) : (data.tasa_pedido !== undefined ? Number(data.tasa_pedido) : tasaBs || 1);

    if (pagadoCalc) {
      estadoPago.innerHTML = `<strong style="color:green;">✅ Pedido pagado</strong>`;
      botonPago.disabled = true;
    } else {
      const totalBs = t.totalBs !== null ? t.totalBs : (t.totalPedidoUsd * tasa);
      const abonadoBs = t.pagadoBs !== null ? t.pagadoBs : (t.totalPagadoUsd * tasa);
      const deudaUsd = Math.max(0, t.totalPedidoUsd - t.totalPagadoUsd);
      const deudaBs = (totalBs !== null && abonadoBs !== null) ? (totalBs - abonadoBs) : null;

      estadoPago.innerHTML = `
        <strong style="color:red;">💰 Deuda pendiente</strong><br>
        Abonado: Bs ${abonadoBs !== null ? Number(abonadoBs).toFixed(2) : '0.00'} ≈ $${t.totalPagadoUsd.toFixed(2)}<br>
        Total: $${t.totalPedidoUsd.toFixed(2)} / Bs ${totalBs !== null ? Number(totalBs).toFixed(2) : '—'}<br>
        Deuda: $${deudaUsd.toFixed(2)} / Bs ${deudaBs !== null ? Number(deudaBs).toFixed(2) : '—'}<br>
        Tasa: Bs ${tasa.toFixed(2)} / $
      `;
      botonPago.disabled = false;
    }
    cargarMenuAdicional(id);
  })
  .catch(err => {
    console.error('Error al verificar pago:', err);
    const estadoPago = document.getElementById('estadoPago'); if (estadoPago) estadoPago.innerText = 'No se pudo verificar el estado de pago.';
  });
}

// Normalizador de respuesta de verificar_pago
function normalizarTotales(resp) {
  const totalPedidoUsd = (resp.total_pedido_usd !== undefined) ? parseFloat(resp.total_pedido_usd)
                         : (resp.total_pedido !== undefined ? parseFloat(resp.total_pedido) :
                            (resp.total_publico !== undefined ? parseFloat(resp.total_publico) : 0));
  const totalPagadoUsd = (resp.total_pagado_usd !== undefined) ? parseFloat(resp.total_pagado_usd)
                          : (resp.total_pagado !== undefined ? parseFloat(resp.total_pagado) : 0);

  const tasa = resp.tasa_bcv !== undefined ? parseFloat(resp.tasa_bcv) : (resp.tasa_pedido !== undefined ? parseFloat(resp.tasa_pedido) : null);

  const totalBs = (resp.total_pedido_bs !== undefined) ? parseFloat(resp.total_pedido_bs) : (tasa ? (totalPedidoUsd * tasa) : null);
  const pagadoBs = (resp.total_pagado_bs !== undefined) ? parseFloat(resp.total_pagado_bs) : (tasa ? (totalPagadoUsd * tasa) : null);

  return {
    totalPedidoUsd: isNaN(totalPedidoUsd) ? 0 : totalPedidoUsd,
    totalPagadoUsd: isNaN(totalPagadoUsd) ? 0 : totalPagadoUsd,
    totalBs: totalBs !== null ? Number(totalBs.toFixed(2)) : null,
    pagadoBs: pagadoBs !== null ? Number(pagadoBs.toFixed(2)) : null,
    isPagado: !!resp.is_pagado_calc
  };
}

/* =========================
   VI. Registrar pago
   ========================= */
async function registrarPago() {
  const botonPago = document.getElementById('botonPago');
  if (botonPago) botonPago.disabled = true;

  const pagos = [
    { metodo: 'efectivo', monto: parseFloat(document.getElementById('efectivo')?.value) || 0, moneda: 'bs' },
    { metodo: 'tarjeta', monto: parseFloat(document.getElementById('tarjeta')?.value) || 0, moneda: 'usd' },
    { metodo: 'divisas', monto: parseFloat(document.getElementById('divisas')?.value) || 0, moneda: 'usd' },
    { metodo: 'pagomovil', monto: parseFloat(document.getElementById('pagomovil')?.value) || 0, moneda: 'bs' }
  ].filter(p => p.monto > 0);

  const abierto = (typeof pedidoAbierto !== 'undefined' && pedidoAbierto) ? pedidoAbierto : (window.pedidoAbierto || null);
  if (!abierto) {
    alert('Debes abrir una venta antes de registrar el pago.');
    if (botonPago) botonPago.disabled = false;
    return;
  }
  const pedidoIdUsar = abierto;

  if (pagos.length === 0) {
    alert('Debes ingresar al menos un monto.');
    if (botonPago) botonPago.disabled = false;
    return;
  }

  try {
    const res = await fetch('api/ventas.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ pedido_id: pedidoIdUsar, pagos })
    });

    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch (e) { data = { _raw: text }; }

    if (!res.ok) {
      const msg = data.error || data._raw || `HTTP ${res.status}`;
      alert(`No se registró el pago: ${msg}`);
      throw new Error(msg);
    }

    alert(data.mensaje || 'Pago registrado con éxito');

    ['efectivo','tarjeta','divisas','pagomovil'].forEach(id => { const el = document.getElementById(id); if (el) el.value = 0; });

    // preferir campos *_usd devueltos por API, fallback a legacy
    const nuevosTotalPedido = (data.total_pedido_usd !== undefined) ? data.total_pedido_usd : (data.total_pedido !== undefined ? data.total_pedido : data.total);
    const nuevosTotalPagado = (data.total_pagado_usd !== undefined) ? data.total_pagado_usd : (data.total_pagado !== undefined ? data.total_pagado : data.total_pagado_usd || 0);
    actualizarFilaVenta(pedidoIdUsar, data.estado, parseFloat(nuevosTotalPedido||0), parseFloat(nuevosTotalPagado||0));

    try { if (typeof verDetalle === 'function') verDetalle(pedidoIdUsar); } catch (e) { console.warn(e); }
    await new Promise(r => setTimeout(r, 150));
    if (typeof cargarVentas === 'function') cargarVentas();
    if (typeof actualizarResumenCaja === 'function') actualizarResumenCaja();

  } catch (err) {
    console.error('Error en registrarPago:', err);
  } finally {
    if (botonPago) botonPago.disabled = false;
  }
}

/* =========================
   VII. actualizarFilaVenta
   ========================= */
function actualizarFilaVenta(id, estadoBackend, totalPedido, totalPagado) {
  const fila = document.querySelector(`#tablaVentas tr[data-id="${id}"]`);
  if (!fila) return;
  const tp = (typeof totalPedido === 'number') ? totalPedido : parseFloat(totalPedido) || 0;
  const tpag = (typeof totalPagado === 'number') ? totalPagado : parseFloat(totalPagado) || 0;
  const EPS = 0.01;
  const isPagadoCalc = (tpag + EPS) >= tp;
  const estadoNorm = isPagadoCalc ? 'pagado' : (estadoBackend || 'deudor');

  fila.classList.toggle('resaltado-pagado', isPagadoCalc);
  fila.classList.toggle('resaltado-deudor', !isPagadoCalc);

  const estadoTd = fila.querySelector('.estado') || fila.querySelector('td:nth-child(4)');
  if (estadoTd) estadoTd.textContent = estadoNorm;

  let totalTd = fila.querySelector('.total');
  if (!totalTd) {
    const tds = fila.querySelectorAll('td');
    if (tds.length >= 5) { totalTd = tds[4]; totalTd.classList.add('total'); }
    else { totalTd = document.createElement('td'); totalTd.className = 'total'; const last = fila.querySelector('td:last-child'); if (last) fila.insertBefore(totalTd, last); else fila.appendChild(totalTd); }
  }

  const totalMainText = `$${tp.toFixed(2)}`;
  let pagoSpan = totalTd.querySelector('.total-pagado');
  if (!pagoSpan) {
    totalTd.textContent = totalMainText + ' ';
    pagoSpan = document.createElement('div');
    pagoSpan.className = 'total-pagado';
    pagoSpan.style.fontSize = '0.85em';
    pagoSpan.style.opacity = '0.9';
    totalTd.appendChild(pagoSpan);
  } else {
    Array.from(totalTd.childNodes).forEach(node => { if (node !== pagoSpan) totalTd.removeChild(node); });
    totalTd.insertBefore(document.createTextNode(totalMainText + ' '), pagoSpan);
  }
  pagoSpan.textContent = `Pagado: $${tpag.toFixed(2)}`;
  pagoSpan.style.color = isPagadoCalc ? 'green' : '';

  const boton = fila.querySelector('button');
  if (boton) {
    boton.disabled = false;
    boton.removeAttribute('aria-disabled');
    boton.textContent = isPagadoCalc ? 'Abrir (Pagado)' : 'Abrir';
  }

  fila.dataset.estado = estadoNorm;
  fila.dataset.totalPedido = tp.toFixed(2);
  fila.dataset.totalPagado = tpag.toFixed(2);
}

/* =========================
   VIII. Guardar cambios y productos
   ========================= */
function guardarCambios() {
  if (!pedidoAbierto) { alert('No hay un pedido abierto para guardar cambios.'); return; }
  const botonGuardar = document.querySelector('#detalleProductos button[onclick="guardarCambios()"]');
  if (botonGuardar) botonGuardar.disabled = true;
  const inputs = document.querySelectorAll('#detalleProductos input[data-producto]');
  const detalles = [];
  inputs.forEach(input => {
    const producto_id = parseInt(input.getAttribute('data-producto'), 10);
    const cantidad = parseFloat(input.value) || 0;
    if (cantidad > 0) detalles.push({ producto_id, cantidad });
  });
  if (detalles.length === 0) { if (botonGuardar) botonGuardar.disabled = false; return alert('No hay cantidades válidas para actualizar.'); }

  fetch('api/actualizar_detalle.php', {
    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pedido_id: pedidoAbierto, detalles })
  })
    .then(async res => {
      const text = await res.text();
      let data; try { data = JSON.parse(text); } catch (e) { data = { _raw: text }; }
      if (!res.ok) {
        const msg = data && data.error ? data.error : (data && data._raw ? data._raw : `HTTP ${res.status}`);
        alert(`❌ No se pudieron guardar cambios: ${msg}`); throw new Error(msg);
      }
      if (data.error) alert(`❌ ${data.error}`); else {
        alert(data.mensaje || 'Cambios guardados correctamente');
        try { verDetalle(pedidoAbierto); } catch (e) { console.warn('verDetalle no disponible', e); }
        try { cargarVentas(); } catch (e) { console.warn('cargarVentas no disponible', e); }
        try { actualizarResumenCaja(); } catch (e) { console.warn('actualizarResumenCaja no disponible', e); }
      }
      return data;
    })
    .catch(err => console.error('Error en guardarCambios:', err))
    .finally(() => { if (botonGuardar) botonGuardar.disabled = false; });
}

/* =========================
   IX. Carrito / combos / envio pedido
   ========================= */
function actualizarCantidad(index, nuevaCantidad) {
  const cantidad = parseFloat(nuevaCantidad) || 0;
  if (cantidad <= 0) eliminarDelCarrito(index);
  else { carrito[index].cantidad = cantidad; renderCarrito(); actualizarTotal(); }
}
function eliminarDelCarrito(index) { carrito.splice(index, 1); renderCarrito(); actualizarTotal(); }

function agregarAlCarrito(id, nombre, precio) {
  const input = document.getElementById(`cantidad-${id}`);
  const cantidad = parseFloat(input?.value) || 0;
  if (cantidad <= 0) { alert('Cantidad inválida'); return; }
  const existente = carrito.find(p => p.id === id);
  if (existente) existente.cantidad += cantidad; else carrito.push({ id, nombre, precio, cantidad });
  if (input) input.value = '';
  renderCarrito(); actualizarTotal();
}

// Lock global para evitar envíos dobles
let __enviandoPedido = false;

async function enviarPedido() {
  if (__enviandoPedido) return;
  __enviandoPedido = true;

  const submitBtn = document.getElementById('btnEnviarPedido') || document.querySelector('button[onclick="enviarPedido()"]');
  if (submitBtn) submitBtn.disabled = true;

  try {
    const tipoSelect = document.getElementById('tipoPedido');
    const nombreInput = document.getElementById('nombrePedido');

    if (!tipoSelect) {
      alert('No se encontró el selector de tipo de pedido.');
      return;
    }

    const tipo = tipoSelect.value;
    const nombre = nombreInput?.value.trim() || '';

    if (tipo === 'en_sitio' && !mesaSeleccionada) {
      alert('Debes seleccionar una mesa para pedidos en sitio.');
      return;
    }
    if (carrito.length === 0) {
      alert('No has agregado productos al pedido.');
      return;
    }

    const body = {
      tipo,
      nombre,
      productos: carrito.map(p => ({ producto_id: p.id, cantidad: p.cantidad })),
      mesa_id: tipo === 'en_sitio' ? mesaSeleccionada : null
    };

    const res = await fetch('api/pedido.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });

    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch (e) { data = { _raw: text }; }

    if (!res.ok) {
      const msg = data && (data.error || data._raw) ? (data.error || data._raw) : `HTTP ${res.status}`;
      alert('❌ ' + msg);
      throw new Error(msg);
    }

    const pedidoId = data.pedido_id ?? (data.id ?? null);
    alert(data.mensaje ? data.mensaje : (`✅ Pedido registrado${pedidoId ? ' (#' + pedidoId + ')' : ''}`));

    // soft reset
    carrito = [];
    mesaSeleccionada = null;
    pedidoAbierto = null;
    window.pedidoAbierto = null;

    if (tipoSelect) try { tipoSelect.value = 'para_llevar'; } catch (e) {}
    if (nombreInput) nombreInput.value = '';

    document.querySelectorAll('#productosDisponibles input[type="number"], #editorComboProductos input[type="number"], #menu input[type="number"], #menu input[type="text"], #listaCarrito input[type="number"]').forEach(el => {
      try { el.value = ''; } catch (e) {}
    });

    try { renderCarrito(); } catch (e) { console.warn('renderCarrito no definido', e); }
    try { mostrarVistaPrevia(); } catch (e) {}
    try { cargarMesas(); } catch (e) {}
    try { cargarVentas(); } catch (e) {}
    try { actualizarResumenCaja(); } catch (e) {}

    const editorModal = document.getElementById('editorComboModal');
    if (editorModal) editorModal.style.display = 'none';

    if (submitBtn) {
      setTimeout(() => { submitBtn.disabled = false; }, 1200);
    }

  } catch (err) {
    console.error('Error en enviarPedido:', err);
  } finally {
    __enviandoPedido = false;
    const submitBtn2 = document.getElementById('btnEnviarPedido') || document.querySelector('button[onclick="enviarPedido()"]');
    if (submitBtn2) submitBtn2.disabled = false;
  }
}

/* =========================
   X. Combos, cocina, adicionales y utilidades restantes
   ========================= */
function mostrarVistaPrevia() {
  const div = document.getElementById('vistaPrevia'); if (!div) return;
  if (carrito.length === 0) { div.innerHTML = '<p>No hay productos en el pedido.</p>'; return; }
  let html = '<table><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>'; let totalUSD = 0;
  carrito.forEach(p => { const subtotal = p.precio * p.cantidad; totalUSD += subtotal; html += `<tr><td>${escapeHtml(p.nombre)}</td><td>${formatearCantidad(p.cantidad)}</td><td>$${p.precio.toFixed(2)}</td><td>$${subtotal.toFixed(2)}</td></tr>`; });
  const totalBs = totalUSD * tasaBs; html += `</table><p><strong>Total en USD:</strong> $${totalUSD.toFixed(2)}</p><p><strong>Total en Bs:</strong> Bs ${totalBs.toFixed(2)}</p>`;
  div.innerHTML = html;
}

function cargarCombos() {
  fetch('api/combos.php').then(res => res.json()).then(data => {
    const contenedor = document.getElementById('listaCombos'); if (!contenedor) return; contenedor.innerHTML = '';
    data.forEach(combo => {
      const div = document.createElement('div'); div.className = 'combo';
      div.innerHTML = `<strong>${escapeHtml(combo.nombre)}</strong> - $${Number(combo.precio).toFixed(2)}<br><button onclick="agregarCombo(${combo.id})">Agregar combo</button><button onclick="abrirEditorCombo(${combo.id})">✏️ Editar combo</button><div class="detalle-combo"><em>Cargando...</em></div>`;
      contenedor.appendChild(div);
      (async () => {
        try {
          const detalleRes = await fetch(`api/combo_detalle.php?id=${combo.id}`); const comboDetalle = await detalleRes.json();
          const detalleDiv = div.querySelector('.detalle-combo');
          if (!comboDetalle || !comboDetalle.productos || comboDetalle.productos.length === 0) detalleDiv.innerHTML = '<em>Sin productos</em>';
          else { const lista = comboDetalle.productos.map(p => `<li>${escapeHtml(p.nombre)} x ${parseFloat(p.cantidad).toFixed(2)}</li>`).join(''); detalleDiv.innerHTML = `<ul>${lista}</ul>`; }
        } catch { const detalleDiv = div.querySelector('.detalle-combo'); if (detalleDiv) detalleDiv.innerHTML = '<em>Error al cargar productos</em>'; }
      })();
    });
  }).catch(err => console.error('Error cargarCombos:', err));
}

async function cargarListaCombos() { if (__cargarCombosRunning) return; __cargarCombosRunning = true; try { await cargarCombos(); } finally { __cargarCombosRunning = false; } }

function agregarCombo(id) {
  fetch(`api/combo_detalle.php?id=${id}`).then(res => res.json()).then(combo => {
    if (!combo || !Array.isArray(combo.productos)) { alert('❌ Combo inválido o sin productos.'); return; }
    fetch('api/productos.php').then(res => res.json()).then(productos => {
      combo.productos.forEach(p => {
        const producto = productos.find(prod => Number(prod.id) === Number(p.producto_id));
        if (producto) carrito.push({ id: producto.id, nombre: producto.nombre, precio: parseFloat(producto.precio), cantidad: p.cantidad });
      });
      renderCarrito(); actualizarTotal();
    });
  }).catch(() => alert('❌ Error al agregar combo.'));
}

function cargarPedidosCocina() {
  fetch('api/pedidos_cocina.php').then(res => { if (!res.ok) throw new Error('Error al cargar pedidos'); return res.json(); }).then(data => {
    const contenedor = document.getElementById('pedidos'); if (!contenedor) return; contenedor.innerHTML = '';
    data.forEach(p => {
      const card = document.createElement('div'); card.className = 'pedido-card estado-' + (p.estado_cocina || '').toLowerCase();
      card.innerHTML = `<h2>Pedido #${p.id}</h2><p><strong>Hora:</strong> ${escapeHtml(p.hora || '')}</p><p><strong>Estado:</strong> ${escapeHtml(p.estado_cocina || '')}</p><p><strong>Mesa:</strong> ${escapeHtml(p.mesa ?? '—')}</p><p><strong>Cliente:</strong> ${escapeHtml(p.nombre || '—')}</p><div class="productos">${p.productos}</div>${p.estado_cocina !== 'listo' ? `<button onclick="marcarPedidoListo(${p.id})">✅ Marcar como listo</button>` : ''}`;
      contenedor.appendChild(card);
    });
  }).catch(err => console.error('Error en cargarPedidosCocina:', err));
}
window.marcarPedidoListo = function (id) { fetch('api/marcar_listo.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({id}) }).then(() => cargarPedidosCocina()).catch(err => console.error('marcarPedidoListo error:', err)); };

window.eliminarProductoPedido = function(pedidoId, productoId) {
  if (!confirm('¿Eliminar este producto del pedido?')) return;
  fetch('api/eliminar_producto_pedido.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pedido_id: pedidoId, producto_id: productoId }) })
    .then(async res => {
      const text = await res.text(); let data; try { data = JSON.parse(text); } catch(e) { data = { _raw: text }; }
      if (!res.ok) { const msg = data && data.error ? data.error : (data && data._raw ? data._raw : `HTTP ${res.status}`); alert(`❌ No se pudo eliminar: ${msg}`); throw new Error(msg); }
      if (data.error) { alert(`❌ ${data.error}`); return; }
      alert(data.mensaje || 'Producto eliminado');
      try { verDetalle(pedidoId); } catch(e) {}
      try { cargarVentas(); } catch(e) {}
      try { actualizarResumenCaja(); } catch(e) {}
    })
    .catch(err => console.error('Error al eliminar producto:', err));
};

function buscarProducto() {
  const texto = document.getElementById('inputBusqueda')?.value.trim().toLowerCase() || '';
  const contenedor = document.getElementById('resultadosBusqueda'); if (!contenedor) return;
  if (texto.length === 0) { contenedor.innerHTML = ''; return; }
  fetch('api/productos.php').then(res => res.json()).then(data => {
    contenedor.innerHTML = '';
    const resultados = data.filter(p => p.nombre.toLowerCase().includes(texto));
    resultados.forEach(p => {
      const precioUSD = parseFloat(p.precio) || 0;
      const precioBs = precioUSD * tasaBs;
      const div = document.createElement('div'); div.className = 'producto';
      div.innerHTML = `<strong>${escapeHtml(p.nombre)}</strong><br>$${isNaN(precioUSD) ? '—' : precioUSD.toFixed(2)} | Bs ${isNaN(precioBs) ? '—' : precioBs.toFixed(2)}<br><input type="number" id="cantidad-${p.id}" step="0.01" min="0.01" placeholder="Cantidad"><button onclick="agregarAlCarrito(${p.id}, '${escapeHtml(p.nombre)}', ${precioUSD})">Agregar</button>`;
      contenedor.appendChild(div);
    });
  }).catch(err => console.error('buscarProducto error:', err));
}

function cargarMenuAdicional(pedidoId) {
  const div = document.getElementById('menuAdicional'); if (!div) return;
  div.innerHTML = `<h4>Agregar productos adicionales</h4><input type="text" id="busquedaAdicional" placeholder="Buscar producto..." oninput="buscarProductoAdicional(${pedidoId})" style="width:100%; padding:8px; font-size:16px; margin-bottom:10px;"><div id="resultadosAdicionales"></div>`;
}

let buscarTimeout;
function buscarProductoAdicional(pedidoId) {
  clearTimeout(buscarTimeout);
  buscarTimeout = setTimeout(() => {
    const texto = document.getElementById('busquedaAdicional')?.value.trim().toLowerCase() || '';
    const contenedor = document.getElementById('resultadosAdicionales'); if (!contenedor) return;
    if (texto.length === 0) { contenedor.innerHTML = ''; return; }
    fetch('api/productos.php').then(res => res.json()).then(data => {
      contenedor.innerHTML = '';
      const resultados = data.filter(p => p.nombre.toLowerCase().includes(texto));
      resultados.forEach(p => {
        const precioUSD = parseFloat(p.precio) || 0; const precioBs = precioUSD * tasaBs;
        const div = document.createElement('div'); div.className = 'tarjeta-producto';
        div.innerHTML = `<strong>${escapeHtml(p.nombre)}</strong><p>$${precioUSD.toFixed(2)} | Bs ${precioBs.toFixed(2)}</p><label>Cantidad:</label><input type="number" id="cantidad-adicional-${p.id}" step="0.01" min="0.01" placeholder="Ej: 0.5"><button onclick="agregarProductoAdicional(${pedidoId}, ${p.id}, '${escapeHtml(p.nombre)}', ${precioUSD})">Agregar</button>`;
        contenedor.appendChild(div);
      });
    }).catch(err => console.error('buscarProductoAdicional error:', err));
  }, 300);
}

function agregarProductoAdicional(pedidoId, productoId, nombre, precio) {
  const input = document.getElementById(`cantidad-adicional-${productoId}`); const cantidad = parseFloat(input?.value) || 0;
  if (cantidad <= 0) { alert('Cantidad inválida'); return; }
  fetch('api/agregar_producto_pedido.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pedido_id: pedidoId, producto_id: productoId, cantidad }) })
    .then(res => res.json()).then(data => { alert(`${nombre} agregado correctamente`); verDetalle(pedidoId); }).catch(err => console.error('agregarProductoAdicional error:', err));
}

/* =========================
   XIII. Resumen caja
   ========================= */
function actualizarResumenCaja() {
  const desde = document.getElementById('filtroDesde')?.value || '';
  const hasta = document.getElementById('filtroHasta')?.value || '';
  if (!desde || !hasta || new Date(desde) > new Date(hasta)) return;
  fetch(`api/resumen_caja.php?desde=${desde}&hasta=${hasta}`).then(res => res.json()).then(data => {
    const resumen = document.getElementById('resumenCaja'); if (!resumen) return;
    const metodos = data.resumen?.metodos || {}; const total = data.resumen?.total_ventas || 0;
    let html = '<h4>Resumen de caja</h4><ul>'; for (const metodo in metodos) html += `<li>${escapeHtml(metodo)}: $${parseFloat(metodos[metodo]).toFixed(2)}</li>`;
    html += `</ul><p><strong>Total ventas:</strong> $${parseFloat(total).toFixed(2)}</p>`;
    resumen.innerHTML = html;
  }).catch(err => console.error('Error al actualizar resumen de caja:', err));
}

function actualizarVisibilidadMesas() {
  const tipoSelect = document.getElementById('tipoPedido');
  const grupoMesas = document.getElementById('grupoMesas');
  if (!tipoSelect || !grupoMesas) return;
  grupoMesas.style.display = (tipoSelect.value === 'en_sitio') ? 'block' : 'none';
}
window.actualizarVisibilidadMesas = actualizarVisibilidadMesas;

/* =========================
   XIV. Inicialización DOM
   ========================= */
document.addEventListener('DOMContentLoaded', () => {
  if (window.__initMainOnce) return; window.__initMainOnce = true; console.log('[init-main] Ejecutando inicialización principal');
  cargarTasa();
  if (document.getElementById('mesas')) cargarMesas();
  if (document.getElementById('menu')) { try { if (typeof cargarMenu === 'function') cargarMenu(); } catch {} }
  if (document.getElementById('tipoPedido')) { actualizarVisibilidadMesas(); document.getElementById('tipoPedido').addEventListener('change', actualizarVisibilidadMesas); }
  if (document.getElementById('pedidos')) { cargarPedidosCocina(); if (!window.__cocinaInterval) window.__cocinaInterval = setInterval(cargarPedidosCocina, 5000); }
  if (document.getElementById('tablaVentas')) cargarVentas();
  if (document.getElementById('tablaProductos')) { try { if (typeof cargarProductos === 'function') cargarProductos(); } catch {} }
  if (document.getElementById('productosDisponibles')) cargarProductosParaCombo();
  if (document.getElementById('listaCombos')) cargarListaCombos();
});

document.addEventListener('DOMContentLoaded', () => {
  if (window.__initNewOnce) return; window.__initNewOnce = true; console.log('[init-new] Ejecutando inicialización secundaria');
  cargarTasa();
  if (document.getElementById('listaCombos')) { if (!__cargarCombosRunning) { __cargarCombosRunning = true; cargarListaCombos().finally(() => { __cargarCombosRunning = false; }); } }
  if (document.getElementById('mesas')) cargarMesas();
  if (document.getElementById('tipoPedido')) { actualizarVisibilidadMesas(); document.getElementById('tipoPedido').addEventListener('change', actualizarVisibilidadMesas); }
});
