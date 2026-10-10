/*==========================================================
    REGISTER.JS
==========================================================*/

document.addEventListener("DOMContentLoaded", () => {

    /*======================================================
        SELECTORES
    ======================================================*/

    const form = document.querySelector(".register-form");

    const nombres = document.getElementById("nombres");

    const apellidos = document.getElementById("apellidos");

    const email = document.getElementById("email");

    const password = document.getElementById("password");

    const confirmPassword = document.getElementById("confirm_password");

    const toggleButtons = document.querySelectorAll(".toggle-password");

const capsWarning = document.querySelector(".caps-warning");

const strengthFill = document.querySelector(".strength-fill");

const strengthText = document.querySelector(".strength-text");

    /*======================================================
        UTILIDADES
    ======================================================*/

    function obtenerGrupo(input){

        return input.closest(".input-group");

    }

    function obtenerMensaje(input){

        return obtenerGrupo(input)
            .querySelector(".input-message");

    }

    function marcarValido(input, mensaje = ""){

        input.classList.remove("is-invalid");

        input.classList.add("is-valid");

        const texto = obtenerMensaje(input);

        if(texto){

            texto.textContent = mensaje;

            texto.style.color = "#22C55E";

        }

    }

    function marcarInvalido(input, mensaje){

        input.classList.remove("is-valid");

        input.classList.add("is-invalid");

        const texto = obtenerMensaje(input);

        if(texto){

            texto.textContent = mensaje;

            texto.style.color = "#EF4444";

        }

    }

    function limpiarEstado(input){

        input.classList.remove("is-valid");

        input.classList.remove("is-invalid");

        const texto = obtenerMensaje(input);

        if(texto){

            texto.textContent = "";

        }

    }

    /*======================================================
        VALIDAR NOMBRES
    ======================================================*/

    function validarNombre(input){

        const valor = input.value.trim();

        if(valor === ""){

            limpiarEstado(input);

            return false;

        }

        if(valor.length < 2){

            marcarInvalido(
                input,
                "Debe tener mínimo 2 caracteres."
            );

            return false;

        }

        if(!/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/.test(valor)){

            marcarInvalido(
                input,
                "Solo se permiten letras."
            );

            return false;

        }

        marcarValido(
            input,
            "Correcto."
        );

        return true;

    }

    /*======================================================
        VALIDAR EMAIL
    ======================================================*/

    function validarEmail(){

        const valor = email.value.trim();

        if(valor === ""){

            limpiarEstado(email);

            return false;

        }

        const expresion =

        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if(!expresion.test(valor)){

            marcarInvalido(
                email,
                "Correo electrónico inválido."
            );

            return false;

        }

        marcarValido(
            email,
            "Correo válido."
        );

        return true;

    }
    /*======================================================
    FUERZA DE CONTRASEÑA
======================================================*/

function validarPassword(){

    const valor = password.value;

    if(valor === ""){

        limpiarEstado(password);

        strengthFill.style.width = "0%";

        strengthFill.style.background = "#2563EB";

        strengthText.textContent =
            "La contraseña debe contener mínimo 8 caracteres.";

        return false;

    }

    let fuerza = 0;

    if(valor.length >= 8) fuerza++;

    if(/[A-Z]/.test(valor)) fuerza++;

    if(/[a-z]/.test(valor)) fuerza++;

    if(/[0-9]/.test(valor)) fuerza++;

    if(/[^A-Za-z0-9]/.test(valor)) fuerza++;

    const porcentaje = fuerza * 20;

    strengthFill.style.width = porcentaje + "%";

    switch(fuerza){

        case 1:
        case 2:

            strengthFill.style.background="#EF4444";

            strengthText.textContent="Contraseña débil.";

            marcarInvalido(
                password,
                "Contraseña demasiado débil."
            );

            return false;

        case 3:

            strengthFill.style.background="#F59E0B";

            strengthText.textContent="Contraseña aceptable.";

            marcarValido(
                password,
                "Puede mejorarse."
            );

            return false;

        case 4:

            strengthFill.style.background="#3B82F6";

            strengthText.textContent="Contraseña fuerte.";

            marcarValido(
                password,
                "Buena contraseña."
            );

            return true;

        case 5:

            strengthFill.style.background="#22C55E";

            strengthText.textContent="Contraseña muy segura.";

            marcarValido(
                password,
                "Excelente contraseña."
            );

            return true;

    }

}
/*======================================================
    CONFIRMAR CONTRASEÑA
======================================================*/

function validarConfirmacion(){

    const valor = confirmPassword.value;

    if(valor === ""){

        limpiarEstado(confirmPassword);

        return false;

    }

    if(valor !== password.value){

        marcarInvalido(

            confirmPassword,

            "Las contraseñas no coinciden."

        );

        return false;

    }

    marcarValido(

        confirmPassword,

        "Las contraseñas coinciden."

    );

    return true;

}
/*======================================================
    CAPS LOCK
======================================================*/

function detectarCaps(evento){

    if(!capsWarning) return;

    if(evento.getModifierState("CapsLock")){

        capsWarning.classList.add("active");

    }else{

        capsWarning.classList.remove("active");

    }

}
/*======================================================
    MOSTRAR / OCULTAR CONTRASEÑA
======================================================*/

toggleButtons.forEach((boton)=>{

    boton.addEventListener("click",()=>{

        const input = boton.previousElementSibling;

        const icono = boton.querySelector("i");

        if(input.type==="password"){

            input.type="text";

            icono.classList.replace(

                "fa-eye",

                "fa-eye-slash"

            );

        }else{

            input.type="password";

            icono.classList.replace(

                "fa-eye-slash",

                "fa-eye"

            );

        }

    });

});


  /*======================================================
    EVENTOS
======================================================*/

nombres.addEventListener("input", () => {

    validarNombre(nombres);

});

nombres.addEventListener("blur", () => {

    validarNombre(nombres);

});

apellidos.addEventListener("input", () => {

    validarNombre(apellidos);

});

apellidos.addEventListener("blur", () => {

    validarNombre(apellidos);

});

email.addEventListener("input", validarEmail);

email.addEventListener("blur", validarEmail);

password.addEventListener("input", () => {

    validarPassword();

    validarConfirmacion();

});

password.addEventListener("keyup", detectarCaps);

password.addEventListener("keydown", detectarCaps);

confirmPassword.addEventListener("input", validarConfirmacion);

confirmPassword.addEventListener("keyup", detectarCaps);

confirmPassword.addEventListener("keydown", detectarCaps);

/*======================================================
    SUBMIT
======================================================*/

form.addEventListener("submit", () => {

    validarNombre(nombres);

    validarNombre(apellidos);

    validarEmail();

    validarPassword();

    validarConfirmacion();

});

});