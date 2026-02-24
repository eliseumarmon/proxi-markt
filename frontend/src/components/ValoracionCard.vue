<script setup>
import { computed } from 'vue';
import Estrellas from './ValoracionEstrellas.vue';

const props = defineProps({
    resenya: {
        type: Object,
        required: true
    }
});

const fechaFormateada = computed(() => {
    if (!props.resenya.created_at) return '';
    const fecha = new Date(props.resenya.created_at);
    return new Intl.DateTimeFormat('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: '2-digit'
    }).format(fecha);
});

// Extraemos el nombre del producto de forma segura
const nombreProducto = computed(() => {
    return props.resenya.compraventa?.producto?.nombre_producto || 'Producto no disponible';
});
</script>

<template>
    <div class="card-valoracion">
        <div class="fila-superior">
            <div class="usuario-seccion">
                <div class="avatar-mini">
                    {{ resenya.emisor?.nombre_usuario?.charAt(0).toUpperCase() || '?' }}
                </div>
                <div class="meta">
                    <span class="nombre">{{ resenya.emisor?.nombre_usuario }}</span>
                    <span class="fecha">{{ fechaFormateada }}</span>
                </div>
            </div>
            <Estrellas :solo-lectura="true" :model-value="resenya.valoracion" class="estrellas-compactas" />
        </div>

        <div class="cuerpo">
            <p class="comentario">{{ resenya.comentario }}</p>
            <span class="producto-tag">
                <img src="../assets/iconos/productos_stock.png" class="icono-p">
                {{ nombreProducto }}
            </span>
        </div>
    </div>
</template>

<style scoped>
.card-valoracion {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 12px 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}

.fila-superior {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.usuario-seccion {
    display: flex;
    align-items: center;
    gap: 8px;
}

.avatar-mini {
    width: 28px;
    height: 28px;
    background: #f0fdf4;
    color: #4CA626;
    border: 1px solid #B9E2A6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
}

.meta {
    display: flex;
    flex-direction: column;
}

.nombre {
    font-size: 0.85rem;
    font-weight: 600;
    color: #333;
    line-height: 1;
}

.fecha {
    font-size: 0.7rem;
    color: #999;
}

.estrellas-compactas {
    transform: scale(0.85);
    transform-origin: right;
}

.cuerpo {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.comentario {
    font-size: 0.9rem;
    color: #4b5563;
    margin: 0;
    line-height: 1.3;
}

.producto-tag {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    color: #4CA626;
    font-weight: 500;
}

.icono-p {
    width: 14px;
    height: 14px;
    opacity: 0.8;
}
</style>