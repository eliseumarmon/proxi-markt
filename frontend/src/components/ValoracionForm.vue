<script setup>
import { reactive } from 'vue';
import ValoracionEstrellas from './ValoracionEstrellas.vue';

const emit = defineEmits(['enviar-valoracion', 'cerrar']);

const form = reactive({
    valoracion: 0,
    comentario: ''
});

const enviarValoracion = () => {
    emit('enviar-valoracion', {
        valoracion: form.valoracion,
        comentario: form.comentario
    });
}
</script>

<template>
    <div class="modal-backdrop-custom">
        <div class="modal-dialog modal-dialog-centered shadow">
            <div class="modal-content custom-card border-0">

                <div class="modal-body p-4 my-4 text-center">
                    <form @submit.prevent="enviarValoracion">
                        <h5 class="mb-3 fw-bold text-uppercase color-corporativo">
                            Valorar Comanda
                        </h5>

                        <div class="mb-2 text-start">
                            <label class="form-label d-block mb-1 small fw-bold color-corporativo opacity-75">
                                PUNTUACIÓN
                            </label>
                            <div class="d-block">
                                <ValoracionEstrellas v-model="form.valoracion" :solo-lectura="false" />
                            </div>
                        </div>

                        <div class="mb-4 text-start">
                            <label for="comentario" class="form-label small fw-bold color-corporativo opacity-75">
                                MENSAJE
                            </label>
                            <textarea v-model="form.comentario" id="comentario" class="form-control custom-textarea"
                                rows="2" placeholder="Opcional..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-confirmar py-2 fw-bold" :disabled="!form.valoracion">
                                Enviar
                            </button>
                            <button type="button" class="btn btn-volver py-2 fw-bold" @click="$emit('cerrar')">
                                Volver
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    font-family: 'Clear Sans', sans-serif;
}

.custom-card {
    background-color: #F0F0F0;
    border-radius: 12px;
    width: 500px;
}

.color-corporativo {
    color: #4CA626;
}

.custom-textarea {
    background-color: #FFFFFF;
    border: 1px solid #B9E2A6;
    border-radius: 8px;
    font-size: 0.9rem;
    resize: none;
}

.btn-confirmar {
    background-color: #4CA626;
    color: #FFFFFF;
    border-radius: 8px;
    border: none;
}

.btn-volver {
    background-color: #B9E2A6;
    color: #4CA626;
    border-radius: 8px;
    border: none;
    font-size: 0.85rem;
}

.btn-confirmar:hover {
    background-color: #3d861e;
    color: white;
}

.btn-volver:hover {
    background-color: #8BD16A;
    color: white;
}
</style>
