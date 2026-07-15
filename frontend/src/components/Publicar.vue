<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import L from 'leaflet';
import Navbar from './NavBar.vue'
import Footer from "./Footer.vue";
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import axios from 'axios';
import api from '@/api/axios';
import { leafletPin } from '@/utils/leafletPin';

const router = useRouter()
const { usuario, fetchUsuario } = useAuth();

const puntosEntrega = ref([])
const puntoEntrega = ref('');
const categorias = ref([])
const categoria = ref('');
const nombreProducto = ref('');
const descripcion = ref('');
const precio = ref('');
const stock = ref('');
const imagen = ref(null);
const imagenPreview = ref(null);

const cargando = ref(false);
const errores = ref({});

const mostrarModalPunto = ref(false);
const nuevoPuntoNombre = ref('');
const nuevoPuntoDireccion = ref('');
const nuevaLatitud = ref(null);
const nuevaLongitud = ref(null);
const cargandoNuevoPunto = ref(false);
let map = null;

const toastVisible = ref(false);
const toastMensaje = ref("");

const lanzarToast = (mensaje) => {
    toastMensaje.value = mensaje;
    toastVisible.value = true;
    setTimeout(() => {
        toastVisible.value = false;
    }, 3000);
};

const guardarImagen = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (imagenPreview.value) URL.revokeObjectURL(imagenPreview.value);
        imagen.value = file;
        imagenPreview.value = URL.createObjectURL(file);
    }
}

const abrirModalYMapa = async () => {
    mostrarModalPunto.value = true;
    await nextTick();
    iniciarMapa();
}

