<script setup>
import L from 'leaflet'
import { ref, onMounted, nextTick } from "vue";
import api from "@/api/axios";
import NavBar from "./NavBar.vue";
import MostrarProductosMain from './MostrarProductosMain.vue';
import { useAuth } from '@/composables/useAuth';
import Footer from "./Footer.vue";

let map = null;
let markersLayer = null;
const puntosProductos = ref([]);
const productos = ref([]);
const productosKey = ref(0);
const mensajeEstado = ref("");
const radioSeleccionado = ref(10);
const { usuario, fetchUsuario } = useAuth();

const seleccionarPunto = async (idPunto) => {

    productos.value = [];
    mensajeEstado.value = "Cargando...";

    try {
        const response = await api.get(`/puntos/${idPunto}/productos`);

        if (response.data.status && response.data.productos) {
            const data = response.data.productos;

            // Forzamos la reactividad: vaciamos, esperamos un tick y llenamos
            await nextTick();
            productos.value = [...data];
            mensajeEstado.value = "";

            // Scroll suave
            setTimeout(() => {
                const el = document.querySelector('.productos');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    } catch (error) {
        if (error.response && error.response.status === 404) {
            mensajeEstado.value = error.response.data.message || "No hay productos disponibles en este punto.";
        } else {
            mensajeEstado.value = "Hubo un error al cargar los productos.";
            console.error("Error en la petición de productos:", error);
        }
        productos.value = [];
    }
};

// Carga marcadores en el mapa
const cargarMarcadores = () => {
    if (!map || !markersLayer) return;

    markersLayer.clearLayers();

    puntosProductos.value.forEach(punto => {
        // Aseguramos que las coordenadas existan
        if (!punto.latitud || !punto.longitud) return;

        const marker = L.marker([punto.latitud, punto.longitud]).addTo(markersLayer);

        // Al hacer clic, llamamos a la función externa pasando el ID
        marker.on('click', () => {
            seleccionarPunto(punto.id);
        });

        marker.bindTooltip(punto.nombre_punto);
    });
};

const actualizarPuntos = async (nuevoRadio) => {
    // Sincronizamos el radio seleccionado para pasarlo al hijo
    radioSeleccionado.value = nuevoRadio;

    if (!usuario.value?.latitud || !usuario.value?.longitud) return;

    // Para la API usamos un número grande si es Infinity
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

const inicializarMapa = async () => {
    await nextTick();

    const radioInicial = localStorage.getItem('distancia_guardada');

    try {
        const { longitud: lng, latitud: lat } = usuario.value;

        if (!map) {
            const limitesVerticales = [
                [-90, -180],
                [90, 180]
            ];

            map = L.map('map', {
                minZoom: 3,
                worldCopyJump: true,
                maxBounds: limitesVerticales,
                maxBoundsViscosity: 1.0
            }).setView([lat, lng], 13);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                minZoom: 3,
                noWrap: false,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            markersLayer = L.layerGroup().addTo(map);
        }

        const radioFinal = radioInicial === "Infinity" ? Infinity : (Number(radioInicial) || 10);
        await actualizarPuntos(radioFinal);

    } catch (error) {
        console.error("Error en la inicialización del mapa:", error);
    }
}

onMounted(async () => {
    await fetchUsuario();
    if (usuario.value?.id) inicializarMapa();
});
</script>

<template>
    <!-- Escuchamos el evento que emite el Navbar -->
    <NavBar @cambiar-radio="actualizarPuntos" />

    <div class="contenedor-pagina">
        <div id="contenedor-titulo">
            <h1 class="titulo">Mapa</h1>
            <p class="subtitulo">
                Visualiza los puntos de entrega y productos cercanos
            </p>
        </div>

        <div id="contenedor-mapa">
            <div id="map" class="leaflet-map"></div>
        </div>

        <!-- Sección de resultados -->
        <div class="seccion-resultados">
            <!-- Caso: Cargando -->
            <div v-if="mensajeEstado === 'Cargando...'" class="mensaje-informativo">
                <p>Cargando productos...</p>
            </div>

            <!-- Caso: Éxito (Hay productos) -->
            <div class="productos" v-else-if="productos.length > 0" :key="productosKey">
                <div class="cabecera-productos">
                    <h2 class="titulo-seccion">Productos en este punto</h2>
                </div>
                <!-- Pasamos el radioSeleccionado para que el componente hijo no filtre productos válidos -->
                <MostrarProductosMain :productos="productos" :usuario="usuario" />
            </div>

            <!-- Caso: Error o Mensaje de Vacío -->
            <div v-else-if="mensajeEstado" class="mensaje-informativo">
                <p>{{ mensajeEstado }}</p>
            </div>

            <!-- Caso: Estado inicial -->
            <div v-else-if="puntosProductos.length > 0" class="mensaje-ayuda">
                <p>Haz clic en un marcador del mapa para ver sus productos</p>
            </div>
        </div>
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

#map {
    height: 500px;
    width: 100%;
    border-radius: 12px;
    border: 2px solid #4ca626;
    z-index: 10;
}

.contenedor-pagina {
    margin-top: 80px;
    padding: 20px 20px;
}

#contenedor-titulo {
    max-width: 1200px;
    margin: 10px auto 20px auto;
    padding: 0 15px;
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

#contenedor-mapa {
    max-width: 1200px;
    margin: 0 auto 30px auto;
    padding: 0 15px;
}

.seccion-resultados {
    max-width: 1200px;
    margin: 0 auto;
    min-height: 100px;
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

.titulo-seccion {
    color: #333;
    font-size: 1.5rem;
    font-weight: 600;
}

.mensaje-ayuda,
.mensaje-informativo {
    margin: 40px auto;
    text-align: center;
    color: #999;
    font-style: italic;
    font-size: 1.1rem;
    padding: 20px;
    background: #fdfdfd;
    border-radius: 8px;
}

.mensaje-informativo p {
    color: #4ca626;
    font-weight: 500;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .contenedor-pagina {
        padding: 10px;
    }

    #map {
        height: 350px;
    }
}
</style>