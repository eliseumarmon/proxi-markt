<script setup>
import { reactive } from 'vue';
import { ref } from 'vue';
import axios from "axios";
import { useRouter } from "vue-router";
// import navbar from "./nav.vue";

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
    datos.append('stock_real', form.stock_total);
    datos.append('stock_reserva', 0);
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
    <h1>Publicar Producto</h1><br>
    <p>Comparte tus frutas y verduras frescasc con la comunidad</p><br><br>

    <form @sumbit.prevent="enviar">
        <label for="Nombre producto">Nombre del producto</label>
        <input type="text" id="nom" name="nom" v-model="form.nombre_producto">

        <label for="descr">Descripcion:</label>
        <input type="text" name="descr" id="descr" v-model="form.descripcion">

        <label for="precio">Precio (€)</label>
        <input type="number" name="precio" id="precio" v-model="form.precio">

        <label for="stock">Stock:</label>
        <input type="number" name="stock" id="stock" v-model="form.stock_total">

        <label for="cat">Cateoría:</label>
        <select id="cat" v-model="categoria">
            <option value="" disabled="Selecciona una opción"></option>

            <option v-for="cat in listaCategorias" :key="cat.id" :value="cat.id">
                {{ cat.nombre }}
            </option>
        </select>

        <label for="punto">Punto de entrega:</label>
        <input type="text" name="punto" id="punto">

        <label for="imagen">Imagen del producto (opcional)</label>
        <input type="file" name="imagen" id="imagen" accept="image/*" @change="cogerImagen">

        <button type="button" onclick="history.back()">Cancelar</button>
        <button type="submit">Publicar producto</button>
    </form>
</template>

<style scoped>
    
</style>