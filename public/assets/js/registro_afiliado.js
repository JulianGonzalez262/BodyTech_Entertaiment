document.querySelector('input[name="fecha_nacimiento"]').addEventListener('change', function () {
    const birth = new Date(this.value);
    const ageDif = Date.now() - birth.getTime();
    const age = new Date(ageDif).getUTCFullYear() - 1970;
    if (age < 18) {
        alert('Debes ser mayor de edad para afiliarte.');
        this.value = '';
    }
});

const password = document.getElementById("password");
const toggle = document.getElementById("togglePassword");
const icon = toggle.querySelector("i");

toggle.addEventListener("click", () => {

    const visible = password.type === "text";

    password.type = visible ? "password" : "text";

    icon.classList.toggle("fa-eye");
    icon.classList.toggle("fa-eye-slash");

});