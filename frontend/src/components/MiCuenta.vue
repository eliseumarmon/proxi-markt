<script setup>
import L from 'leaflet'
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
import { useAuth } from '@/composables/useAuth.js';
import axios from 'axios';
import api from '@/api/axios';
import NavBar from './NavBar.vue'
import MostrarProductos from './MostrarProductos.vue'
import MisVentas from './MisVentas.vue';
import MisCompras from './MisCompras.vue';
import ValoracionView from './ValoracionView.vue';
import Footer from './Footer.vue';
import ValoracionEstrellas from './ValoracionEstrellas.vue';

let map = null;
let layerPuntos = null;
let marcadorTemporal = null;
const activarMapa = ref(false)
const direccion = ref('');
const latitud = ref(null)
const longitud = ref(null)
const nombrePunto = ref('')
const puntosEntrega = ref([])
const { usuario, fetchUsuario } = useAuth();
const productosUser = ref([])
const eleccionActual = ref('productos');
const pagination = ref({});
const paginaActual = ref(1);

const toastVisible = ref(false);
const toastMensaje = ref("");

const lanzarToast = (mensaje) => {
    toastMensaje.value = mensaje;
    toastVisible.value = true;
    setTimeout(() => {
        toastVisible.value = false;
    }, 3000);
};

// console.log(puntosEntrega)

const guardarPuntoEntrega = async () => {
    activarMapa.value = true;

    const southWest = L.latLng(-89.9, -180);
    const northEast = L.latLng(89.9, 180);
    const bounds = L.latLngBounds(southWest, northEast);


    await nextTick();

    const centroInicial = (usuario.value?.latitud && usuario.value?.longitud)
        ? [usuario.value?.latitud, usuario.value?.longitud]
        : [39.032719, -0.215864];

    if (!map) {
        map = L.map('map', {
            minZoom: 3,
            worldCopyJump: true,
            maxBounds: bounds,
            maxBoundsViscosity: 1.0
        }).setView(centroInicial, 8);


        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            minZoom: 3,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        layerPuntos = L.layerGroup().addTo(map);
        marcadorTemporal = L.marker(centroInicial, { opacity: 0 }).addTo(map);
        map.on('click', onMapClick);
    }

    direccion.value = "";
    nombrePunto.value = "";
    longitud.value = null;
    latitud.value = null;

    cargarMarcadores();
}

const cargarMarcadores = () => {
    if (!layerPuntos) return;

    layerPuntos.clearLayers();

    puntosEntrega.value.forEach(punto => {
        const lat = parseFloat(punto.latitud);
        const lng = parseFloat(punto.longitud)
        L.marker([lat, lng])
            .addTo(layerPuntos)
            .bindPopup(punto.nombre_punto);
    });
}

async function onMapClick(e) {
    latitud.value = e.latlng.lat;
    longitud.value = e.latlng.lng;

    try {
        const response = await axios.get("https://nominatim.openstreetmap.org/reverse", {
            params: {
                format: 'jsonv2',
                lat: latitud.value,
                lon: longitud.value
            }
        });

        const address = response.data.address;

        direccion.value = [
            address.road,
            address.city || address.town || address.village,
            address.postcode,
            address.country
        ]

        direccion.value = direccion.value.filter(Boolean).join(", ");

        // console.log("Valor de direccion:", `"${direccion.value}"`);
        if (marcadorTemporal) {
            marcadorTemporal
                .setLatLng(e.latlng)
                .setOpacity(1)
                .bindPopup(direccion.value)
                .openPopup();
        }

    } catch (error) {
        lanzarToast("Punto no válido");
        console.error(error.message);
    }
}

const crearPunto = async () => {
    const datos = {
        latitud: latitud.value,
        longitud: longitud.value,
        nombre_punto: nombrePunto.value,
        direccion_punto: direccion.value
    }
    try {
        const response = await api.post('/puntos', datos);

        const nuevoPunto = {
            id: response.data.id,
            nombre_punto: nombrePunto.value,
            direccion_punto: direccion.value,
            latitud: latitud.value,
            longitud: longitud.value
        };

        lanzarToast("Punto creado correctamente.");
        puntosEntrega.value.push(nuevoPunto);
    } catch (error) {
        console.error("Error del servidor:", error.response ? error.response.data : error.message);
        lanzarToast("Fallo al crear punto de entrega.");
    }
}

const cargarPuntos = async () => {
    const resposta = await api.get(`usuarios/${usuario.value.id}/puntos`);
    puntosEntrega.value = resposta.data;
}

const esconderMapa = () => {
    if (map) {
        map.remove();
        map = null;
    }
    activarMapa.value = false;
};

const eliminarPunto = async (id) => {
    if (!confirm('¿Estás seguro de que quieres eliminar este punto de entrega?')) return;

    try {
        await api.delete(`/puntos/${id}`);
        lanzarToast("Punto eliminado correctamente.");
        puntosEntrega.value = puntosEntrega.value.filter(p => p.id !== id);
        if (activarMapa.value && map) {
            cargarMarcadores();
        }
    } catch (error) {
        lanzarToast("No se ha podido eliminar el punto de entrega.");
        console.error("Error al eliminar:", error);
    }
}

