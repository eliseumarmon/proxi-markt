<script setup>
import { ref, computed, watch } from 'vue';

const propiedades = defineProps(['mostrar', 'distanciaInicial']);
const emitir = defineEmits(['cerrar', 'confirmar']);

const minimoInterno = 0; 
const maximo = 200; 

const maximoSlider = computed(() => maximo + 1);

const etiquetas = computed(() => [
  { valor: 0, texto: `1 km` },
  { valor: maximo / 2, texto: `${maximo / 2} km` },
  { valor: maximoSlider.value, texto: 'Sin límite' }
]);

const normalizarEntrada = (valor) => {
  if (valor === Infinity || valor >= maximoSlider.value) return maximoSlider.value;
  if (valor <= 1) return 0;
  return valor;
};

const radioTemporal = ref(normalizarEntrada(propiedades.distanciaInicial));

const esIlimitado = computed(() => radioTemporal.value === maximoSlider.value);

const valorAMostrar = computed(() => {
  if (esIlimitado.value) return 'Sin límite';
  return `${Math.max(1, radioTemporal.value)} km`;
});

const calcularPorcentaje = (valor) => {
  return ((valor - minimoInterno) * 100) / (maximoSlider.value - minimoInterno);
};

const tamanoFondo = computed(() => {
  return `${calcularPorcentaje(radioTemporal.value)}% 100%`;
});

watch(() => propiedades.mostrar, (seEstaMostrando) => {
  if (seEstaMostrando) {
    radioTemporal.value = normalizarEntrada(propiedades.distanciaInicial);
  }
});

const confirmarSeleccion = () => {
  const valorFinal = esIlimitado.value ? Infinity : Math.max(1, radioTemporal.value);
  emitir('confirmar', valorFinal);
};
</script>

<template>
  <div v-if="mostrar" class="capa-modal">
    <div class="contenedor-modal">
      <button class="boton-cerrar-x" @click="$emit('cerrar')">✕</button>

      <div class="cabecera-seccion">
        <div class="circulo-icono">
          <img class="radio-icono" src="../assets/iconos/flecha-mapa.png" alt="Radio">
        </div>
        <h2>Radio de Búsqueda</h2>
      </div>

      <p class="texto-descripcion">
        Selecciona la distancia máxima para buscar productos frescos cerca de ti
      </p>

      <div class="bloque-valor">
        <span class="cifra-grande">
          {{ valorAMostrar }}
        </span>
        <span class="subtexto-etiqueta">
          {{ esIlimitado ? 'Mostrando todos los productos' : 'Distancia máxima de búsqueda' }}
        </span>
      </div>

      <div class="area-deslizador">
        <div class="contenedor-input">
          <input 
            type="range" 
            v-model.number="radioTemporal" 
            :min="minimoInterno" 
            :max="maximoSlider"
            :style="{ backgroundSize: tamanoFondo }"
            class="entrada-rango"
          >
          <div class="etiquetas-rango">
            <span 
              v-for="(etiqueta, index) in etiquetas" 
              :key="index"
              class="etiqueta-item"
              :style="{ 
                left: `${calcularPorcentaje(etiqueta.valor)}%`,
                transform: index === 0 ? 'translateX(0)' : index === etiquetas.length - 1 ? 'translateX(-100%)' : 'translateX(-50%)'
              }"
            >
              {{ etiqueta.texto }}
            </span>
          </div>
        </div>
      </div>

      <div class="cuadro-informativo">
        <div class="icono-ubicacion">
          <img src="../assets/iconos/ubicacion.png" alt="Ubicación">
        </div>
        <div class="contenido-info">
          <p v-if="!esIlimitado">
            Buscaremos productos en un radio de <strong>{{ Math.max(1, radioTemporal) }} km</strong> desde tu ubicación
          </p>
          <p v-else>
            Buscaremos productos en <strong>cualquier distancia</strong> desde tu ubicación
          </p>
          <span>Productos en tu zona</span>
        </div>
      </div>

      <button class="boton-confirmacion" @click="confirmarSeleccion">
        Confirmar Radio de Búsqueda
      </button>
    </div>
  </div>
</template>

<style scoped>
.capa-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.contenedor-modal {
  background: white;
  width: 90%;
  max-width: 420px;
  padding: 32px;
  border-radius: 24px;
  position: relative;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.boton-cerrar-x {
  position: absolute;
  top: 20px;
  right: 20px;
  background: none;
  border: none;
  font-size: 18px;
  color: #888;
  cursor: pointer;
}

.cabecera-seccion {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.circulo-icono {
  background: #00c853;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.radio-icono {
  width: 30px;
  height: 30px;
  object-fit: contain;
}

.cabecera-seccion h2 {
  font-size: 20px;
  margin: 0;
  color: #1a1a1a;
  font-weight: 700;
}

.texto-descripcion {
  color: #666;
  font-size: 14px;
  line-height: 1.4;
  margin-bottom: 30px;
}

.bloque-valor {
  text-align: center;
  margin-bottom: 30px;
  min-height: 70px;
}

.cifra-grande {
  display: block;
  font-size: 44px;
  font-weight: 700;
  color: #4CA626;
  line-height: 1;
}

.subtexto-etiqueta {
  font-size: 12px;
  color: #aaa;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 8px;
  display: block;
}

.area-deslizador {
  margin-bottom: 40px;
}

.contenedor-input {
  position: relative;
  width: 100%;
}

.entrada-rango {
  appearance: none;
  -webkit-appearance: none;
  width: 100%; 
  height: 8px;
  border-radius: 5px;
  background: #ebebeb;
  background-image: linear-gradient(#1a1a1a, #1a1a1a);
  background-repeat: no-repeat;
  cursor: pointer;
  outline: none;
  display: block;
}

.entrada-rango::-webkit-slider-thumb {
  appearance: none;
  -webkit-appearance: none;
  height: 22px; 
  width: 22px;
  border-radius: 50%;
  background: white;
  border: 2px solid #1a1a1a;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.etiquetas-rango {
  position: relative;
  width: 100%;
  height: 20px;
  margin-top: 12px;
}

.etiqueta-item {
  position: absolute;
  color: #AAAAAA;
  font-size: 11px;
  font-weight: 500;
  white-space: nowrap;
  top: 0;
}

.cuadro-informativo {
  background: #f1fdf5;
  border-radius: 16px;
  padding: 16px;
  display: flex; 
  gap: 12px;
  margin-bottom: 24px;
}

.icono-ubicacion { 
  display: flex;
  align-items: center;
}

.icono-ubicacion img {
  width: 30px;
  height: 30px;
}

.contenido-info p {
  margin: 0;
  font-size: 13px;
  color: #333;
  line-height: 1.4;
}

.contenido-info strong {
  color: #4CA626;
}

.contenido-info span {
  font-size: 11px;
  color: #999999;
  display: block;
  margin-top: 2px;
}

.boton-confirmacion {
  width: 100%;
  background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
  color: white;
  border: none;
  padding: 16px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.2s;
}

.boton-confirmacion:hover {
  filter: brightness(1.1);
  transform: translateY(-1px);
}

.boton-confirmacion:active {
  transform: translateY(0px);
}
</style>