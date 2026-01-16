<template>
  <navbar></navbar>
  <div class="contenedor-pagina">
    <div id="contenedor-titulo">
      <h1 class="titulo">Dashboard</h1>
      <p class="subtitulo">Resumen de tu actividad en ProxyMarket</p>
    </div>

    <div class="cajas-informacion-uno">
      <div class="caja">
        <h3>Mis productos</h3>
        <p>2</p>
        <img src="../assets/iconos/stock.png" alt="Mis productos">
      </div>

      <div class="caja">
        <h3>Stock total</h3>
        <p>55</p>
        <img src="../assets/iconos/stock.png" alt="Stock total">
      </div>

      <div class="caja">
        <h3>Ventas pendientes</h3>
        <p>3</p>
        <img src="../assets/iconos/stock.png" alt="Ventas pendientes">
      </div>

      <div class="caja">
        <h3>Ingresos</h3>
        <p>5.40€</p>
        <img src="../assets/iconos/stock.png" alt="Ingresos">
      </div>
    </div>
    <div class="cajas-informacion-dos">
      <div class="ventas">
        <img src="../assets/iconos/stock.png" alt="Ventas recientes" class="icono">
        <h3>Ventas Recientes</h3>
        <div class="producto-ventas">
          <p id="nombre-producto">Lechuga Fresca</p>
          <p id="precio">5.40€</p>
          <p id="info">Ana</p>
          <p id="estado">Completada</p>
        </div>
      </div>

      <div class="productos">
        <img src="../assets/iconos/stock.png" alt="Productos disponibles" class="icono">
        <h3>Productos disponibles</h3>
        <div class="producto-disponible">
          <p id="nombre-producto">Lechuga Fresca</p>
          <p id="precio-producto">1.40€</p>
          <p id="info">Ana</p>
          <p id="stock-disponible">18 disponibles</p>
        </div>
      </div>
    </div>

    <div class="cajas-informacion-tres">
      <div class="productos">
        <img src="../assets/iconos/stock.png" alt="Mis compras recientes" class="icono">
        <h3>Mis Compras Recientes</h3>
        <div class="compras-producto">
          <p id="nombre-producto">Lechuga Fresca</p>
          <p id="info">Vendedor: Juan</p>
          <p id="estado">Pendiente</p>
          <p id="precio">5.00€</p>
        </div>
      </div>
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
  font-family: 'Segoe UI', 'Arial';
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

.cajas-informacion-uno, .cajas-informacion-dos, .cajas-informacion-tres {
  max-width: 90%;
  margin-left: auto;
  margin-right: auto;
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.cajas-informacion-uno {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
}

.caja {
  flex: 1;
  min-width: 200px;
  border-radius: 12px;
  padding: 20px;
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  overflow: hidden;
}

.caja h3 {
  font-size: 14px;
  font-weight: 500;
  opacity: 0.9;
  z-index: 2;
}

.caja p {
  font-size: 28px;
  font-weight: 700;
  margin-top: 5px;
  z-index: 2;
}

.caja img {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  filter: brightness(0) invert(1);
  width: 70px;
  padding: 10px;
  background-color: rgba(255, 255, 255, 0.25);
  border-radius: 10px;
}

.cajas-informacion-uno .caja:nth-child(1) {
  background-image: linear-gradient(to left, #009e5b, #20c97e);
}

.cajas-informacion-uno .caja:nth-child(2) { 
  background-image: linear-gradient(to left, #1060c4, #4facfe);
}
  
.cajas-informacion-uno .caja:nth-child(3) { 
  background-image: linear-gradient(to left, #d95000, #ff8c42);
}
  
.cajas-informacion-uno .caja:nth-child(4) { 
  background-image: linear-gradient(to left, #801ac0, #b845fc);
}

.cajas-informacion-dos, .cajas-informacion-tres {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.ventas, .productos {
  background: white;
  border-radius: 12px;
  padding: 20px;
  flex: 1;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  border: 1px solid #eee;
}

.ventas h3, .productos h3 {
  display: inline-block;
  vertical-align: middle;
  margin: 0;
  margin-bottom: 20px;
  font-size: 18px;
  color: #333;
}

.ventas .icono, .productos .icono {
  display: inline-block;
  vertical-align: middle;
  width: 24px;
  margin-right: 10px;
  margin-bottom: 20px;
}

.producto-ventas, .producto-disponible, .compras-producto {
  display: grid;
  grid-template-columns: 1fr auto; 
  gap: 5px;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
  background-color: #f9fafb;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 10px;
  border-bottom: none;
}

.producto-ventas #nombre-producto {
  grid-column: 1;
  grid-row: 1;
  font-weight: bold;
  color: #333;
  font-size: 15px;
}

.producto-ventas #info {
  grid-column: 1;
  grid-row: 2;
  font-size: 13px;
  color: #888;
}

.producto-ventas #precio {
  grid-column: 2;
  grid-row: 1;
  text-align: right;
  color: #4ca626;
}

.producto-ventas #estado {
  grid-column: 2;
  grid-row: 2;
  text-align: right;
  font-size: 12px;
  font-weight: bold;
  color: #00b86b;
  background: #e0f8e9;
  padding: 2px 8px;
  border-radius: 4px;
  justify-self: end;
}

.producto-disponible #nombre-producto {
  grid-column: 1;
  grid-row: 1;
  font-weight: bold;
  color: #333;
}

.producto-disponible #info {
  grid-column: 1;
  grid-row: 2;
  font-size: 13px;
  color: #888;
}

.producto-disponible #precio-producto {
  grid-column: 2;
  grid-row: 1;
  text-align: right;
}

.producto-disponible #stock-disponible {
  grid-column: 2;
  grid-row: 2;
  text-align: right;
  font-size: 12px;
  color: #888;
}

.cajas-informacion-tres .productos {
  flex: 1;
}

.compras-producto {
  grid-template-columns: 1fr auto;
}

.compras-producto #nombre-producto {
  grid-column: 1;
  grid-row: 1;
  font-weight: bold;
}

.compras-producto #info {
  grid-column: 1;
  grid-row: 2;
  font-size: 13px;
  color: #888;
}

.compras-producto #estado { 
  grid-column: 1;
  grid-row: 3;
  display: inline-block;
  width: fit-content;
  font-size: 11px;
  background: #fff4e6;
  color: #ff9f43;
  padding: 2px 6px;
  border-radius: 4px;
  margin-top: 5px;
}

.compras-producto #precio { 
  grid-column: 2;
  grid-row: 1 / span 3;
  align-self: center;
  font-size: 16px;
}
</style>