const cargarProductosUser = async (pagina = 1) => {
    const productos = await api.get(`/usuarios/${usuario.value.id}/productos`, {
        params: {
            page: pagina
        }
    });

    productosUser.value = productos.data.data;
    pagination.value = productos.data;
    paginaActual.value = productos.data.current_page;
}

const eliminarProducto = async (id) => {
    try {
        await api.delete('/productos/' + id);
        lanzarToast("Producto eliminado correctamente.");
        productosUser.value = productosUser.value.filter(p => p.id !== id);
    } catch (error) {
        lanzarToast("No se pudo eliminar el producto.");
    }
}

const irAlPunto = (punto) => {
    const lat = parseFloat(punto.latitud);
    const lng = parseFloat(punto.longitud);
    map.flyTo([lat, lng], 10);
}

const cambiarSeccion = async (seccion) => {
    if (activarMapa.value) {
        esconderMapa();
    }
    await nextTick();
    eleccionActual.value = seccion;
};

onMounted(async () => {
    await fetchUsuario();
    if (usuario.value?.id) {
        cargarPuntos();
        cargarProductosUser();
    }
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});
</script>

<template>
    <NavBar/>
    <div class="contenedor-pagina">
        <div class="contenedor-titulo">
            <h1 class="titulo">Mi Cuenta</h1>
            <p class="subtitulo">Gestiona tus productos y datos personales</p><br>
        </div>

        <div class="contenido-centrado">
            <div class="card-perfil">
                <h3>Mi Perfil</h3><br>
                <div class="info-usuario">
                    <p><span><img src="../assets/iconos/mi_cuenta_verde.png" alt="icono-usuario"
                                class="icono">Nombre:</span> {{ usuario?.nombre_usuario || 'Cargando...' }}</p>
                    <p><span><img src="../assets/iconos/correo.png" alt="icono-email" class="icono">Email:</span> {{
                        usuario?.email }}</p>
                    <p><span><img src="../assets/iconos/ubicacion.png" alt="icono-direccion"
                                class="icono">Dirección:</span> {{ usuario?.direccion || 'No definida' }}</p>

                    <div class="linea-valoracion">
                        <span class="etiqueta-valoracion">
                            <img src="../assets/iconos/valoraciones-icono.png" alt="icono-valoracion" class="icono">
                            Valoración:
                        </span>

                        <div v-if="usuario?.puntuacion" class="d-flex align-items-center">
                            <ValoracionEstrellas :model-value="Number(usuario?.puntuacion)" :solo-lectura="true" />
                            <span class="ms-2 text-muted">
                                ({{ Number(usuario?.puntuacion).toFixed(2) }})
                            </span>
                        </div>

                        <span v-else class="text-muted ms-1">
                            Sin valoraciones
                        </span>
                    </div>
                </div>
            </div>

            <div class="contenedor-accion-superior">
                <button @click="guardarPuntoEntrega" class="botones-perfil">
                    Crear nuevo punto de entrega
                </button>
                <router-link to="/ubicacion" class="botones-perfil">
                    Cambiar mi ubicación
                </router-link>
            </div>

            <div v-if="activarMapa" class="seccion-gestion-puntos">
                <div class="formulario-mapa">
                    <h3>Configurar ubicación</h3>
                    <div id="map"></div>
                    <div class="controles-mapa">
                        <input v-model="nombrePunto" placeholder="Nombre del punto (Ej: Casa)">
                        <div class="botones-flex">
                            <button :disabled="!direccion" @click="crearPunto" class="boton-confirmar">Guardar</button>
                            <button @click="esconderMapa" class="boton-cancelar">Cerrar</button>
                        </div>
                    </div>
                </div>

                <div class="tus-puntos-existentes">
                    <h3>Tus puntos de entrega actuales</h3>
                    <div class="grid-puntos-mini">
                        <div v-for="punto in puntosEntrega" @click="irAlPunto(punto)" :key="punto.id"
                            class="card-punto-mini">
                            <div class="info-mini">
                                <strong>{{ punto.nombre_punto }}</strong>
                                <p>{{ punto.direccion_punto }}</p>
                            </div>
                            <button @click.stop="eliminarPunto(punto.id)" class="boton-borrar">Borrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="eleccion">
                <ul>
                    <li>
                        <button :class="{ active: eleccionActual === 'productos' }"
                            @click="cambiarSeccion('productos')">
                            <img src="../assets/iconos/productos_stock.png" alt="caja-stock" class="iconoSubNav">Mis
                            Productos ({{ productosUser.length }})
                        </button>
                    </li>
                    <li>
                        <button :class="{ active: eleccionActual === 'compras' }" @click="cambiarSeccion('compras')">
                            <img src="../assets/iconos/carrito.png" alt="carrito" class="iconoSubNav">Mis Compras
                        </button>
                    </li>
                    <li>
                        <button :class="{ active: eleccionActual === 'ventas' }" @click="cambiarSeccion('ventas')">
                            <img src="../assets/iconos/manzana.png" alt="manzana" class="iconoSubNav">Mis Ventas
                        </button>
                    </li>
                    <li>
                        <button :class="{ active: eleccionActual === 'valoraciones' }"
                            @click="cambiarSeccion('valoraciones')">
                            <img src="../assets/iconos/valoraciones-icono.png" alt="valoraciones"
                                class="iconoSubNav">Mis Valoraciones
                        </button>
                    </li>
                </ul>
            </div>

            <div class="contenedor-secciones-datos">
                <MostrarProductos v-if="eleccionActual === 'productos'" :productos="productosUser"
                    :pagination="pagination" :paginaActual="paginaActual" @borrar="eliminarProducto"
                    @cambiarPagina="cargarProductosUser" />
                <MisCompras v-else-if="eleccionActual === 'compras'" :eleccion="'compras'" />
                <MisVentas v-else-if="eleccionActual === 'ventas'" :eleccion="'ventas'" />
                <ValoracionView v-else-if="eleccionActual === 'valoraciones'" />
            </div>
        </div>
        <div v-if="toastVisible" class="toast-notificacion">
            {{ toastMensaje }}
        </div>
    </div>
    <Footer></Footer>
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', 'Arial', sans-serif;
}