const iniciarMapa = () => {
    if (map) {
        map.remove();
        map = null;
    }

    const centroInicial = (usuario.value?.latitud && usuario.value?.longitud)
        ? [usuario.value.latitud, usuario.value.longitud]
        : [39.4699, -0.3763];

    nuevoPuntoDireccion.value = "";
    nuevoPuntoNombre.value = "";
    nuevaLatitud.value = null;
    nuevaLongitud.value = null;

    map = L.map('map-modal').setView(centroInicial, 13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let marcadorTemporal = L.marker(centroInicial, { icon: leafletPin, opacity: 0 }).addTo(map);

    map.on('click', async (e) => {
        nuevaLatitud.value = e.latlng.lat;
        nuevaLongitud.value = e.latlng.lng;
        marcadorTemporal
            .setLatLng(e.latlng)
            .setOpacity(1)
            .bindPopup("Ubicación seleccionada")
            .openPopup();

        try {
            const response = await axios.get("https://nominatim.openstreetmap.org/reverse", {
                params: {
                    format: 'jsonv2',
                    lat: nuevaLatitud.value,
                    lon: nuevaLongitud.value
                }
            });

            const address = response.data.address;
            const partesDireccion = [
                address.road,
                address.house_number,
                address.city || address.town || address.village,
                address.postcode
            ].filter(Boolean);

            nuevoPuntoDireccion.value = partesDireccion.join(", ") || "Ubicación seleccionada";

        } catch (error) {
            console.error("Error obteniendo dirección:", error);
        }
    });
}

const guardarNuevoPunto = async () => {
    if (!nuevoPuntoNombre.value || !nuevaLatitud.value) {
        alert("Por favor, pon un nombre y selecciona una ubicación en el mapa.");
        return;
    }

    cargandoNuevoPunto.value = true;

    try {
        const datosPunto = {
            id_usuario: usuario.value.id,
            nombre_punto: nuevoPuntoNombre.value,
            direccion_punto: nuevoPuntoDireccion.value,
            latitud: nuevaLatitud.value,
            longitud: nuevaLongitud.value
        };

        const respuesta = await api.post('/puntos', datosPunto);
        await cargarPuntos();

        // Intentar seleccionar el punto recién creado
        const nuevoId = respuesta.data.id;
        if (nuevoId) {
            puntoEntrega.value = nuevoId;
        }

        mostrarModalPunto.value = false;
    } catch (error) {
        alert("Error al crear punto: " + (error.response?.data?.message || error.message));
    } finally {
        cargandoNuevoPunto.value = false;
    }
}

const intentarPublicar = () => {
    errores.value = {};

    if (!nombreProducto.value) errores.value.nombre = true;
    if (!descripcion.value) errores.value.descripcion = true;
    if (!precio.value || precio.value <= 0) errores.value.precio = true;
    if (!stock.value || stock.value <= 0) errores.value.stock = true;
    if (!categoria.value) errores.value.categoria = true;

    if (puntosEntrega.value.length === 0) {
        alert("¡Atención! Para publicar, necesitas configurar dónde entregarás el producto.");
        abrirModalYMapa();
        return;
    }

    if (!puntoEntrega.value) {
        errores.value.punto = true;
    }

    if (Object.keys(errores.value).length > 0) return;

    insertarProducto();
}

const insertarProducto = async () => {
    try {
        const datos = new FormData();
        datos.append('nombre_producto', nombreProducto.value);
        datos.append('descripcion', descripcion.value);
        datos.append('precio', precio.value);
        datos.append('stock_total', stock.value);
        datos.append('id_categoria', categoria.value);
        datos.append('id_puntoentrega', puntoEntrega.value);

        if (imagen.value) {
            datos.append('imagen', imagen.value);
        }

        await api.post('/productos', datos, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        lanzarToast("¡Producto creado correctamente!");
        setTimeout(() => {
            router.push('/cuenta');
        }, 2000);

    } catch (error) {
        lanzarToast('No se pudo crear el producto');
        console.error("Error:", error.response?.data?.message);
    }
}

const cargarPuntos = async () => {

    cargando.value = true;

    const token = localStorage.getItem('token');

    try {
        const resposta = await api.get(`/usuarios/${usuario.value.id}/puntos`);
        puntosEntrega.value = resposta.data;
    } catch (err) {
        lanzarToast("Error cargando puntos de entrega.");
        console.error(err.message);
    } finally {
        cargando.value = false;
    }
}

const cargarCategorias = async () => {
    const resposta = await api.get('/categorias');
    categorias.value = resposta.data;
}

onMounted(async () => {
    await fetchUsuario();
    if (usuario.value?.id) {
        cargarPuntos();
        cargarCategorias();
    }
});

onBeforeUnmount(() => {
    if (map) map.remove();
    if (imagenPreview.value) URL.revokeObjectURL(imagenPreview.value);
});
</script>

<template>
    <Navbar />
    <div class="contenedor-pagina">
        <div id="contenedor-titulo">
            <h1 class="titulo">Publicar Producto</h1>
            <p class="subtitulo">Comparte tus frutas y verduras frescas con la comunidad</p>
        </div>

        <div class="tarjeta-formulario">
            <h3 class="header-interno">Publicar nuevo producto</h3>

            <form @submit.prevent>
                <div class="campo">
                    <label>Nombre del producto</label>
                    <input v-model="nombreProducto" type="text" placeholder="Ej: Tomates orgánicos"
                        :class="{ 'input-error': errores.nombre }">
                    <span v-if="errores.nombre" class="texto-validacion">Campo obligatorio</span>
                </div>

                <div class="campo">
                    <label>Descripción</label>
                    <textarea v-model="descripcion" placeholder="Describe tu producto..."
                        :class="{ 'input-error': errores.descripcion }"></textarea>
                </div>

                <div class="fila-doble">
                    <div class="columna">
                        <label>Precio (€)</label>
                        <input v-model="precio" type="number" step="0.01" placeholder="0.00"
                            :class="{ 'input-error': errores.precio }">
                    </div>
                    <div class="columna">
                        <label>Stock disponible</label>
                        <input v-model="stock" type="number" placeholder="Cantidad"
                            :class="{ 'input-error': errores.stock }">
                    </div>
                </div>

                <div class="campo">
                    <label>Categoría</label>
                    <select v-model="categoria" :class="{ 'input-error': errores.categoria }">
                        <option value="" disabled>Selecciona una categoría</option>
                        <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nombre_categoria }}
                        </option>
                    </select>
                </div>

                <div class="campo">
                    <div class="label-con-accion">
                        <label>Punto de entrega</label>
                        <button type="button" class="btn-link-crear" @click="abrirModalYMapa">
                            + Crear nuevo punto
                        </button>
                    </div>

                    <p class="ayuda-texto" v-if="cargando">Cargando puntos...</p>

                    <select v-if="puntosEntrega.length > 0" v-model="puntoEntrega"
                        :class="{ 'input-error': errores.punto }">
                        <option value="" disabled>Selecciona un punto de entrega</option>
                        <option v-for="punto in puntosEntrega" :key="punto.id" :value="punto.id">
                            {{ punto.nombre_punto }}
                        </option>
                    </select>

                    <div v-else-if="!cargando && puntosEntrega.length === 0" class="alerta-sin-puntos">
                        <p class="error-texto">⚠️ No tienes puntos de entrega configurados.</p>
                        <button type="button" class="btn-secundario-pequeno" @click="abrirModalYMapa">
                            Crear uno ahora
                        </button>
                    </div>
                    <span v-if="errores.punto && puntosEntrega.length > 0" class="texto-validacion">Selecciona un punto</span>
                </div>

                <div class="campo">
                    <label>Imagen del producto (opcional)</label>
                    <div class="zona-upload" :class="{ 'con-imagen': imagenPreview }">
                        <input type="file" @change="guardarImagen" accept="image/*" class="input-file-oculto">
                        <div v-if="!imagenPreview" class="diseno-upload">
                            <span class="icono-nube">↑</span>
                            <p>Haz clic para subir o arrastra una imagen</p>
                            <small>PNG, JPG o WEBP (máx. 5MB)</small>
                        </div>
                        <div v-else class="preview-contenedor">
                            <img :src="imagenPreview" alt="Previsualización" class="img-preview-real">
                            <div class="overlay-cambiar">
                                <span>Clic para cambiar imagen</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="acciones">
                    <button type="button" class="boton-cancelar" @click="$router.go(-1)">Cancelar</button>
                    <button type="button" class="boton-publicar" @click="intentarPublicar">Publicar producto</button>
                </div>
            </form>
        </div>
        <div v-if="toastVisible" class="toast-notificacion">
            {{ toastMensaje }}
        </div>
    </div>

    <div v-if="mostrarModalPunto" class="modal-overlay">
        <div class="modal-contenido modal-grande">
            <h3>Nuevo Punto de Entrega</h3>
            <p>Haz clic en el mapa para seleccionar la ubicación exacta.</p>

            <div id="map-modal" class="mapa-estilo"></div>

            <div class="modal-body">
                <label>Nombre del lugar</label>
                <input v-model="nuevoPuntoNombre" type="text" placeholder="Ej: Mercado Central" class="input-modal">

                <label>Dirección detectada</label>
                <input v-model="nuevoPuntoDireccion" type="text" placeholder="Selecciona en el mapa..."
                    class="input-modal" readonly>
            </div>

            <div class="modal-acciones">
                <button @click="mostrarModalPunto = false" class="btn-modal-cancelar">Cancelar</button>
                <button @click="guardarNuevoPunto" class="btn-modal-guardar"
                    :disabled="!nuevoPuntoNombre || !nuevaLatitud || cargandoNuevoPunto">
                    {{ cargandoNuevoPunto ? 'Guardando...' : 'Guardar Punto' }}
                </button>
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
    font-family: "Segoe UI", "Arial";
}

