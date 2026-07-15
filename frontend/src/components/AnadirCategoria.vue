<script setup>
import api from '@/api/axios';
import { ref } from 'vue';
import NavBar from './NavBar.vue';
import Footer from "./Footer.vue";

const nombrecategoria = ref('');

const toastVisible = ref(false);
const toastMensaje = ref('');

const lanzarToast = (mensaje) => {
    toastMensaje.value = mensaje;
    toastVisible.value = true;
    setTimeout(() => {
        toastVisible.value = false;
    }, 3000);
};

const nuevacategoria = async () => {
    try {
        const respuesta = await api.post('/categorias', {
            nombre_categoria: nombrecategoria.value
        });
        if (respuesta.status === 201) {
            lanzarToast('Categoria insertada correctamente');
            nombrecategoria.value = '';
        }
    } catch (error) {
        lanzarToast(error.response?.data?.message || 'No se pudo insertar la categoria');
    }
};
</script>

<template>
    <NavBar />

    <main class="pagina-categoria">
        <section class="card-categoria">
            <h1 class="titulo">Anadir categoria</h1>
            <p class="subtitulo">Crea una nueva categoria para clasificar productos.</p>

            <form class="form-categoria" @submit.prevent="nuevacategoria">
                <label for="categoria" class="label-campo">Nombre de la nueva categoria</label>
                <input
                    id="categoria"
                    v-model="nombrecategoria"
                    type="text"
                    class="input-categoria"
                    placeholder="Ejemplo: Verduras de temporada"
                    required
                />

                <button type="submit" class="boton-enviar">Guardar categoria</button>
            </form>
        </section>
    </main>

    <div v-if="toastVisible" class="toast-notificacion">
        {{ toastMensaje }}
    </div>

    <Footer />
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", "Arial";
}

.pagina-categoria {
    min-height: calc(100vh - 160px);
    margin-top: 80px;
    padding: 30px 20px 50px;
    background: linear-gradient(180deg, #F3FAF6 0%, #FFFFFF 100%);
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.card-categoria {
    width: 100%;
    max-width: 760px;
    background-color: #FFFFFF;
    border: 1px solid #EAEAEA;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    padding: 28px;
}

.titulo {
    font-size: 30px;
    color: #1A202C;
    margin-bottom: 8px;
}

.subtitulo {
    font-size: 15px;
    color: #64748B;
    margin-bottom: 24px;
    line-height: 1.45;
}

.form-categoria {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.label-campo {
    font-weight: 600;
    color: #334155;
    font-size: 14px;
}

.input-categoria {
    min-height: 44px;
    border: 1px solid #CFE2D4;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 15px;
    color: #1E293B;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.input-categoria:focus {
    border-color: #4CA626;
    box-shadow: 0 0 0 3px rgba(76, 166, 38, 0.15);
}

.boton-enviar {
    margin-top: 8px;
    align-self: flex-end;
    border: none;
    border-radius: 10px;
    background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
    color: #FFFFFF;
    font-weight: 700;
    font-size: 15px;
    padding: 11px 18px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.boton-enviar:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(76, 166, 38, 0.25);
}

.toast-notificacion {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #1F2937;
    color: #FFFFFF;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 14px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
    z-index: 2000;
}

@media (max-width: 767px) {
    .pagina-categoria {
        padding: 22px 12px 36px;
    }

    .card-categoria {
        padding: 20px 16px;
        border-radius: 12px;
    }

    .titulo {
        font-size: 24px;
    }

    .boton-enviar {
        width: 100%;
    }
}
</style>
