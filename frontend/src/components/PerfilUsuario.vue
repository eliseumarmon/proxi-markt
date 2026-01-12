<script setup>
  import L from 'leaflet'
  import 'leaflet/dist/leaflet.css'
  import { ref, nextTick, onMounted } from 'vue'
  import axios from 'axios'

  let map;

  const activarMapa = ref(false)

  const nombreCalle = ref('');
  const latitud = ref(0)
  const longitud = ref(0)
  const nombrePunto = ref('')
  const PuntosEntrega = ref([])

  console.log(PuntosEntrega)

  const GuardarPuntoEntrega = async () => {
    if(PuntosEntrega.value.length >= 5){
        activarMapa.value = false;
        alert('Solo puedes hacer 5 puntos de entrega');
        return;
    }else{
        activarMapa.value = true;
    }

    await nextTick();
    
    map = L.map('map').setView([39.032719, -0.215864], 13); 

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }).addTo(map);
    
    for(let i = 0 ; i < PuntosEntrega.value.length ; i++){
      const longitud = parseFloat(PuntosEntrega.value[i].longitud)
      const latitud = parseFloat(PuntosEntrega.value[i].latitud)
      const marker = L.marker([ latitud, longitud ]).addTo(map).bindPopup( PuntosEntrega.value[i].nombre_punto );
    }
    
    var markerseleccion = L.marker([0, 0]).addTo(map);

    async function onMapClick(e) {
      latitud.value = e.latlng.lat;
      longitud.value = e.latlng.lng;

      const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitud.value}&lon=${longitud.value}`;
      try{
        const response = await fetch(url);
        const data = await response.json();

        console.log(data);

        nombreCalle.value = data.address.road || data.display_name

        markerseleccion
          .setLatLng(e.latlng) 
          .bindPopup("Estas en " + nombreCalle.value) 
          .openPopup();
        }catch(error){

        }
    }
    map.on('click', onMapClick);

    }

  const CrearPunto = async () =>{
    const Datos = {
        latitud: latitud.value,
        longitud: longitud.value,
        nombre_punto: nombrePunto.value,
        direccion_punto: nombreCalle.value
    }
    try{
        await axios.post('/insertarpunto', Datos, {withCredentials: true});
        alert('creado')
        location.reload();
        //refrescar la pagina
    }catch (error){
        console.error("Error del servidor:", error.response ? error.response.data : error.message);
        
        const mensajeError = error.response?.data?.message || "Error desconocido";
        alert('Fallo al crear: ' + mensajeError);
    }
  }

  const CargarPuntos = async() => {
    const resposta = await axios.get('/puntos',{withCredentials: true} )
    PuntosEntrega.value = resposta.data;
  }

  const EsconderMapa = () =>{
    activarMapa.value = false
  }

  onMounted(() => {
      CargarPuntos();
  });

</script>
<template>
  <div>
    <button @click="GuardarPuntoEntrega">Crear punto de entrega</button>

    <div v-if="activarMapa" id="map" style="height: 400px; width: 100%; margin-top: 20px;"></div>
    <div v-if="activarMapa">
        <label for="nombrepunto">Nombre del punto de venta</label><br>
        <input v-model="nombrePunto" type="text" name="nombrepunto" id="nombrepunto" required><br><br>
        <button @click="CrearPunto">Guardar</button><button @click="EsconderMapa">Cancelar</button>
    </div>

    <p v-if="nombreCalle">Calle seleccionada: <strong>{{ nombreCalle }}</strong></p>

    <div v-if="PuntosEntrega.length === 0">
      <h2>Llista de Punts</h2>
        <p>No hay Punts disponibles</p>
    </div>
    <div v-else>
        <h2>Llista de Punts</h2>
        <table>
          <thead>
            <tr>
              <th>Nom</th>
              <th>Direccio</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="punto in PuntosEntrega" :key="punto.id">
              <td>{{ punto.nombre_punto }}</td>
              <td>{{ punto.direccion_punto }}</td>

              <!-- <td>
                <button @click="Eliminarproducte(producte.id)">Eliminar</button>
              </td> -->
            </tr>
          </tbody>
        </table>
    </div>
  </div>
</template>