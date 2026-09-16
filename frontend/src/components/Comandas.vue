<script setup>
import { ref, onMounted, computed } from "vue";
import api from "@/api/axios";
import NavBar from "./NavBar.vue";
import Footer from "./Footer.vue";
import ValoracionForm from "./ValoracionForm.vue";
import { useAuth } from "@/composables/useAuth";
import { storageUrl, storageDefaultProductUrl } from "@/utils/storage";
import { useToast } from '@/composables/useToast';
import BaseButton from '@/components/ui/BaseButton.vue';

const comandas = ref([]);
const cargando = ref(true);
const { usuario, fetchUsuario } = useAuth();
const aValorar = ref(null);

const toast = useToast();

const obtenerComandas = async () => {
    try {
        const response = await api.get("/mis-comandas");
        comandas.value = response.data.datos;
    } catch (error) {
        console.error("Error al cargar:", error);
    } finally {
        cargando.value = false;
    }
};

const comandasPendientes = computed(() => {
    return comandas.value.filter(c => c.estado == 'pendiente' || c.estado == 'en curso');
});

const historialComandas = computed(() => {
    return comandas.value.filter(c => c.estado == 'completado' || c.estado == 'cancelado');
});

const actualizarComanda = async (id, nuevoEstado) => {
    try {
        await api.put(`/mis-comandas/${id}`, { estado: nuevoEstado });
        const comandaEncontrada = comandas.value.find(c => c.id === id);
        if (comandaEncontrada) comandaEncontrada.estado = nuevoEstado;
    } catch (err) {
        toast.error("Ha ocurrido un error al actualizar la comanda.");
        console.error(err);
    }
}

const abrirModalValoracion = (id) => {
    aValorar.value = id;
};

const getColoresEstado = (estado) => {
    const paleta = {
        'pendiente': '#ff7519',
        'en curso': '#3498db',
        'completado': '#4CA626',
        'cancelado': '#e74c3c'
    };
    return paleta[estado] || '#64748b';
};

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const [year, month, day] = fecha.split('-');
    return `${day}/${month}/${year}`;
};

const getNombreContraparte = (comanda) => {
    if (comanda.id_comprador === usuario.value.id) {
        return comanda.vendedor?.nombre_usuario || 'Vendedor';
    }
    return comanda.comprador?.nombre_usuario || 'Comprador';
};

const getUrlImagen = (rutaRelativa) => {
    return rutaRelativa ? storageUrl(rutaRelativa) : storageDefaultProductUrl();
};

const postValoracion = async (idCompraventa, datos) => {
    const token = localStorage.getItem('token');
    if (!token) return;
    try {
        await api.post(`/valoraciones/${idCompraventa}`, datos);
        const comandaValorada = comandas.value.find(c => c.id === idCompraventa);
        if (comandaValorada) comandaValorada.he_valorado = true;
        toast.success("Valoración realizada con éxito.");
        aValorar.value = null;
    } catch (err) {
        toast.error("Algo ha ido mal.");
        console.log(err);
    }
};

onMounted(async () => {
    await fetchUsuario();
    if (usuario.value?.id) obtenerComandas();
});
</script>

<template>
    <NavBar />
    <div class="contenedor-pagina">
        <div id="contenedor-titulo">
            <h1 class="titulo">Comandas</h1>
            <p class="subtitulo">Gestiona las solicitudes de compra de tus productos</p>
        </div>

        <div class="seccion-comandas">
            <div class="cabecera-seccion">
                <div class="titulo-grupo">
                    <img src="../assets/iconos/stock.png" alt="Icono" class="icono-seccion" />
                    <h3>Comandas en curso</h3>
                </div>
                <span class="contador-badge">{{ comandasPendientes.length }} pendientes</span>
            </div>

            <p v-if="cargando" class="texto-info">Cargando comandas...</p>
            <div v-if="!cargando && comandasPendientes.length === 0" class="sin-datos">
                No hay comandas pendientes
            </div>

            <div v-for="comanda in comandasPendientes" :key="comanda.id" class="tarjeta-comanda"
                :style="{ borderLeftColor: getColoresEstado(comanda.estado) }">

                <div class="info-principal">
                    <img :src="getUrlImagen(comanda.producto?.imagen)" class="img-producto" />
                    <div class="detalles">
                        <h3>{{ comanda.producto?.nombre_producto || "Producto" }}</h3>
                        <div class="fila-datos">
                            <span>Cant: {{ comanda.cantidad }}</span>
                            <span class="separador">•</span>
                            <span class="precio">{{ comanda.precio_total }}€</span>
                            <span class="separador">•</span>
                            <span class="usuario">{{ getNombreContraparte(comanda) }}</span>
                            <span class="separador">•</span>
                            <span class="fecha">{{ formatearFecha(comanda.fecha_prevista) }}</span>
                        </div>
                    </div>
                </div>

                <div class="acciones">
                    <BaseButton v-if="comanda.estado == 'en curso' && comanda.id_comprador !== usuario.id"
                            size="sm" @click="actualizarComanda(comanda.id, 'completado')">
                        Finalizar
                    </BaseButton>
                    <BaseButton v-else-if="comanda.id_comprador !== usuario.id"
                            size="sm" @click="actualizarComanda(comanda.id, 'en curso')">
                        Aceptar
                    </BaseButton>
                    <BaseButton variant="danger" size="sm" @click="actualizarComanda(comanda.id, 'cancelado')">
                        Rechazar
                    </BaseButton>
                </div>

                <div class="etiqueta-estado" :style="{ backgroundColor: getColoresEstado(comanda.estado) }">
                    {{ comanda.estado }}
                </div>
            </div>
        </div>

        <div class="seccion-comandas">
            <div class="cabecera-seccion">
                <div class="titulo-grupo">
                    <img src="../assets/iconos/aceptar.png" alt="Historial" class="icono-seccion">
                    <h3>Historial de comandas</h3>
                </div>
            </div>

            <div v-if="historialComandas.length === 0" class="sin-datos">
                No hay historial disponible.
            </div>

            <div v-for="item in historialComandas" :key="item.id" class="tarjeta-comanda"
                :style="{ borderLeftColor: getColoresEstado(item.estado) }">

                <div class="info-principal">
                    <img :src="getUrlImagen(item.producto?.imagen)" class="img-producto">
                    <div class="detalles">
                        <h3>{{ item.producto?.nombre_producto }}</h3>
                        <div class="fila-datos">
                            <span>Cant: {{ item.cantidad }}</span>
                            <span class="separador">•</span>
                            <span class="precio">{{ item.precio_total }}€</span>
                            <span class="separador">•</span>
                            <span class="usuario">{{ getNombreContraparte(item) }}</span>
                            <span class="separador">•</span>
                            <span class="fecha">{{ formatearFecha(item.fecha_prevista) }}</span>
                        </div>
                    </div>
                </div>

                <div class="acciones">
                    <BaseButton v-if="item.estado == 'completado' && !item.he_valorado" size="sm"
                        @click="abrirModalValoracion(item.id)">
                        Valorar
                    </BaseButton>
                </div>

                <div class="etiqueta-estado" :style="{ backgroundColor: getColoresEstado(item.estado) }">
                    {{ item.estado }}
                </div>
            </div>
        </div>

        <ValoracionForm v-if="aValorar" :id="aValorar" @enviar-valoracion="postValoracion(aValorar, $event)"
            @cerrar="aValorar = null" />

    </div>
    <Footer></Footer>
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", "Arial";
}

