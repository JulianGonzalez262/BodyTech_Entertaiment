/*
==========================================================
 BODYTECH UI SYSTEM
 Forms Component
==========================================================
*/

"use strict";

ready(() => {

    initializeFormValidation();

    initializeCapsLockDetection();

});
function initializeFormValidation(){

    const fields = $$(".input-field");

    fields.forEach(field=>{

        field.addEventListener("input",()=>{

            validateField(field);

        });

        field.addEventListener("blur",()=>{

            validateField(field);

        });

    });

}
function validateField(field){

    if(field.type==="email"){

        validateEmail(field);

        return;

    }

    validateRequired(field);

}
function validateEmail(field){

    const value = field.value.trim();

    if(value===""){

        clearValidation(field);

        return;

    }

    const emailRegex=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if(emailRegex.test(value)){

        showSuccess(field,"Correo válido.");

    }else{

        showError(field,"Correo inválido.");

    }

}
function validateRequired(field){

    const value=field.value.trim();

    if(value===""){

        clearValidation(field);

        return;

    }

    showSuccess(field,"Correcto.");

}
function showError(field,message){

    field.classList.remove("is-valid");

    field.classList.add("is-invalid");

    const helper=getHelper(field);

    helper.textContent=message;

    helper.className="input-message error";

}
function showSuccess(field,message){

    field.classList.remove("is-invalid");

    field.classList.add("is-valid");

    const helper=getHelper(field);

    helper.textContent=message;

    helper.className="input-message success";

}
function clearValidation(field){

    field.classList.remove("is-valid");

    field.classList.remove("is-invalid");

    const helper=getHelper(field);

    helper.textContent="";

    helper.className="input-message";

}
function getHelper(field){

    return field
        .closest(".input-group")
        .querySelector(".input-message");

}
function initializeCapsLockDetection(){

    const password=$("#password");

    if(!password) return;

    const warning=password
        .closest(".input-group")
        .querySelector(".caps-warning");

    password.addEventListener("keyup",(e)=>{

        if(e.getModifierState("CapsLock")){

            warning.classList.add("show");

        }else{

            warning.classList.remove("show");

        }

    });

}
function validateForm(form){

    const fields=form.querySelectorAll(".input-field");

    let valid=true;

    fields.forEach(field=>{

        validateField(field);

        if(field.classList.contains("is-invalid")){

            valid=false;

        }

    });

    return valid;

}