body {
    min-width: 400px;
}

.contenedor-pagina {
    margin-top: 80px;
    padding: 20px 50px;
}

.contenido-centrado {
    max-width: 90%;
    margin: 0 auto;
}

.contenedor-titulo {
    max-width: 90%;
    margin: 10px auto 0 auto;
}

.titulo {
    color: #4CA626;
    margin-bottom: 10px;
    font-weight: bold;
}

.subtitulo {
    color: #666666;
    margin-bottom: 20px;
}

/* --- CARD PERFIL Y DATOS --- */
.card-perfil {
    background: white;
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}

.info-usuario p,
.linea-valoracion {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    /* Margen consistente para todos los campos */
}

.info-usuario p span,
.etiqueta-valoracion {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #4CA626;
    font-weight: bold;
    white-space: nowrap;
}

.icono {
    width: 25px;
    height: 25px;
    object-fit: contain;
}

hr {
    border: none;
    height: 2px;
    background-color: #EEEEEE;
    margin: 20px 0;
}

/* --- BOTONES Y ACCIONES --- */
.contenedor-accion-superior {
    margin-bottom: 40px;
    display: flex;
    gap: 15px;
}

.botones-perfil {
    background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    transition: transform 0.2s;
}

.botones-perfil:hover {
    background: linear-gradient(90deg, #008F4C 0%, rgb(1, 104, 59) 100%);
    transform: translateY(-1px);
}

/* --- MAPA Y GESTIÓN DE PUNTOS --- */
.seccion-gestion-puntos {
    margin-bottom: 40px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 12px;
    border: 1px solid #ddd;
}

#map {
    height: 300px;
    width: 100%;
    border-radius: 8px;
    margin-bottom: 15px;
    z-index: 1;
}

.controles-mapa input {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    outline: none;
}

.controles-mapa input:focus {
    border-color: #4CA626;
    box-shadow: 0 0 0 3px rgba(76, 166, 38, 0.1);
}

.grid-puntos-mini {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.card-punto-mini {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s;
}

.card-punto-mini:hover {
    background-color: #f0fdf4;
    border-color: #4CA626;
}

.boton-confirmar {
    background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    cursor: pointer;
}

.boton-confirmar:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.boton-cancelar,
.boton-borrar {
    background: #fee2e2;
    color: #ef4444;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
}

/* --- SUB-NAVEGACIÓN (ELECCIÓN) --- */
.eleccion {
    margin-bottom: 25px;
}

.eleccion ul {
    list-style: none;
    display: flex;
    background-color: #f3f4f6;
    padding: 5px;
    border-radius: 50px;
    border: 1px solid #e5e7eb;
}

.eleccion li {
    flex: 1;
}

.eleccion button {
    width: 100%;
    padding: 10px 0;
    border: none;
    background: transparent;
    font-weight: bold;
    cursor: pointer;
    border-radius: 50px;
    color: #4b5563;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: 0.3s;
}

.eleccion button.active {
    background-color: white;
    color: #000;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.iconoSubNav {
    width: 20px;
    height: 20px;
}

/* --- SECCIONES DE DATOS --- */
.contenedor-secciones-datos {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-height: 200px;
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

@media (max-width: 600px) {
    .contenedor-accion-superior {
        flex-direction: column;
    }

    .botones-perfil {
        text-align: center;
    }

    .eleccion ul {
        flex-direction: column;
        border-radius: 12px;
    }
}
</style>