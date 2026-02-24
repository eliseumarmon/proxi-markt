<script setup>
import { ref } from 'vue';

const props = defineProps({
    soloLectura: { type: Boolean, required: true },
    modelValue: { type: Number, required: false, default: 0 },
    maxValoracion: { type: Number, required: false, default: 5 }
});

const emit = defineEmits(['update:modelValue']);
const hoverEstrella = ref(0);

const enviarPuntuacion = (estrella) => {
    emit('update:modelValue', estrella);
}

const obtenerClaseEstrella = (estrella) => {
    const valorActual = hoverEstrella.value || props.modelValue;

    if (estrella <= valorActual) {
        return 'bi-star-fill'; // Estrella llena
    } else if (estrella - 0.5 <= valorActual) {
        return 'bi-star-half'; // Media estrella
    } else {
        return 'bi-star'; // Estrella vacía
    }
}
</script>

<template>
    <div class="d-flex">
        <i v-for="estrella in maxValoracion" :key="estrella" :class="[
            'bi',
            obtenerClaseEstrella(estrella),
            { 'pointer': !soloLectura }
        ]" 
        @mouseenter="!soloLectura && (hoverEstrella = estrella)"
        @mouseleave="!soloLectura && (hoverEstrella = 0)" 
        @click="!soloLectura && enviarPuntuacion(estrella)" />
    </div>
</template>

<style scoped>
.bi {
    font-size: 1.25em;
    color: #FFD700;
    transition: transform 0.2s ease, filter 0.2s;
}

.pointer {
    cursor: pointer;
}

.pointer:hover {
    filter: brightness(1.2);
    transform: scale(1.2);
}
</style>