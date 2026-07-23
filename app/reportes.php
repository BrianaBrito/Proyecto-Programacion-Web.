<?php
require_once __DIR__ . '/../assets/php/roles.php';
verificarAutenticacion();
verificarAcceso('reportes.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/img/icons/reportesicono.png" type="image/x-icon">
    <title>Reportes | Panel de PyMEs</title>
        <link rel="stylesheet" href="../assets/css/reportes.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.4/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
    
    <div id="navbar-container"></div>

    <main class="main-container">
        <div class="card-reportes">
            <div class="encabezado-reportes">
                <h1 class="titulo-reportes">Centro de Reportes</h1>
                <p class="subtitulo-reportes">Seleccione una entidad para generar y exportar la información.</p>
            </div>
            
            <div class="reportes-grid">
                <div class="entidad-card" onclick="seleccionarEntidad('Productos', this)">
                    <div class="entidad-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </div>
                    <h3>Productos</h3>
                </div>

                <div class="entidad-card" onclick="seleccionarEntidad('Categorías', this)">
                    <div class="entidad-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    </div>
                    <h3>Categorías</h3>
                </div>

                <div class="entidad-card" onclick="seleccionarEntidad('Proveedores', this)">
                    <div class="entidad-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <h3>Proveedores</h3>
                </div>

                <div class="entidad-card" onclick="seleccionarEntidad('Clientes', this)">
                    <div class="entidad-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <h3>Clientes</h3>
                </div>

                <div class="entidad-card" onclick="seleccionarEntidad('Movimientos', this)">
                    <div class="entidad-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <h3>Movimientos</h3>
                </div>
            </div>

            <div class="vista-previa-section">
                <div class="vista-previa-header">
                    <h2 class="titulo-seccion">Vista Previa</h2>
                    <button id="btnExportar" class="action-btn primary disabled" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        Exportar a PDF
                    </button>
                </div>

                <div id="vistaPreviaContenido" class="vista-previa-contenido">
                    <p class="placeholder-text">Seleccione una entidad arriba para visualizar la información del reporte.</p>
                </div>
            </div>

        </div>
    </main>

    <script src="../assets/scripts/navbar-menu.js"></script>
    <script>
        fetch('navbar.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('navbar-container').innerHTML = data;

                const links = document.querySelectorAll('nav ul li a');
                links.forEach(link => {
                    if(link.textContent.trim() === 'Reportes') {
                        link.classList.add('active-link');
                    }
                });
                inicializarMenuHamburguesa();
            })
            .catch(error => console.error("Error al cargar el navbar:", error));

        // Claves usadas por el endpoint del servidor (assets/php/reportes_datos.php)
        const ENTIDADES = {
            'Productos': 'productos',
            'Categorías': 'categorias',
            'Proveedores': 'proveedores',
            'Clientes': 'clientes',
            'Movimientos': 'movimientos',
        };

        // Reporte consultado más recientemente, usado por exportarPDF()
        let reporteActual = null;

        // logica de selección y vista previa: los datos se piden al servidor, que consulta la DB real
        async function seleccionarEntidad(nombreEntidad, elemento) {

            // quitamos la clase activa de todas las tarjetas
            const tarjetas = document.querySelectorAll('.entidad-card');
            tarjetas.forEach(t => t.classList.remove('activa'));

            // agregamos clase activa a la seleccionada
            elemento.classList.add('activa');

            const btnExportar = document.getElementById('btnExportar');
            btnExportar.disabled = true;
            btnExportar.classList.add('disabled');

            const contenedorVista = document.getElementById('vistaPreviaContenido');
            contenedorVista.innerHTML = '<p class="placeholder-text">Cargando datos...</p>';

            const clave = ENTIDADES[nombreEntidad];
            try {
                const respuesta = await fetch(`../assets/php/reportes_datos.php?entidad=${clave}`);
                const datos = await respuesta.json();

                if (!respuesta.ok) {
                    contenedorVista.innerHTML = `<p class="placeholder-text">${datos.error || 'No se pudo cargar el reporte.'}</p>`;
                    return;
                }

                reporteActual = { entidad: nombreEntidad, clave, columnas: datos.columnas, filas: datos.filas };
                renderizarVistaPrevia(nombreEntidad, datos.columnas, datos.filas);

                btnExportar.disabled = false;
                btnExportar.classList.remove('disabled');
            } catch (error) {
                console.error('Error al obtener el reporte:', error);
                contenedorVista.innerHTML = '<p class="placeholder-text">Error de conexión al generar el reporte.</p>';
            }
        }

        function renderizarVistaPrevia(nombreEntidad, columnas, filas) {
            const contenedorVista = document.getElementById('vistaPreviaContenido');
            const fechaHoy = new Date().toLocaleDateString('es-ES');

            const encabezados = columnas.map(columna => `<th>${columna}</th>`).join('');
            const cuerpo = filas.length
                ? filas.map(fila => `<tr>${Object.values(fila).map(valor => `<td>${valor ?? ''}</td>`).join('')}</tr>`).join('')
                : `<tr><td colspan="${columnas.length}">No hay registros para mostrar.</td></tr>`;

            contenedorVista.innerHTML = `
                <div class="reporte-generado animate-fade-in">
                    <div class="reporte-info">
                        <h3>Reporte de ${nombreEntidad}</h3>
                        <span class="reporte-fecha">Generado el: ${fechaHoy}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="tabla-resumen">
                            <thead><tr>${encabezados}</tr></thead>
                            <tbody>${cuerpo}</tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        function formatDinero(valor) {
            const numero = Number(valor);
            return '$' + (isNaN(numero) ? '0.00' : numero.toLocaleString('es-MX', {
                minimumFractionDigits: 2, maximumFractionDigits: 2
            }));
        }

        // Indicadores calculados en el cliente a partir de los mismos datos ya traídos del servidor
        function calcularResumen(clave, filas) {
            switch (clave) {
                case 'productos': {
                    const unidadesTotales = filas.reduce((acc, f) => acc + Number(f.stock || 0), 0);
                    const valorInventario = filas.reduce((acc, f) => acc + Number(f.stock || 0) * Number(f.precio_unitario || 0), 0);
                    const stockBajo = filas.filter(f => Number(f.stock || 0) < 10).length;
                    return [
                        ['Total de productos', filas.length],
                        ['Unidades en stock', unidadesTotales],
                        ['Valor total del inventario', formatDinero(valorInventario)],
                        ['Productos con stock bajo (< 10)', stockBajo],
                    ];
                }
                case 'categorias':
                    return [['Total de categorías', filas.length]];
                case 'proveedores':
                case 'clientes': {
                    const activos = filas.filter(f => f.estado === 'Activo').length;
                    const saldoTotal = filas.reduce((acc, f) => acc + Number(f.saldo || 0), 0);
                    const etiqueta = clave === 'proveedores' ? 'proveedores' : 'clientes';
                    return [
                        [`Total de ${etiqueta}`, filas.length],
                        ['Activos', activos],
                        ['Inactivos', filas.length - activos],
                        ['Saldo total', formatDinero(saldoTotal)],
                    ];
                }
                case 'movimientos': {
                    const entradas = filas.filter(f => f.tipo === 'Entrada');
                    const salidas = filas.filter(f => f.tipo === 'Salida');
                    const unidadesEntrada = entradas.reduce((acc, f) => acc + Number(f.cantidad || 0), 0);
                    const unidadesSalida = salidas.reduce((acc, f) => acc + Number(f.cantidad || 0), 0);
                    return [
                        ['Total de movimientos', filas.length],
                        ['Entradas', entradas.length],
                        ['Salidas', salidas.length],
                        ['Unidades totales de entrada', unidadesEntrada],
                        ['Unidades totales de salida', unidadesSalida],
                        ['Balance neto de unidades', unidadesEntrada - unidadesSalida],
                    ];
                }
                default:
                    return [];
            }
        }

        // Exportación en el cliente con jsPDF: usa los mismos datos ya traidos del servidor
        function exportarPDF() {
            if (!reporteActual || !reporteActual.filas.length) return;

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const fechaHoy = new Date().toLocaleDateString('es-ES');

            doc.setFontSize(14);
            doc.text(`Reporte de ${reporteActual.entidad}`, 14, 15);
            doc.setFontSize(10);
            doc.text(`Generado el: ${fechaHoy}`, 14, 21);

            let siguienteY = 26;
            const resumen = calcularResumen(reporteActual.clave, reporteActual.filas);

            if (resumen.length) {
                doc.setFontSize(11);
                doc.text('Resumen', 14, siguienteY);
                doc.autoTable({
                    startY: siguienteY + 4,
                    head: [['Indicador', 'Valor']],
                    body: resumen,
                    theme: 'grid',
                    styles: { fontSize: 9 },
                    headStyles: { fillColor: [30, 58, 138] },
                    columnStyles: { 0: { cellWidth: 100 } },
                });
                siguienteY = doc.lastAutoTable.finalY + 10;
            }

            doc.setFontSize(11);
            doc.text('Detalle', 14, siguienteY);

            doc.autoTable({
                startY: siguienteY + 4,
                head: [reporteActual.columnas],
                body: reporteActual.filas.map(fila => Object.values(fila).map(valor => valor ?? '')),
                theme: 'striped',
                styles: { fontSize: 8 },
                headStyles: { fillColor: [59, 130, 246] },
            });

            const totalPaginas = doc.internal.getNumberOfPages();
            for (let i = 1; i <= totalPaginas; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(150);
                doc.text(
                    `Página ${i} de ${totalPaginas}`,
                    doc.internal.pageSize.getWidth() - 30,
                    doc.internal.pageSize.getHeight() - 10
                );
            }

            doc.save(`reporte-${reporteActual.entidad.toLowerCase()}-${Date.now()}.pdf`);
        }

        document.getElementById('btnExportar').addEventListener('click', exportarPDF);
    </script>
</body>
</html>
