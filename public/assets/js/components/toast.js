"use strict";

function showToast(type, title, message, duration = 4000){

    let container = document.querySelector(".toast-container");

    if(!container){

        container = document.createElement("div");

        container.className = "toast-container";

        document.body.appendChild(container);

    }

    const icons = {

        success:"fa-circle-check",

        error:"fa-circle-xmark",

        warning:"fa-triangle-exclamation",

        info:"fa-circle-info"

    };

    const toast = document.createElement("div");

    toast.className = `toast ${type}`;

    toast.innerHTML = `
        <i class="fa-solid ${icons[type] || icons.info}"></i>

        <div class="toast-content">

            <div class="toast-title">${title}</div>

            <div class="toast-message">${message}</div>

        </div>

        <button class="toast-close">

            <i class="fa-solid fa-xmark"></i>

        </button>

        <div class="toast-progress"
             style="animation-duration:${duration}ms"></div>
    `;

    container.appendChild(toast);

    const removeToast = ()=>{

        toast.classList.add("hide");

        setTimeout(()=>toast.remove(),350);

    };

    toast.querySelector(".toast-close")
         .addEventListener("click",removeToast);

    setTimeout(removeToast,duration);

}