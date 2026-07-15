<script setup>
import { ref } from "vue";
import api from "@/api/axios";
import { useAuth } from '@/composables/useAuth';
import { useRouter } from 'vue-router';

const emit = defineEmits(['registro-completado']);
const mostrar = ref({
    pass1: false,
    pass2: false
});

const ojoTachado = "M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88";
const ojoNormal = "M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z";

const toastVisible = ref(false);
const toastMensaje = ref("");

const lanzarToast = (mensaje) => {
    toastMensaje.value = mensaje;
    toastVisible.value = true;
    setTimeout(() => {
        toastVisible.value = false;
    }, 3000);
};

const form = ref({
    nombre_usuario: "",
    email: "",
    contrasenya: "",
    confirmar_contrasenya: "",
    telefono: ""
});

const { loading, setLoading } = useAuth();
const router = useRouter();

const enviarInfo = async () => {
    const { nombre_usuario, email, contrasenya, confirmar_contrasenya, telefono } = form.value;
    if (!nombre_usuario || !email || !contrasenya || !confirmar_contrasenya || !telefono) {
        lanzarToast("Rellena los campos obligatorios.");
        return;
    }
    
    if (contrasenya !== confirmar_contrasenya) {
        lanzarToast("Las contraseñas no coinciden.");
        return;
    }

    if (contrasenya.length < 6) {
        lanzarToast("La contraseña debe tener al menos 6 caracteres.");
        return;
    }

    setLoading(true);

    try {
        const datosRegistro = {
            nombre_usuario,
            email,
            contrasenya,
            telefono
        };

        await api.post("/register", datosRegistro);

        form.value = {
            nombre_usuario: "",
            email: "",
            contrasenya: "",
            confirmar_contrasenya: "",
            telefono: ""
        };

        lanzarToast("¡Cuenta creada con éxito! Ahora puedes iniciar sesión.");
        setTimeout(() => {
            router.push('/auth');
        }, 2000);

    } catch (error) {
        lanzarToast("Hubo un error al conectar con el servidor");
        console.error("Error en la petición:", error);
    } finally {
        setLoading(false);
    }
}
</script>

<template>
    <div class="form-card">
        <h3>Crear Cuenta</h3>
        <p class="subtitle">Únete a la comunidad de ProxiMarkt</p>

        <form @submit.prevent="enviarInfo">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input v-model="form.nombre_usuario" type="text" id="nombre" placeholder="Juan Garcia" />
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input v-model="form.email" type="email" id="email" placeholder="tu@gmail.com" />
            </div>

            <div class="row-passwords">
                <div class="form-group">
                    <label for="contrasenya">Contraseña</label>
                    <div class="password-wrapper">
                        <input 
                            v-model="form.contrasenya" 
                            :type="mostrar.pass1 ? 'text' : 'password'" 
                            id="contrasenya" 
                            placeholder="••••••••" 
                        />
                        <span class="icono-ojo" @click="mostrar.pass1 = !mostrar.pass1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="mostrar.pass1 ? ojoTachado : ojoNormal" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmar_contrasenya">Confirmar Contraseña</label>
                    <div class="password-wrapper">
                        <input v-model="form.confirmar_contrasenya" :type="mostrar.pass2 ? 'text' : 'password'" id="confirmar_contrasenya" placeholder="••••••••"/>
                        <span class="icono-ojo" @click="mostrar.pass2 = !mostrar.pass2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="mostrar.pass2 ? ojoTachado : ojoNormal" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input v-model="form.telefono" type="tel" id="telefono" placeholder="123456789" />
            </div>

            <button type="submit" class="boton-submit" :disabled="loading">
                <span v-if="!loading">Crear Cuenta</span>
                <span v-else>Procesando...</span>
            </button>
        </form>
        <div v-if="toastVisible" class="toast-notificacion">
            {{ toastMensaje }}
        </div>
    </div>
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-width: 400px;
}

.register-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    width: 100%;
    padding: 20px;
}

.form-card {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    padding: 35px 30px;
    text-align: left;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    width: 100%;
}

h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin-top: 0;
    margin-bottom: 8px;
    color: #111827;
}

.subtitle {
    color: #6B7280;
    font-size: 0.95rem;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 8px;
    color: #1F2937;
}

input {
    width: 100%;
    padding: 12px 15px;
    background-color: #F3F4F6;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    font-size: 0.95rem;
    color: #333333;
    outline: none;
    transition: all 0.2s;
}

input:focus {
    background-color: #FFFFFF;
    border-color: #D1D5DB;
    box-shadow: 0 0 0 3px rgba(0, 176, 80, 0.1);
}

input::placeholder {
    color: #9CA3AF;
}

.password-wrapper {
    position: relative;
    width: 100%;
}

.password-wrapper input {
    padding-right: 40px;
}

.icono-ojo {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #9CA3AF;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icono-ojo:hover {
    color: #4B5563;
}

.icono-ojo svg {
    width: 20px;
    height: 20px;
}

.boton-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
    color: #FFF;
    font-weight: bold;
    font-size: 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
    transition: background-color 0.2s;
}

.boton-submit:hover {
    background: linear-gradient(90deg, #008F4C 0%, rgb(1, 104, 59) 100%);
}

.boton-submit:disabled {
    background: #cccccc;
    cursor: not-allowed;
    box-shadow: none;
    opacity: 0.7;
    color: #888888
}

.boton-submit:disabled:hover {
    background: #cccccc;
}

.toast-notificacion {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #333;
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    z-index: 99999;
    animation: subida 0.3s ease-out;
}

.row-passwords {
    display: flex;
    gap: 15px;
}
.row-passwords .form-group {
    flex: 1;
}

@keyframes subida {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0); opacity: 1; }
}

@media (min-width: 1200px) {
    .form-card {
        max-width: 500px;
    }
}

@media (max-width: 768px) {
    .register-container {
        align-items: flex-start;
        padding-top: 40px;
    }

    .form-card {
        width: 100%;
        max-width: none;
        padding: 25px 20px;
    }

    h3 {
        font-size: 1.2rem;
    }
    
    .row-passwords {
        flex-direction: column;
        gap: 0;
    }
}
</style>