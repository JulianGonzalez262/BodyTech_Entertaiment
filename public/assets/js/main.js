"use strict";

/*==========================================================
    UTILIDADES
==========================================================*/

const $ = (selector, scope = document) => scope.querySelector(selector);

const $$ = (selector, scope = document) =>
    [...scope.querySelectorAll(selector)];

/*==========================================================
    READY
==========================================================*/

function ready(callback){

    if(document.readyState === "loading"){

        document.addEventListener("DOMContentLoaded", callback);

    }else{

        callback();

    }

}

/*==========================================================
    SLEEP
==========================================================*/

function sleep(ms){

    return new Promise(resolve => setTimeout(resolve, ms));

}

/*==========================================================
    DEBOUNCE
==========================================================*/

function debounce(fn, delay = 300){

    let timer;

    return (...args)=>{

        clearTimeout(timer);

        timer = setTimeout(()=>{

            fn(...args);

        },delay);

    };

}

/*==========================================================
    THROTTLE
==========================================================*/

function throttle(fn, limit = 200){

    let waiting = false;

    return (...args)=>{

        if(waiting) return;

        fn(...args);

        waiting = true;

        setTimeout(()=>{

            waiting = false;

        },limit);

    };

}

/*==========================================================
    RIPPLE EFFECT
==========================================================*/

function initializeRipple(){

    const buttons = $$("button");

    buttons.forEach(button=>{

        button.addEventListener("click",function(e){

            const ripple = document.createElement("span");

            ripple.classList.add("ripple");

            const rect = this.getBoundingClientRect();

            ripple.style.left = `${e.clientX - rect.left}px`;

            ripple.style.top = `${e.clientY - rect.top}px`;

            this.appendChild(ripple);

            setTimeout(()=>{

                ripple.remove();

            },600);

        });

    });

}

/*==========================================================
    LOADING BUTTON
==========================================================*/

function setLoading(button,text="Cargando..."){

    if(!button) return;

    button.dataset.original = button.innerHTML;

    button.disabled = true;

    button.innerHTML = `
        <i class="fa-solid fa-spinner fa-spin"></i>
        <span>${text}</span>
    `;

}

function removeLoading(button){

    if(!button) return;

    button.disabled = false;

    if(button.dataset.original){

        button.innerHTML = button.dataset.original;

    }

}

/*==========================================================
    SCROLL REVEAL
==========================================================*/

function initializeReveal(){

    const elements = $$("[data-reveal]");

    if(!elements.length) return;

    const observer = new IntersectionObserver(entries=>{

        entries.forEach(entry=>{

            if(entry.isIntersecting){

                entry.target.classList.add("fade-up");

                observer.unobserve(entry.target);

            }

        });

    },{
        threshold:.15
    });

    elements.forEach(el=>observer.observe(el));

}

/*==========================================================
    ALERTAS
==========================================================*/

function showAlert(type,message){

    console.log(`[${type}] ${message}`);

    /*
        Aquí construiremos más adelante
        las alertas visuales del proyecto.
    */

}

/*==========================================================
    DETECTAR REDUCED MOTION
==========================================================*/

function prefersReducedMotion(){

    return window.matchMedia(
        "(prefers-reduced-motion: reduce)"
    ).matches;

}

/*==========================================================
    INICIALIZACIÓN
==========================================================*/

ready(()=>{

    initializeRipple();

    initializeReveal();

    console.log("%cBODYTECH UI SYSTEM",
        "color:#3B82F6;font-size:16px;font-weight:bold;"
    );

});