/*
==========================================================
 BODYTECH
 Login
==========================================================
*/

"use strict";

ready(() => {

    initializePasswordToggle();

    initializeAutoFocus();

    initializeLoginLoading();

    initializeKeyboardNavigation();

    initializeStaggerAnimation();

});


/*==========================================================
    MOSTRAR / OCULTAR CONTRASEÑA
==========================================================*/

function initializePasswordToggle() {

    const button = $(".toggle-password");

    const password = $("#password");

    if (!button || !password) return;

    const icon = $("i", button);

    button.addEventListener("click", () => {

        const isPassword = password.type === "password";

        password.type = isPassword ? "text" : "password";

        icon.classList.toggle("fa-eye");

        icon.classList.toggle("fa-eye-slash");

    });

}


/*==========================================================
    AUTO FOCUS
==========================================================*/

function initializeAutoFocus() {

    const email = $("#email");

    if (!email) return;

    setTimeout(() => {

        email.focus();

    }, 700);

}


/*==========================================================
    BOTÓN LOADING
==========================================================*/

function initializeLoginLoading() {

    const form = $(".login-form");

    const button = $(".login-btn");

    if (!form || !button) return;

    form.addEventListener("submit", () => {

        setLoading(button, "Iniciando sesión...");

    });

}


/*==========================================================
    NAVEGACIÓN CON TECLADO
==========================================================*/

function initializeKeyboardNavigation() {

    const inputs = $$("input");

    inputs.forEach(input => {

        input.addEventListener("keydown", (e) => {

            if (e.key === "Enter") {

                const form = input.form;

                if (form) {

                    form.requestSubmit();

                }

            }

        });

    });

}


/*==========================================================
    ANIMACIÓN ESCALONADA
==========================================================*/

function initializeStaggerAnimation() {

    const animatedElements = [

        ".logo-circle",

        ".login-header h1",

        ".brand",

        ".login-header h2",

        ".description",

        ".form-group:nth-child(2)",

        ".form-group:nth-child(3)",

        ".form-options",

        ".login-btn",

        ".login-footer"

    ];

    animatedElements.forEach((selector, index) => {

        const element = $(selector);

        if (!element) return;

        element.style.opacity = "0";

        element.style.animation = `fadeUp .8s ease forwards`;

        element.style.animationDelay = `${index * 120}ms`;

    });

}