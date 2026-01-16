<script setup>
import { reactive } from 'vue';
import { ref } from 'vue';
import axios from "axios";
import { useRouter } from "vue-router";
import navbar from "./nav.vue";

const router = useRouter();

const listaCategorias = [
    {id: 1, nombre: "Frutas"},
    {id: 2, nombre: "Verduras"},
    {id: 3, nombre: "Hortalizas"},
    {id: 4, nombre: "Legumbres"},
    {id: 5, nombre: "Hieerbas Aromaticas"},
    {id: 6, nombre: "Otros"}
];

const form = reactive({
    nombre_producto: '',
    descripcion:'',
    precio:'',
    stock_total:'',
    id_categoria:'',
    delivery_point: '',
    imagen: null
});

const cogerImagen = (event) => {
    form.imagen = event.target.files[0];
}

const enviar = async () => {
    let datos = new FormData();
    datos.append('nombre_producto', form.nombre_producto);
    datos.append('descripcion', form.descripcion);
    datos.append('precio', form.precio);
    datos.append('stock_total', form.stock_total);
    datos.append('id_categoria', form.id_categoria);
    datos.append('delivery_point', form.delivery_point)

    if (form.imagen) {
        datos.append('image', form.imagen);
    }

    try {
        await fetch('http://127.0.0.1:8000/api/products', {
            method: 'POST',
            body: datos,
            headers: {
                'Accept': 'application/json'
            }  
        });
        alert("¡Producto publicado!");
    } catch (e) {
        console.error(e);
        alert("Error al conectar");
    }
};


</script>

<template>
    <navbar></navbar>

    <div class="contenedor-pagina">
        <div id="contenedor-titulo">
            <h1 class="titulo">Publicar Producto</h1>
            <p class="subtitulo">Comparte tus frutas y verduras frescas con la comunidad</p>
        </div>

        <div class="contenedor-formulario">
            <form @submit.prevent="enviar">
                
                <label for="nom">Nombre del producto</label>
                <input type="text" id="nom" v-model="form.nombre_producto" placeholder="Ej: Tomates orgánicos" required>

                <label for="descr">Descripción</label>
                <textarea id="descr" v-model="form.descripcion" placeholder="Describe tu producto en detalle..." required></textarea>

                <div class="dos-columnas">
                    <div class="columna">
                        <label for="precio">Precio (€)</label>
                        <input type="number" step="0.01" id="precio" v-model="form.precio" placeholder="0.00" required>
                    </div>
                    <div class="columna">
                        <label for="stock">Stock disponible</label>
                        <input type="number" id="stock" v-model="form.stock_total" placeholder="Cantidad" required>
                    </div>
                </div>

                <label for="cat">Categoría</label>
                <select id="cat" v-model="form.id_categoria" required>
                    <option value="" disabled>Selecciona una categoría</option>
                    <option v-for="cat in listaCategorias" :key="cat.id" :value="cat.id">
                        {{ cat.nombre }}
                    </option>
                </select>

                <label for="punto">Punto de entrega </label>
                <input type="text" id="punto" v-model="form.delivery_point" placeholder="Ej: Mercado de Santa Caterina" required>
                <p class="nota-campo">Indica dónde el comprador podrá recoger el producto</p>

                <label>Imagen del producto (opcional)</label>
                <div class="imagen-caja">
                    <input type="file" id="imagen" accept="image/*" @change="cogerImagen">
                    <div class="imagen-contenido">
                        <span class="icono-imagen">↑</span>
                        <p>Haz clic para subir o arrastra una imagen</p>
                        <span class="info-imagen">PNG, JPG o WEBP (máx. 5MB)</span>
                    </div>
                </div>

                <div class="nota-azul">
                    <strong>Nota:</strong> Una vez publicado, los compradores podrán ver tu producto y enviarte solicitudes de compra.
                </div>

                <div class="grupo-botones">
                    <button type="button" class="btn-cancelar" onclick="history.back()">Cancelar</button>
                    <button type="submit" class="btn-publicar">Publicar producto</button>
                </div>
            </form>
        </div>
    </div>
</template>

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

.contenedor-formulario {
  background-color: #ffffff;
  max-width: 800px;
  margin: 0 auto;
  padding: 40px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}


label {
  display: block;
  font-weight: 700;
  margin-bottom: 8px;
  color: #111827;
  font-size: 14px;
}

input[type="text"],
input[type="number"],
select,
textarea {
  width: 100%;
  padding: 12px 16px;
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 14px;
  color: #1f2937;
  margin-bottom: 20px;
  transition: all 0.2s;
  font-family: inherit; 
}

input:focus, select:focus, textarea:focus {
  outline: none;
  background-color: #ffffff;
  border-color: #00b050;
  box-shadow: 0 0 0 3px rgba(0, 176, 80, 0.1);
}

textarea {
  resize: vertical;
  min-height: 100px;
}


.nota-campo {
  font-size: 12px;
  color: #9ca3af;
  margin-top: -15px;
  margin-bottom: 20px;
}


.dos-columnas {
  display: flex;
  gap: 20px;
}
.columna {
  flex: 1;
}

.imagen-caja {
  position: relative;
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  background-color: #ffffff;
  height: 180px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  cursor: pointer;
  margin-bottom: 30px;
  transition: background-color 0.2s;
}

.imagen-contenido:hover {
  background-color: #f9fafb;
  border-color: #00b050;
}

.imagen-caja input[type="file"] {
  position: absolute;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.icono-imagen {
  font-size: 32px;
  color: #9ca3af;
  display: block;
  margin-bottom: 10px;
}

.upload-content p {
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 5px;
}

.info-imagen {
  font-size: 12px;
  color: #9ca3af;
}

.nota-azul {
  background-color: #eff6ff;
  border: 1px solid #dbeafe;
  color: #1e40af; 
  padding: 16px;
  border-radius: 6px;
  font-size: 13px;
  margin-bottom: 30px;
  line-height: 1.5;
}


.grupo-botones {
  display: flex;
  gap: 15px; 
  border-top: 1px solid #f3f4f6;
  padding-top: 25px;
  width: 100%; 
}

button {
  flex: 1; 
  padding: 14px 24px; 
  border-radius: 8px;
  font-weight: 700;
  font-size: 15px;
  cursor: pointer;
  transition: transform 0.1s, box-shadow 0.2s;
}


button:active {
  transform: scale(0.98);
}

.btn-cancelar {
  background-color: #ffffff;
  border: 1px solid #d1d5db;
  color: #4b5563;
}

.btn-cancelar:hover {
  background-color: #f9fafb;
  border-color: #9ca3af;
}

.btn-publicar {
  
  background: linear-gradient(135deg, #00b050 0%, #00d660 100%);
  border: none;
  color: white;
  box-shadow: 0 4px 6px rgba(0, 176, 80, 0.2);
}

.btn-publicar:hover {
  
  background: linear-gradient(135deg, #009945 0%, #00c256 100%);
  box-shadow: 0 6px 8px rgba(0, 176, 80, 0.3);
}

</style>