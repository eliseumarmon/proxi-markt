<script setup>
import L from 'leaflet'
import { ref, onMounted, nextTick } from "vue";
import api from "@/api/axios";
import { leafletPin } from "@/utils/leafletPin";
import NavBar from "./NavBar.vue";
import MostrarProductosMain from './MostrarProductosMain.vue';
import { useAuth } from '@/composables/useAuth';
import Footer from "./Footer.vue";

// Referencias para el mapa y Leaflet
let map = null;
let markersLayer = null;

// Referencia de Vue para el scroll seguro
const productosSeccion = ref(null);

// Estado reactivo
const puntosProductos = ref([]);
const productos = ref([]);
const productosKey = ref(0);
const mensajeEstado = ref("");
const radioSeleccionado = ref(10);
const { usuario, fetchUsuario } = useAuth();

/**
 * Selecciona un punto y carga sus productos
 */
const seleccionarPunto = async (idPunto) => {
    productos.value = [];
    mensajeEstado.value = "Cargando...";

    try {
        const response = await api.get(`/puntos/${idPunto}/productos`);

        if (response.data && response.data.status) {
            const dataServidor = response.data.productos || [];

            if (dataServidor.length > 0) {
                productos.value = [...dataServidor];
                productosKey.value++; // Forzamos refresco del hijo
                mensajeEstado.value = "";

                // Esperamos a que Vue monte el div ".productos" y asigne la ref
                await nextTick();

                if (productosSeccion.value) {
                    productosSeccion.value.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else {
                mensajeEstado.value = "No hay productos disponibles en este punto.";
            }
        }
    } catch (error) {
        console.error("Fallo en seleccionarPunto:", error);
        productos.value = [];
        if (error.response?.status === 404) {
            mensajeEstado.value = "Punto de entrega no encontrado.";
        } else {
            mensajeEstado.value = "Hubo un error al cargar los productos.";
        }
    }
};

/**
 * Dibuja los marcadores en el mapa
 */
const cargarMarcadores = () => {
    if (!map || !markersLayer) return;

    markersLayer.clearLayers();

    puntosProductos.value.forEach(punto => {
        if (!punto.latitud || !punto.longitud) return;

        const marker = L.marker([punto.latitud, punto.longitud], { icon: leafletPin }).addTo(markersLayer);

        marker.on('click', () => {
            seleccionarPunto(punto.id);
        });

        marker.bindTooltip(punto.nombre_punto);
    });
};

/**
 * Actualiza los puntos según el radio
 */
const actualizarPuntos = async (nuevoRadio) => {
    radioSeleccionado.value = nuevoRadio;

    if (!usuario.value?.latitud || !usuario.value?.longitud) return;

    const radio = nuevoRadio === Infinity ? 99999 : nuevoRadio;

    try {
        const puntosEntrega = await api.get(`/puntos_radio/${radio}`, {
            params: {
                lng: usuario.value.longitud,
                lat: usuario.value.latitud
            },
        });

        puntosProductos.value = Array.isArray(puntosEntrega.data) ? puntosEntrega.data : [];
        mensajeEstado.value = "";
        cargarMarcadores();
    } catch (error) {
        console.error("Error al actualizar puntos por radio:", error);
    }
};

/**
 * Inicializa Leaflet
 */
const inicializarMapa = async () => {
    await nextTick();

    const radioInicial = localStorage.getItem('distancia_guardada');

    try {
        const latUsuario = Number(usuario.value?.latitud);
        const lngUsuario = Number(usuario.value?.longitud);
        const centroInicial = (Number.isFinite(latUsuario) && Number.isFinite(lngUsuario))
            ? [latUsuario, lngUsuario]
            : [39.4699, -0.3763];

        if (!map) {
            map = L.map('map', {
                minZoom: 3,
                worldCopyJump: true,
                maxBounds: [[-90, -180], [90, 180]],
                maxBoundsViscosity: 1.0
            }).setView(centroInicial, 13);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                minZoom: 3,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            markersLayer = L.layerGroup().addTo(map);
            setTimeout(() => map.invalidateSize(), 100);
        }

        const radioFinal = radioInicial === "Infinity" ? Infinity : (Number(radioInicial) || 10);
        await actualizarPuntos(radioFinal);

    } catch (error) {
        console.error("Error en la inicialización del mapa:", error);
    }
}

onMounted(async () => {
    await fetchUsuario();
    if (usuario.value?.id) {
        inicializarMapa();
    }
});
</script>

<template>
    <NavBar @cambiar-radio="actualizarPuntos" />

    <div class="contenedor-pagina">
        <div id="contenedor-titulo">
            <h1 class="titulo">Mapa</h1>
            <p class="subtitulo">Visualiza los puntos de entrega y productos cercanos</p>
        </div>

        <div id="contenedor-mapa">
            <div id="map" class="leaflet-map"></div>
        </div>

        <div class="seccion-resultados">
            <div v-if="mensajeEstado === 'Cargando...'" class="mensaje-informativo">
                <p>Cargando productos...</p>
            </div>

            <div
                v-else-if="productos.length > 0"
                class="productos"
                :key="productosKey"
                ref="productosSeccion"
            >
                <div class="cabecera-productos">
                    <h2 class="titulo-seccion">Productos en este punto</h2>
                </div>
                <MostrarProductosMain :productos="productos" :usuario="usuario || {}" />
            </div>

            <div v-else-if="mensajeEstado" class="mensaje-informativo">
                <p>{{ mensajeEstado }}</p>
            </div>

            <div v-else-if="puntosProductos.length > 0" class="mensaje-ayuda">
                <p>Haz clic en un marcador del mapa para ver sus productos</p>
            </div>
        </div>
    </div>
    <Footer />
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", "Arial", sans-serif;
}

#map {
    height: 500px;
    width: 100%;
    border-radius: 12px;
    border: 2px solid #4ca626;
    z-index: 10;
}

.contenedor-pagina {
    margin-top: 80px;
    padding: 20px;
}

#contenedor-titulo {
    max-width: 1200px;
    margin: 10px auto 20px;
    padding: 0 15px;
}

.titulo {
    color: #4CA626;
    font-size: 2rem;
    font-weight: bold;
}

.subtitulo {
    color: #666;
}

#contenedor-mapa {
    max-width: 1200px;
    margin: 0 auto 30px;
    padding: 0 15px;
}

.seccion-resultados {
    max-width: 1200px;
    margin: 0 auto;
}

.productos {
    margin: 50px auto;
    padding: 30px 15px;
    border-top: 2px solid #f0f0f0;
    animation: fadeIn 0.5s ease-out;
}

.cabecera-productos {
    margin-bottom: 25px;
    border-left: 5px solid #4ca626;
    padding-left: 15px;
}

.mensaje-ayuda,
.mensaje-informativo {
    margin: 40px auto;
    text-align: center;
    color: #999;
    padding: 20px;
    background: #fdfdfd;
    border-radius: 8px;
}

.mensaje-informativo p {
    color: #4ca626;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    #map { height: 350px; }
}
</style>