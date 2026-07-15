<script setup>
import { storageUrl } from "@/utils/storage";
const props = defineProps({
    productos: {
        type: Array,
        required: true,
        default: () => []
    },
    pagination: {
        type: Object
    },
    paginaActual: {
        type: Number
    }
});

const emit = defineEmits(['borrar', 'cambiarPagina']);

const eliminarproducto = (id) => {
    emit('borrar', id)
}

const cambiarpagina = (pagina) => {
    emit('cambiarPagina', pagina);
}
</script>

<template>
    <div class="contenedor-seccion-productos">
        <div v-if="productos && productos.length > 0" class="grid-productos">
            <div v-for="producto in productos" :key="producto.id" class="card-producto">
                <div class="imagen-contenedor">
                    <img :src="producto.imagen ? storageUrl(producto.imagen) : 'https://via.placeholder.com/150'"
                        alt="Imagen producto">
                    <span class="badge-estado" :class="producto.estado">{{ producto.estado }}</span>
                </div>

                <div class="detalles-producto">
                    <h4>{{ producto.nombre_producto }}</h4>
                    <p class="precio">{{ producto.precio }}€</p>
                    <div class="meta-info">
                        <span><img src="../assets/iconos/stock.png" alt="caja-icono" class="icono"> Stock: {{
                            producto.stock_total }}</span>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 me-3 mb-2">
                    <router-link :to="{ name: 'editar_producto', params: { id: producto.id } }" class="btn btn-success"
                        title="Editar producto">
                        Editar
                    </router-link>
                    <button @click="eliminarproducto(producto.id)" class="btn btn-danger" title="Eliminar producto">
                        Eliminar
                    </button>
                </div>

            </div>
        </div>

        <div v-else class="card-vacia">
            No hay productos para mostrar en este momento.
        </div>
    </div>
    <div v-if="pagination.last_page > 1" class="paginacion">
        <button :disabled="paginaActual === 1" @click="cambiarpagina(paginaActual - 1)"> Anterior </button>

        <span>Página {{ paginaActual }} de {{ pagination.last_page }}</span>
        <button :disabled="paginaActual === pagination.last_page" @click="cambiarpagina(paginaActual + 1)"> Siguiente
        </button>
    </div>
</template>

<style scoped>
.meta-info span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.icono {
    width: 25px;
    height: 25px;
}

.grid-productos {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    width: 100%;
}

.card-producto {
    background: white;
    border: 1px solid #eee;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s;
}

.imagen-contenedor {
    position: relative;
    height: 140px;
}

.imagen-contenedor img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.badge-estado {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: bold;
}

.badge-estado.disponible {
    background: #dcfce7;
    color: #166534;
}

.detalles-producto {
    padding: 15px;
}

.precio {
    font-size: 1.2rem;
    font-weight: bold;
    color: #4CA626;
}

.card-vacia {
    background: white;
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    color: #999;
}

.btn-accion {
    padding: 6px 12px;
    margin: 3px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
}

.btn-editar {
    background-color: #f0fdf4;
    color: #166534;
    border-color: #bbf7d0;
}

.btn-editar:hover {
    background-color: #4CA626;
    color: white;
}

.btn-eliminar {
    background-color: #fef2f2;
    color: #991b1b;
    border-color: #fecaca;
}

.btn-eliminar:hover {
    background-color: #ef4444;
    color: white;
}

.paginacion {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin: 20px 0px 40px 0px;
    padding: 10px;
}

.paginacion button {
    padding: 8px 16px;
    border-radius: 5px;
    border: 1px solid #ccc;
    background: white;
    cursor: pointer;
}

.paginacion button:disabled {
    background: #eee;
    cursor: not-allowed;
}
</style>
