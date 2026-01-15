<template>
  <navbar></navbar>
  <div class="contenedor-pagina">
    <div id="contenedor-titulo">
      <h1 class="titulo">Productos Frescos y Locales</h1>
      <p class="subtitulo">Conecta directamente con productores de tu zona (radio: 50 km)</p>
    </div>
    

    <div id="contenedor-buscador">
      <div id="buscador">
        <div class="caja">
          <img src="../assets/iconos/buscar.png" alt="lupa" class="icono" />
          <input class="texto" type="text" placeholder="Buscar productos frescos..."/>
        </div>
        <button class="boton-filtro">
          <img src="../assets/iconos/filtro.png" alt="filtro productos" class="icono"/>
          Filtros
        </button>
      </div>
      <p class="informacion-filtro"> 6 productos encontrados <span class="texto-verde">(en un radio de 50 km)</span></p>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import navbar from "./nav.vue";

const router = useRouter();
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  min-width: 400px;
}

.contenedor-pagina {
  margin-top: 80px;
  padding: 20px 50px;
}

#contenedor-titulo{
  max-width: 90%;
  margin: 40px auto 0 auto;
}

.titulo {
  font-family: sans-serif;
  color: #4ca626;
  margin-bottom: 10px;
  font-weight: bold;
}

.subtitulo {
  font-family: sans-serif;
  color: #666666;
  margin-bottom: 20px;
}

#contenedor-buscador {
  background-color: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1), 0 -4px 10px rgba(0, 0, 0, 0.1);
  font-family: sans-serif;
  max-width: 90%;
  margin: 40px auto 0 auto;
}

#buscador {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
}

.caja {
  background-color: #F3F4F6;
  border-radius: 8px;
  padding: 15px 15px;
  display: flex;
  align-items: center;
  flex-grow: 1;
  border: 1px solid #E5E7EB;
}

.texto {
  border: none;
  background: transparent;
  width: 100%;
  margin-left: 10px;
  outline: none;
  font-size: 16px;
  color: #333333;
}

.caja:focus-within {
  background-color: #F3F4F6;
  border-color: #D1D5DB;
  box-shadow: 0 0 0 3px rgba(0, 176, 80, 0.1);
}

.boton-filtro {
  background-color: white;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  padding: 15px 20px;
  height: 100%;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: bold;
  font-size: 18px;
  color: #333333;
  white-space: nowrap;
}

.icono {
  width: 30px;
  height: 30px;
}

.informacion-filtro {
  color: #666666;
  align-items: center;
}

.texto-verde {
  color: #4ca626;
}
</style>
