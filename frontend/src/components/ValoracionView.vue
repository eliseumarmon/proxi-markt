<script setup>
import api from '@/api/axios';
import { ref, onMounted } from 'vue';
import ValoracionCard from './ValoracionCard.vue';

const valoraciones = ref([]);

const obtenerValoraciones = async () => {
    const response = await api.get('/valoraciones');
    valoraciones.value = response.data.data;
}
onMounted(() => obtenerValoraciones());
</script>

<template>
    <div v-if="valoraciones.length == 0" class="card-vacia">
        No valoraciones para mostrar en este momento.
    </div>
    <ValoracionCard v-else v-for="valoracion in valoraciones" :key="valoracion.id" :resenya="valoracion" />
</template>

<style scoped>
.card-vacia {
    background: white;
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    color: #999;
}
</style>