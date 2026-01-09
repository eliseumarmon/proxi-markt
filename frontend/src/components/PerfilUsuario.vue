<script setup>
  import L from 'leaflet'
  import 'leaflet/dist/leaflet.css'
  import { ref, nextTick } from 'vue'

  let map;
  // Guardamos las capas de los marcadores para poder limpiarlas/actualizarlas
  let markersLayer; 

  const activarMapa = ref(false)
  const nombreCalle = ref('');
  const latitud = ref(0)
  const longitud = ref(0)
  const nombrePunto = ref('')
  
  const PuntosEntrega = ref([])

  const GuardarPuntoEntrega = async () => {
    if(PuntosEntrega.value.length >= 5){
        alert('Solo puedes hacer 5 puntos de entrega');
        return;
    }
    
    activarMapa.value = true;

    await nextTick();
    
    if (map) {
        map.remove();
    }

    map = L.map('map').setView([39.032719, -0.215864], 13); 

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);
    
    PuntosEntrega.value.forEach(punto => {
      L.marker([punto.latitud, punto.longitud])
       .addTo(map)
       .bindPopup(punto.nombre_punto);
    });
    
    let markerseleccion = L.marker([0, 0]).addTo(map);

    async function onMapClick(e) {
      latitud.value = e.latlng.lat;
      longitud.value = e.latlng.lng;

      const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitud.value}&lon=${longitud.value}`;
      try {
        const response = await fetch(url);
        const data = await response.json();
        
        nombreCalle.value = data.address.road || data.display_name || "Dirección desconocida";

        markerseleccion
          .setLatLng(e.latlng) 
          .bindPopup("Seleccionado: " + nombreCalle.value) 
          .openPopup();
      } catch(error) {
        console.error("Error obteniendo dirección", error);
      }
    }

    map.on('click', onMapClick);
  }

  const CrearPunto = () => {
    if (!nombrePunto.value || latitud.value === 0) {
        alert("Por favor, selecciona un punto en el mapa y dale un nombre.");
        return;
    }

    const nuevoPunto = {
        id: Date.now(), 
        latitud: latitud.value,
        longitud: longitud.value,
        nombre_punto: nombrePunto.value,
        direccion_punto: nombreCalle.value
    };

    PuntosEntrega.value.push(nuevoPunto);

    alert('Punto guardado localmente');
    limpiarFormulario();
  }

  const limpiarFormulario = () => {
    activarMapa.value = false;
    nombrePunto.value = '';
    nombreCalle.value = '';
    latitud.value = 0;
    longitud.value = 0;
  }

  const EsconderMapa = () => {
    activarMapa.value = false;
  }

</script>

<template>
  <div class="container">
    <button @click="GuardarPuntoEntrega" :disabled="PuntosEntrega.length >= 5">
      {{ PuntosEntrega.length >= 5 ? 'Límite alcanzado' : 'Crear punto de entrega' }}
    </button>

    <div v-if="activarMapa" id="map" style="height: 400px; width: 100%; margin-top: 20px;"></div>
    
    <div v-if="activarMapa" class="form-container">
        <p v-if="nombreCalle">Dirección detectada: <strong>{{ nombreCalle }}</strong></p>
        <label for="nombrepunto">Nombre del punto:</label><br>
        <input v-model="nombrePunto" type="text" required><br><br>
        <button @click="CrearPunto">Guardar en lista</button>
        <button @click="EsconderMapa">Cancelar</button>
    </div>

    <div class="listado">
        <h2>Lista de Puntos ({{ PuntosEntrega.length }}/5)</h2>
        
        <div v-if="PuntosEntrega.length === 0">
            <p>No hay puntos guardados en la lista local.</p>
        </div>
        
        <table v-else>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Dirección</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="punto in PuntosEntrega" :key="punto.id">
              <td>{{ punto.nombre_punto }}</td>
              <td>{{ punto.direccion_punto }}</td>
            </tr>
          </tbody>
        </table>
    </div>
  </div>
</template>