body {
    min-width: 400px;
}

.contenedor-pagina {
    margin-top: 45px;
    padding: 20px 50px;
}

#contenedor-titulo {
    width: 100%;
    max-width: 650px;
    margin: 40px auto 20px auto;
    padding: 0;
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

.tarjeta-formulario {
    background: #FFFFFF;
    width: 100%;
    max-width: 650px;
    padding: 35px;
    border-radius: 12px;
    border: 1px solid #EDF2F7;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    margin: 0 auto;
}

.header-interno {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 25px;
    color: #1A202C;
}

label {
    display: block;
    font-size: 13.5px;
    font-weight: 500;
    color: #4A5568;
    margin-bottom: 8px;
}

input[type="text"],
input[type="number"],
select,
textarea {
    width: 100%;
    padding: 11px 14px;
    background-color: #F8FAFC;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 18px;
}

.fila-doble {
    display: flex;
    gap: 20px;
    width: 100%;
}

.columna {
    flex: 1;
}

.label-con-accion {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.btn-link-crear {
    background: none;
    border: none;
    color: #00A859;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: underline;
    padding: 0;
}

.alerta-sin-puntos {
    background-color: #FFF5F5;
    border: 1px dashed #FC8181;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
}

.btn-secundario-pequeno {
    background-color: white;
    border: 1px solid #CBD5E0;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    margin-top: 8px;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2000;
}

.modal-contenido {
    background: white;
    padding: 25px;
    border-radius: 12px;
    width: 90%;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.modal-grande {
    max-width: 600px;
}

.mapa-estilo {
    width: 100%;
    height: 300px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #E2E8F0;
}

.modal-acciones {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
}

.btn-modal-cancelar {
    background: transparent;
    border: 1px solid #E2E8F0;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
}

.btn-modal-guardar {
    background: #00A859;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
}

.btn-modal-guardar:disabled {
    background: #9AE6B4;
    cursor: not-allowed;
}

.input-modal {
    margin-bottom: 15px;
}

.input-error {
    border-color: #e53e3e !important;
    background-color: #fff5f5 !important;
}

.texto-validacion {
    color: #e53e3e;
    font-size: 12px;
    margin-top: -15px;
    margin-bottom: 15px;
    display: block;
}

.error-texto {
    color: #e53e3e;
    font-size: 14px;
    margin-bottom: 10px;
}

.acciones {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.boton-cancelar {
    flex: 1;
    padding: 14px;
    background: white;
    border: 1px solid #E2E8f0;
    border-radius: 8px;
    cursor: pointer;
}

.boton-publicar {
    flex: 1;
    padding: 14px;
    background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
    border: none;
    color: white;
    border-radius: 8px;
    cursor: pointer;
}

.boton-publicar:hover {
    background: linear-gradient(to right, #00A650, #008F44);
}

.zona-upload {
    position: relative;
    border: 2px dashed #E2E8f0;
    border-radius: 10px;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    background-color: #fff;
    transition: all 0.2s;
    overflow: hidden;
}

.zona-upload:hover {
    background-color: #f9fafb;
    border-color: #00a859;
}

.zona-upload.con-imagen {
    border: 2px solid #4CA626;
    background-color: #000;
}

.input-file-oculto {
    opacity: 0;
    position: absolute;
    width: 100%;
    height: 100%;
    cursor: pointer;
    z-index: 10;
}

.diseno-upload {
    text-align: center;
    color: #718096;
}

.icono-nube {
    font-size: 24px;
    display: block;
    margin-bottom: 8px;
}

.preview-contenedor {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

.img-preview-real {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    display: block;
}

.overlay-cambiar {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.2s;
    font-weight: 600;
    z-index: 5;
    pointer-events: none;
}

.zona-upload:hover .overlay-cambiar {
    opacity: 1;
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
</style>
