<script setup>
import api from '@/api/axios';
import NavBar from "./NavBar.vue";
import Footer from "./Footer.vue";
import { onMounted, ref } from 'vue';
const toastVisible = ref(false);
const toastMensaje = ref('');
const incidencias = ref([]);

const lanzarToast = (mensaje) => {
    toastMensaje.value = mensaje;
    toastVisible.value = true;
    setTimeout(() => {
        toastVisible.value = false;
    }, 3000);
};

const cargarincidencias = async () => {
    try {
        const respuesta = await api.get('/incidencias');
        incidencias.value = respuesta.data.data ?? [];
    } catch (error) {
        lanzarToast(error.response?.data?.message || 'Error al cargar incidencias');
    }
};

const actualizarincidencia = async (incidencia) => {
    try {
        const respuesta = await api.put(`/incidencias/${incidencia.id}`, {
            estado: incidencia.estado
        });
        if (respuesta.status === 200) {
            lanzarToast('Incidencia revisada correctamente');
        }
    } catch (error) {
        lanzarToast(error.response?.data?.message || 'Error al actualizar incidencias');
    }
};

onMounted(async () => {
    await cargarincidencias();
});
</script>

<template>
    <NavBar />

    <main class="pagina-incidencias-admin">
        <section class="card-incidencias">
            <div class="cabecera">
                <h1 class="titulo">Listado de incidencias</h1>
            </div>

            <div class="tabla-wrapper">
                <table class="tabla-incidencias">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Incidencia</th>
                            <th>Estado</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="incidencia in incidencias" :key="incidencia.id">
                            <td>{{ incidencia.usuario?.nombre_usuario || 'Usuario' }}</td>
                            <td class="mensaje">{{ incidencia.mensaje }}</td>
                            <td v-if="incidencia.estado === 'en revision'">
                                <select v-model="incidencia.estado" class="selector-estado">
                                    <option value="en revision">En revision</option>
                                    <option value="aceptada">Aceptada</option>
                                    <option value="rechazada">Rechazada</option>
                                </select>
                            </td>
                            <td v-else>
                                    <span class="badge-estado">
                                        {{ incidencia.estado }}
                                    </span>
                            </td>
                            <td>
                                <button class="boton-guardar" @click="actualizarincidencia(incidencia)">Guardar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div v-if="toastVisible" class="toast-notificacion">{{ toastMensaje }}</div>

    <Footer />
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", "Arial";
}

.pagina-incidencias-admin {
    margin-top: 80px;
    min-height: calc(100vh - 160px);
    background: linear-gradient(180deg, #F3FAF6 0%, #FFFFFF 100%);
    padding: 28px 20px 50px;
}

.card-incidencias {
    max-width: 1200px;
    margin: 0 auto;
    background-color: #FFFFFF;
    border: 1px solid #EAEAEA;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    padding: 24px;
}

.cabecera {
    margin-bottom: 20px;
}

.titulo {
    font-size: 30px;
    color: #1A202C;
    margin-bottom: 8px;
}

.subtitulo {
    color: #64748B;
    font-size: 15px;
}

.texto-info {
    color: #64748B;
    padding: 16px 0;
}

.tabla-wrapper {
    overflow-x: auto;
}

.tabla-incidencias {
    width: 100%;
    border-collapse: collapse;
    min-width: 820px;
}

.tabla-incidencias th,
.tabla-incidencias td {
    border-bottom: 1px solid #EDF2F7;
    padding: 12px 10px;
    text-align: left;
    vertical-align: top;
}

.tabla-incidencias th {
    color: #334155;
    font-size: 14px;
    font-weight: 700;
}

.tabla-incidencias td {
    color: #1F2937;
    font-size: 14px;
}

.mensaje {
    min-width: 280px;
    white-space: pre-wrap;
}

.badge-estado {
    display: inline-block;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 700;
    text-transform: capitalize;
}

.acciones {
    display: flex;
    gap: 8px;
    align-items: center;
}

.selector-estado {
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    padding: 8px;
    font-size: 14px;
    background-color: #FFFFFF;
}

.boton-guardar {
    border: none;
    border-radius: 8px;
    background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
    color: #FFFFFF;
    font-size: 13px;
    font-weight: 700;
    padding: 8px 12px;
    cursor: pointer;
}

.boton-guardar:hover {
    opacity: 0.95;
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
    .pagina-incidencias-admin {
        padding: 20px 12px 35px;
    }

    .card-incidencias {
        padding: 16px;
        border-radius: 12px;
    }

    .titulo {
        font-size: 24px;
    }
}
</style>
