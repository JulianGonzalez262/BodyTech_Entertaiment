const subtotalOriginal = <?= $total ?>;
let descuentoAplicado = 0;


function aplicarCupon(){

    let codigo = document
        .getElementById('codigo_cupon')
        .value
        .trim();

    let mensaje =
        document.getElementById('mensaje_cupon');

    if(codigo === ''){

        mensaje.innerHTML =
            'Ingresa un cupón.';

        mensaje.style.color =
            'red';

        return;
    }

    descuentoAplicado = 0;

    if(codigo.toUpperCase() === 'BODYTECH10'){

        descuentoAplicado =
            subtotalOriginal * 0.10;

    }

    else if(codigo.toUpperCase() === 'FIT20'){

        descuentoAplicado =
            subtotalOriginal * 0.20;

    }

    else{

        mensaje.innerHTML =
            'Cupón aplicado. Validación final al pagar.';

        mensaje.style.color =
            '#16a34a';
    }

    actualizarTotales();


    if(descuentoAplicado > 0){

        mensaje.innerHTML =
            'Cupón aplicado correctamente.';

        mensaje.style.color =
            '#16a34a';
    }

}


function actualizarTotales(){

    document
        .getElementById('descuento')
        .innerText =
        descuentoAplicado.toFixed(2);


    document
        .getElementById('total_final')
        .innerText =
        (
            subtotalOriginal -
            descuentoAplicado
        ).toFixed(2);

}


function mostrarCamposPago(){

    let metodo =
        document
            .getElementById('metodo_pago')
            .value;


    document
        .getElementById('campos_tarjeta')
        .style.display =
        'none';


    document
        .getElementById('campos_pse')
        .style.display =
        'none';


    document
        .getElementById('campos_efectivo')
        .style.display =
        'none';


    if(metodo === 'tarjeta'){

        document
            .getElementById('campos_tarjeta')
            .style.display =
            'block';

    }


    if(metodo === 'pse'){

        document
            .getElementById('campos_pse')
            .style.display =
            'block';

    }


    if(metodo === 'efectivo'){

        document
            .getElementById('campos_efectivo')
            .style.display =
            'block';

    }

}

