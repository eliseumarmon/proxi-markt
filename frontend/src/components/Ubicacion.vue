<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import api from '@/api/axios';
import L from 'leaflet';
import { leafletPin } from '@/utils/leafletPin';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

const router = useRouter()

const { usuario, fetchUsuario, setLoading, loading } = useAuth();
const latitud = ref(null);
const longitud = ref(null);
const direccion = ref('');
const cargando = ref(false);

const toastVisible = ref(false);
const toastMensaje = ref("");

const lanzarToast = (mensaje) => {
    toastMensaje.value = mensaje;
    toastVisible.value = true;
    setTimeout(() => {
        toastVisible.value = false;
    }, 3000);
};

let map = null;
let markerseleccion = null;

const inicializarMapa = async () => {

    if (usuario.value.latitud && usuario.value.longitud) {
        latitud.value = usuario.value.latitud;
        longitud.value = usuario.value.longitud;
        direccion.value = usuario.value.direccion;
    }

    const centroInicial = (latitud.value && longitud.value)
        ? [latitud.value, longitud.value]
        : [39.032719, -0.215864];

    await nextTick();

    const limitesVerticales = [
        [-89.9, -180],
        [89.9, 180]
    ];

    map = L.map('map', {
        minZoom: 3,
        worldCopyJump: true,
        maxBounds: limitesVerticales,
        maxBoundsViscosity: 1.0
    }).setView(centroInicial, 13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        minZoom: 3,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    markerseleccion = L.marker(centroInicial, { icon: leafletPin }).addTo(map);

    map.on('click', async (e) => {
        const { lat, lng } = e.latlng;
        latitud.value = lat;
        longitud.value = lng;

        // 1. Mover el marcador a la nueva posición
        if (markerseleccion) {
            markerseleccion.setLatLng([lat, lng]);
        } else {
            markerseleccion = L.marker([lat, lng], { icon: leafletPin }).addTo(map);
        }

        // 2. Centrar ligeramente el mapa (opcional)
        map.panTo([lat, lng]);

        // 3. Obtener la dirección automáticamente al pinchar
        try {
            const response = await axios.get("https://nominatim.openstreetmap.org/reverse", {
                params: {
                    format: 'jsonv2',
                    lat: lat,
                    lon: lng
                }
            });

            if (response.data.address) {
                const address = response.data.address;
                direccion.value = [
                    address.road,
                    address.city || address.town || address.village,
                    address.postcode,
                    address.country
                ].filter(Boolean).join(", ");
            }
        } catch (error) {
            lanzarToast("La dirección no es válida");
            console.error("Error al obtener dirección:", error);
        }
    });
};
const guardarUbicacion = async () => {
    setLoading(true);

    const datos = {
        direccion: direccion.value,
        latitud: latitud.value,
        longitud: longitud.value
    };

    try {
        await api.put(`usuarios/${usuario.value.id}/ubicacion`, datos);

        // actualitzar l'objecte usuari manualment per no fer una nova petició a la base de dates
        usuario.value = {
            ...usuario.value,
            ...datos
        };
        lanzarToast("Dirección actualizada correctamente.");
        // console.log("Respuesta:", respuesta.data);
        setTimeout(() => {
            router.push('/cuenta');
        }, 2000);
    } catch (error) {
        lanzarToast("Hubo un error al guardar los datos.");
        console.error("Error al guardar:", error.response?.data);

    } finally {
        setLoading(false);
    }
};

const cancelar = () => {
    if (direccion.value && direccion.value !== usuario.value.direccion) {
        if (confirm("Tienes cambios sin guardar. ¿Quieres salir?")) {
            router.push('/cuenta');
        }
    } else {
        router.push('/cuenta');
    }
}

const obtenerUbicacionActual = () => {
    if (!navigator.geolocation) {
        lanzarToast("Tu navegador no soporta geolocalización");
        return;
    }

    navigator.geolocation.getCurrentPosition(async (pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;

        latitud.value = lat;
        longitud.value = lng;

        if (map && markerseleccion) {
            const nuevaPos = [lat, lng];
            map.setView(nuevaPos, 16);
            markerseleccion.setLatLng(nuevaPos);
        }

        try {
            const response = await axios.get("https://nominatim.openstreetmap.org/reverse", {
                params: {
                    format: 'jsonv2',
                    lat: lat,
                    lon: lng
                }
            });

            const address = response.data.address;
            direccion.value = [
                address.road,
                address.city || address.town || address.village,
                address.postcode,
                address.country
            ].filter(Boolean).join(", ");

            markerseleccion.bindPopup("Ubicación actual detectada").openPopup();

        } catch (error) {
            console.error("Error en reverse geocoding:", error);
            direccion.value = "Ubicación detectada (dirección no encontrada)";
        }
    }, (error) => {
        let mensaje = "No se pudo obtener tu ubicación.";
        if (error.code === 1) mensaje = "Por favor, permite el acceso a la ubicación en tu navegador.";
        lanzarToast(mensaje);
    }, {
        enableHighAccuracy: true,
        timeout: 10000
    });
};

onMounted(async () => {
    await fetchUsuario();
    if (usuario.value?.id) inicializarMapa();
});
</script>

<template>
    <div class="main-container">
        <div class="card">
            <h2 class="title">Configuración de Ubicación</h2>

            <div class="map-section">
                <div id="map" class="leaflet-map"></div>
            </div>

            <div class="status-section">
                <div v-if="direccion" class="info-box animate-in">
                    <p class="label">Dirección seleccionada:</p>
                    <p class="address-text">{{ direccion }}</p>

                    <div class="button-group">
                        <button @click="cancelar" :disabled="cargando" class="boton-secondary">
                            Cancelar
                        </button>

                        <button @click="obtenerUbicacionActual" type="button" class="boton-geo-inline"
                            :disabled="cargando">
                            📍 Mi ubicación
                        </button>

                        <button @click="guardarUbicacion" :disabled="cargando || !latitud" class="boton-primary">
                            <span v-if="!cargando">Guardar</span>
                            <span v-else>...</span>
                        </button>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <p>Haz clic en el mapa o usa el botón:</p>
                    <button @click="obtenerUbicacionActual" type="button" class="btn-geo-grande">
                        📍 Detectar mi ubicación actual
                    </button>
                </div>
            </div>
        </div>
        <div v-if="toastVisible" class="toast-notificacion">
            {{ toastMensaje }}
        </div>
    </div>
</template>

<style scoped>
.main-container {
    min-height: 100vh;
    background-color: #F0F0F0;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.card {
    background-color: #FFFFFF;
    width: 100%;
    max-width: 700px;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    padding: 24px;
}

.title {
    color: #4CA626;
    margin-bottom: 20px;
    font-weight: 700;
    text-align: center;
}

.leaflet-map {
    height: 350px;
    width: 100%;
    border-radius: 12px;
    border: 2px solid #F0F0F0;
}

.status-section {
    margin-top: 20px;
}

.info-box {
    background-color: #f9fdf7;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e2f0da;
}

.label {
    color: #2c5d18;
    font-size: 0.75rem;
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.address-text {
    color: #333333;
    font-weight: 500;
    margin-bottom: 15px;
    line-height: 1.4;
}

.button-group {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.boton-primary,
.boton-secondary,
.boton-geo-inline {
    flex: 1;
    padding: 12px 5px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 110px;
}

.boton-secondary {
    background-color: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.boton-secondary:hover:not(:disabled) {
    background-color: #fca5a5;
    color: #ffffff;
}

.boton-geo-inline {
    background-color: #FFFFFF;
    color: #4CA626;
    border: 1px solid #4CA626;
}

.boton-geo-inline:hover:not(:disabled) {
    background-color: #f0f7ed;
}

.boton-primary {
    background-color: #4CA626;
    color: white;
}

.boton-primary:hover:not(:disabled) {
    background-color: #3d851e;
}

.boton-primary:disabled,
.boton-secondary:disabled,
.boton-geo-inline:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.empty-state {
    text-align: center;
    color: #8BD16A;
    font-style: italic;
    padding: 20px;
}

.btn-geo-grande {
    margin-top: 15px;
    background-color: #4CA626;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.3s;
}

.btn-geo-grande:hover {
    background-color: #3d851e;
}

.animate-in {
    animation: fadeIn 0.4s ease-out;
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

@keyframes subida {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0); opacity: 1; }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
