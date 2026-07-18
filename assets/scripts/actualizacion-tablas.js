//Para validar formato de texto
function textoSeguro(valor) {
    const contenedor = document.createElement('div'); //espacio que contiene la info
    contenedor.textContent = valor === undefined || valor === null ? '' : String(valor); //si el valor no está definido o es nulo pone espacio en blanco, si se pone algo pone el valor en tipo string
    return contenedor.innerHTML;
}

//para que el dinero se vea con formato
function formatoDinero(valor){
    const numDinero = Number(valor); //guarda el valor del dinero en una de tipo numero
    //retorna el simbolo de pesos, hace pequeña verificacion de si es un numero y si lo es pone la cantidad en el formato mx con dos digitos minimos y max de (centavos)
    return '$' + (isNaN(numDinero) ? '0.00' : numDinero.toLocaleString('es-MX', {
        minimumFractionDigits: 2, maximumFractionDigits : 2
    }));
}


function inicializarRegistroTabla(opciones){
    //creamos la constante con todos los datos necesarios para el registro
    const{
        coleccion, formSelector, tablaSelector, datosDefecto = [], datosNuevos = {}, renderFila
    } = opciones;

    //si no se tienen ejemplos aun se le pasan los que teniamos como ejemplo (uso de js almacenamiento)
    if(datosDefecto.length && obtenerColeccion(coleccion).length === 0){
        guardarColeccion(coleccion, datosDefecto);
    }

    //obtenemos los datos de la tabla
    const tabla = document.querySelector(tablaSelector);
    //validamos que no esté vacía
    if(!tabla || !tabla.tBodies[0]) return;
    //guardamos el cuerpo de la tabla
    const tbody = tabla.tBodies[0];
    //buscamos el form por su selector, si esta vacio no guarda nada. misma logica para el resto de opciones del details
    const form = formSelector ? document.querySelector(formSelector) : null;
    const botonGuardar = form ? form.querySelector('button[type="submit"]') : null;
    const textoBotonOriginal = botonGuardar ? botonGuardar.textContent : '';


    function pintarTabla(){
        const registros = obtenerColeccion(coleccion);
        //tomamos todos los registros de la tabla y los "renderizamos" a etxto, join une el texto y las separa. se actualiza
        tbody.innerHTML = registros.map(renderFila).join('');
    }

    function llenarForm(registro){
        if(!form) return;

        /*obtiene los atributos de cada registro, por cada uno de ellos busca dentro del form un elemento que coincida con el "campo" o "atributo".
        si existe se pasan los que estaban guardados*/
        Object.keys(registro).forEach(campo => {
            const input = form.elements.namedItem(campo);
            if(input) input.value = registro[campo];
        })
    }

    let enEdicion = null; //var de apoyo para saber si estamos en edicion o no
    function entrarEdicion(registro){
        enEdicion = registro.id;
        //buscamos el registro
        llenarForm(registro);
        //el boton cambia a actualizar
        if(botonGuardar) botonGuardar.innerHTML ="Actualizar";

        //si existe form busca el details mas cercano, sino no se ejecuta y se le pone valor nulo
        const details = form ? form.closest('details') : null;
        if(details){
            //decimos que está abierto
            details.open = true;
            //desplaza la pantalla "suavemente" hasta que quede guardado
            details.scrollIntoView({behavior : 'smooth', block : 'start'});
        }
    }
    
    function salirEdicion(){
        enEdicion = null;
        if(form) form.reset(); //reseteamos con valores iniciales
        if(botonGuardar) botonGuardar.textContent = textoBotonOriginal;
    }

    if(form){
        form.addEventListener('submit', evento =>{
            evento.preventDefault();
            //traemos las entradas y datos del form
            const datosFormulario = Object.fromEntries(new FormData(form).entries())

            if(enEdicion !== null){
                actualizarRegistro(coleccion, enEdicion, datosFormulario); //si editamos actualizamos datos
            }else{
                agregarRegistro(coleccion, {...datosNuevos, ...datosFormulario}); //y si no agregamos el nuevo registro
            }
            //salimos y volvemos a act. datos en pantalla
            salirEdicion();
            pintarTabla();
        });
        
    }

    //listener para que editar y eliminar esten habilitadas en todo momento
    tbody.addEventListener('click', evento =>{
        //identificamos cual boton se presiono
        const boton = evento.target.closest('button');
        if(!boton) return;

        //buscamos la fila cercana y su id
        const fila = boton.closest('tr');
        const id = fila ? fila.dataset.id : null;

        if(id === null || id === undefined) return;

        //hacemos una variable de tipo de accion y la ponemos en min
        const accion = boton.textContent.trim().toLowerCase();

        if(accion === 'editar'){
            //obtenemos la coleccion y buscamos el id del registro
            const registro = obtenerColeccion(coleccion).find(r => String(r.id) === String(id));
            if (registro) entrarEdicion(registro);
            return;
        }

        if(accion === 'eliminar'){
            //primero hacemos que se asegure de querer eliminar
            const confirmacion = confirm("¿Estás seguro que quieres eliminar este registro?");
            if(!confirmacion) return;

            eliminarRegistro(coleccion, id);

            //verificamos que no esté en modo edicion
            if(enEdicion !== null && String(enEdicion) === String(id)){
                salirEdicion();
            }

            pintarTabla();
        }
    })

    pintarTabla();

    return{pintarTabla};

}