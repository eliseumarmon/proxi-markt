<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { useRouter } from 'vue-router';

const router = useRouter()

const latitud = ref(null);
const longitud = ref(null);
const nombreCalle = ref('');
const cargando = ref(false);

let map = null;
let markerseleccion = null;

const inicializarMapa = async () => {
    await nextTick(); 

    const centroInicial = [39.032719, -0.215864];

    map = L.map('map').setView(centroInicial, 13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    markerseleccion = L.marker([0, 0]).addTo(map);

    map.on('click', async (e) => {
        latitud.value = e.latlng.lat;
        longitud.value = e.latlng.lng;

        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitud.value}&lon=${longitud.value}`;
        
        try {
            const response = await fetch(url);
            const data = await response.json();
            
            nombreCalle.value = data.address.road || data.display_name;

            markerseleccion
                .setLatLng(e.latlng)
                .bindPopup("Ubicación seleccionada")
                .openPopup();

        } catch (error) {
            console.error("Error al obtener dirección:", error);
            nombreCalle.value = "Dirección no encontrada";
        }
    });
};

const guardarUbicacion = async () => {
    const token = localStorage.getItem('token');
    
    if (!token) {
        alert("Sesión no válida. Por favor, inicia sesión.");
        return;
    }

    cargando.value = true;

    const datos = {
        direccion: nombreCalle.value,
        latitud: latitud.value,
        longitud: longitud.value
    };

    try {
        const respuesta = await axios.put('http://localhost:8080/api/ubicacionusuario', datos, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if(respuesta.status === 201){
            alert("Dirección actualizada correctamente.");
            console.log("Respuesta:", respuesta.data);
            router.push('/cuenta');
        }

    } catch (error) {
        console.error("Error al guardar:", error.response?.data);
        alert("Hubo un error al guardar los datos.");
    } finally {
        cargando.value = false;
    }
};

onMounted(() => {
    inicializarMapa();
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
        <div v-if="nombreCalle" class="info-box animate-in">
          <p class="label">Dirección seleccionada:</p>
          <p class="address-text">{{ nombreCalle }}</p>
          
          <button @click="guardarUbicacion" :disabled="cargando" class="btn-primary">
            <span v-if="!cargando">Confirmar y Guardar</span>
            <span v-else>Procesando...</span>
          </button>
        </div>
        
        <div v-else class="empty-state">
          <p>Haz clic en el mapa para marcar tu posición en el mapa</p>
        </div>
      </div>
    </div>
  </div>
</template>


<style scoped>
/* Aplicando tu paleta de colores */

.main-container {
  min-height: 100vh;
  background-color: #F0F0F0; /* Gris de tu paleta */
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.card {
  background-color: #FFFFFF; /* Blanco de tu paleta */
  width: 100%;
  max-width: 700px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  overflow: hidden;
  padding: 24px;
}

.title {
  color: #4CA626; /* Verde oscuro principal */
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
  background-color: #B9E2A6; /* Verde muy suave de la paleta */
  padding: 15px;
  border-radius: 10px;
  border: 1px solid #8BD16A; /* Verde medio */
}

.label {
  color: #4CA626;
  font-size: 0.8rem;
  font-weight: bold;
  text-transform: uppercase;
  margin-bottom: 5px;
}

.address-text {
  color: #333;
  font-weight: 500;
  margin-bottom: 15px;
}

.btn-primary {
  background-color: #4CA626; /* Verde principal */
  color: white;
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-primary:hover {
  background-color: #8BD16A; /* Verde medio para el hover */
}

.btn-primary:disabled {
  background-color: #B9E2A6;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  color: #8BD16A;
  font-style: italic;
  padding: 10px;
}

.animate-in {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>