// Validacion de formularios de alta con la Constraint Validation API
// (reemplaza el globo nativo del navegador por mensajes con estilo propio)
(function () {
    const SELECTORES_FORMULARIO = [
        '.registrar-producto-details form',
        '.registrar-cliente-details form'
    ];

    function obtenerContenedorCampo(campo) {
        return campo.closest('div') || campo.parentElement;
    }

    // Lectura defensiva: un atributo pattern mal formado puede hacer que el motor
    // de validacion del navegador lance una excepcion al leer .validity. Si eso
    // pasa, se trata el campo como invalido (fail-closed) en vez de dejar pasar
    // el submit sin validar.
    function esCampoValido(campo) {
        try {
            return campo.validity.valid;
        } catch (error) {
            console.error(`No se pudo validar el campo "${campo.name || campo.id}" (revisa su atributo pattern):`, error);
            return false;
        }
    }

    function obtenerMensaje(campo) {
        let validez;
        try {
            validez = campo.validity;
        } catch (error) {
            return 'Este campo tiene una configuración de validación inválida. Contacta al desarrollador.';
        }

        if (validez.valid) return '';
        if (validez.valueMissing) return 'Este campo es obligatorio.';
        // Cualquier otro tipo de invalidez (patternMismatch, typeMismatch,
        // stepMismatch, rangeOverflow/Underflow, badInput, etc.) usa el mensaje
        // en español propio del campo si esta definido.
        return campo.dataset.mensajeError || campo.validationMessage || 'El valor ingresado no es válido.';
    }

    function mostrarError(campo) {
        const contenedor = obtenerContenedorCampo(campo);
        let error = contenedor.querySelector('.campo-error');
        if (!error) {
            error = document.createElement('span');
            error.className = 'campo-error';
            contenedor.appendChild(error);
        }
        error.textContent = obtenerMensaje(campo);
        campo.classList.add('campo-invalido');
        campo.setAttribute('aria-invalid', 'true');
    }

    function limpiarError(campo) {
        const contenedor = obtenerContenedorCampo(campo);
        const error = contenedor.querySelector('.campo-error');
        if (error) error.textContent = '';
        campo.classList.remove('campo-invalido');
        campo.removeAttribute('aria-invalid');
    }

    function validarCampo(campo) {
        if (esCampoValido(campo)) {
            limpiarError(campo);
            return true;
        }
        mostrarError(campo);
        return false;
    }

    function limpiarErroresFormulario(form) {
        form.querySelectorAll('.campo-error').forEach(error => { error.textContent = ''; });
        form.querySelectorAll('.campo-invalido').forEach(campo => {
            campo.classList.remove('campo-invalido');
            campo.removeAttribute('aria-invalid');
        });
    }

    function inicializarValidacion(form) {
        if (form.dataset.validacionInicializada) return;
        form.dataset.validacionInicializada = 'true';
        form.setAttribute('novalidate', '');

        // Se excluyen los botones: participan en willValidate pero no estan
        // envueltos en un <div> propio, por lo que closest('div') caeria en un
        // contenedor ancestro compartido y contaminaria el mensaje de otro campo.
        const campos = Array.from(form.elements).filter(
            elemento => elemento.willValidate && elemento.tagName !== 'BUTTON'
        );

        campos.forEach(campo => {
            campo.addEventListener('input', () => {
                if (campo.classList.contains('campo-invalido')) validarCampo(campo);
            });

            campo.addEventListener('blur', () => validarCampo(campo));
        });

        // Se valida en fase de captura para adelantarse al listener de submit
        // que guarda el registro (assets/scripts/actualizacion-tablas.js).
        form.addEventListener('submit', evento => {
            const esValido = campos.reduce((valido, campo) => validarCampo(campo) && valido, true);

            if (!esValido) {
                evento.preventDefault();
                evento.stopImmediatePropagation();
                const primerInvalido = campos.find(campo => !esCampoValido(campo));
                if (primerInvalido) primerInvalido.focus();
            }
        }, { capture: true });

        form.addEventListener('reset', () => limpiarErroresFormulario(form));
    }

    function inicializarValidacionFormularios() {
        SELECTORES_FORMULARIO.forEach(selector => {
            document.querySelectorAll(selector).forEach(inicializarValidacion);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarValidacionFormularios);
    } else {
        inicializarValidacionFormularios();
    }
})();