body {
    min-width: 400px;
}

.contenedor-pagina {
    margin-top: 80px;
    padding: 20px 50px;
}

#contenedor-titulo {
    max-width: 90%;
    margin: 10px auto 0 auto;
}
.titulo {
    font-family: sans-serif;
    color: #4CA626;
    margin-bottom: 10px;
    font-weight: bold;
}

.subtitulo {
    font-family: sans-serif;
    color: #666666;
    margin-bottom: 20px;
}

.seccion-comandas {
    margin-bottom: 50px;
    max-width: 90%;
    margin-left: auto;
    margin-right: auto;
}

.cabecera-seccion {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    color: #4CA626;
}

.titulo-grupo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.icono-seccion {
    width: 30px;
    height: 30px;
}

.contador-badge {
    background-color: #B9E2A6;
    color: #4CA626;
    padding: 6px 15px;
    border-radius: 8px;
    font-weight: bold;
}

.tarjeta-comanda {
    background: white;
    border: 1px solid #f0f0f0;
    border-left: 6px solid #ccc;
    border-radius: 12px;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    position: relative;
}

.info-principal {
    display: flex;
    align-items: center;
    gap: 15px;
}

.img-producto {
    width: 70px;
    height: 70px;
    border-radius: 10px;
    object-fit: cover;
}

.detalles h3 {
    margin: 0 0 5px 0;
    font-size: 1.1rem;
    color: #1e293b;
}

.fila-datos {
    display: flex;
    gap: 8px;
    font-size: 0.9rem;
    color: #64748b;
    align-items: center;
}

.precio {
    color: #4CA626;
    font-weight: 700;
}

.separador {
    color: #cbd5e1;
}

.acciones {
    display: flex;
    gap: 10px;
    margin-right: 120px;
}

.etiqueta-estado {
    position: absolute;
    right: 20px;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: bold;
    text-transform: uppercase;
}

.sin-datos {
    text-align: center;
    padding: 40px;
    background: #fcfcfc;
    border-radius: 12px;
    color: #999;
    border: 1px dashed #ddd;
}

@media (max-width: 900px) {
    .tarjeta-comanda {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    .acciones {
        margin-right: 0;
        width: 100%;
    }
    .etiqueta-estado {
        top: 15px;
    }
}

.historial {
    margin-top: 50px;
    max-width: 90%;
    margin: auto;
}

.titulo-historial {
    margin-top: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.titulo-historial h3 {
    margin: 0;
    font-size: 1.2rem;
    color: #333;
}

.icono-titulo {
    width: 25px;
    height: 25px;
}

.tarjeta-producto {
    background-color: white;
    border: 1px solid #e2e8f0;
    border-left: 6px solid #4CA626;
    border-radius: 8px;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.info-izquierda {
    display: flex;
    align-items: center;
    gap: 15px;
}

.img-producto {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
}

.detalles h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
}

.fila-datos {
    font-size: 14px;
    color: #64748b;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}

.separador {
    font-size: 10px;
    color: #cbd5e1;
}

.precio {
    color: #4CA626;
    font-weight: 600;
}

.fecha {
    color: #4CA626;
}

.etiqueta-estado {
    background-color: #0f172a;
    color: white;
    padding: 6px 16px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 700;
    text-transform: capitalize;
    white-space: nowrap;
}

@media (max-width: 768px) {
    .comanda-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .comanda-actions {
        flex-direction: column;
    }

    .empty-header-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .badge-pendientes-empty {
        margin-left: 0;
    }
}
</style>
