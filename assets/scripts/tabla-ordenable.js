// Ordenamiento de tablas por columna (clic en encabezado th)
(function () {
    function parseValor(texto) {
        const limpio = texto.replace(/\s+/g, ' ').trim();
        const numeroLimpio = limpio.replace(/[$,]/g, '');
        if (numeroLimpio !== '' && !isNaN(numeroLimpio)) {
            return { tipo: 'numero', valor: parseFloat(numeroLimpio) };
        }
        return { tipo: 'texto', valor: limpio.toLowerCase() };
    }

    function compararCeldas(textoA, textoB) {
        const a = parseValor(textoA);
        const b = parseValor(textoB);
        if (a.tipo === 'numero' && b.tipo === 'numero') {
            return a.valor - b.valor;
        }
        return a.valor.localeCompare(b.valor, 'es', { numeric: true, sensitivity: 'base' });
    }

    function ordenarTabla(tabla, indiceColumna, ascendente) {
        const tbody = tabla.tBodies[0];
        if (!tbody) return;

        const filas = Array.from(tbody.querySelectorAll('tr'));
        filas.sort((filaA, filaB) => {
            const celdaA = filaA.children[indiceColumna];
            const celdaB = filaB.children[indiceColumna];
            const textoA = celdaA ? celdaA.textContent.trim() : '';
            const textoB = celdaB ? celdaB.textContent.trim() : '';
            const resultado = compararCeldas(textoA, textoB);
            return ascendente ? resultado : -resultado;
        });

        filas.forEach(fila => tbody.appendChild(fila));
    }

    function esColumnaOrdenable(th) {
        return th.textContent.trim().toLowerCase() !== 'acciones';
    }

    function inicializarTabla(tabla) {
        const filaEncabezado = tabla.tHead && tabla.tHead.rows[0];
        if (!filaEncabezado) return;

        Array.from(filaEncabezado.cells).forEach((th, indice) => {
            if (!esColumnaOrdenable(th)) return;

            th.classList.add('th-ordenable');
            th.style.cursor = 'pointer';
            th.style.userSelect = 'none';
            th.setAttribute('tabindex', '0');
            th.setAttribute('role', 'button');
            th.setAttribute('aria-sort', 'none');

            const indicador = document.createElement('span');
            indicador.className = 'th-ordenable-indicador';
            th.appendChild(indicador);

            const activarOrden = () => {
                const ascendente = th.getAttribute('aria-sort') !== 'ascending';

                Array.from(filaEncabezado.cells).forEach(otroTh => {
                    if (otroTh !== th) {
                        otroTh.setAttribute('aria-sort', 'none');
                        const otroIndicador = otroTh.querySelector('.th-ordenable-indicador');
                        if (otroIndicador) otroIndicador.textContent = '';
                    }
                });

                th.setAttribute('aria-sort', ascendente ? 'ascending' : 'descending');
                indicador.textContent = ascendente ? ' ▲' : ' ▼';

                ordenarTabla(tabla, indice, ascendente);
            };

            th.addEventListener('click', activarOrden);
            th.addEventListener('keydown', (evento) => {
                if (evento.key === 'Enter' || evento.key === ' ') {
                    evento.preventDefault();
                    activarOrden();
                }
            });
        });
    }

    function inicializarTablasOrdenables() {
        document.querySelectorAll('main table').forEach(inicializarTabla);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarTablasOrdenables);
    } else {
        inicializarTablasOrdenables();
    }
})();
