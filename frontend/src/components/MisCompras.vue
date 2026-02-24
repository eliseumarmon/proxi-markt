<script setup>
import api from '@/api/axios';
import { onMounted, ref } from 'vue';

const props = defineProps({
    eleccion: {
        type: String,
        required: true
    }
});

const mostrar = ref([]);
// guarda tots els datos que mos pasa laravel
const pagination = ref({});
// el numero de pagina que estem veguent
const paginaActual = ref(1);

const misCompras = async (pagina = 1) => {
    const response = await api.get('/mis-compras', {
        params: {
            page: pagina
        }
    });
    mostrar.value = response.data.data;
    pagination.value = response.data;
    paginaActual.value = response.data.current_page;

    // console.log("compras: ", mostrar.value)
    // console.log("paginacion", pagination.value)
}

const formatearFecha = (fechaRaw) => {
    if (!fechaRaw) return 'Sin fecha';

    const fecha = new Date(fechaRaw);
    return new Intl.DateTimeFormat('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(fecha);
};

onMounted(() => misCompras())
</script>

<template>
    <div v-if="mostrar && mostrar.length > 0">
        <div v-for="elemento in mostrar" :key="elemento.id" class="elemento-item">

            <div class="imagen-contenedor">
                <img :src="elemento.producto.imagen ? `http://localhost:8080/storage/${elemento.producto.imagen}` : 'https://via.placeholder.com/150'"
                    alt="Imagen producto">
            </div>

            <div class="info-contenedor">
                <h3>{{ elemento.producto.nombre_producto }}</h3>

                <span class="badge-estado">{{ elemento.estado }}</span>

                <p class="detalle"><img src="../assets/iconos/stock.png" alt="" class="icono"> Cantidad: {{
                    elemento.cantidad }}</p>
                <p class="detalle"><img src="../assets/iconos/calendario.png" alt="" class="icono"> Recogida: {{
                    formatearFecha(elemento.fecha_prevista) }}</p>
                <p class="detalle">
                    <img src="../assets/iconos/mi_cuenta_verde.png" alt="" class="icono">
                    {{ props.eleccion === 'ventas' ? 'Comprador: ' : 'Vendedor: ' }}
                    {{ props.eleccion === 'ventas' ? elemento.id_comprador?.nombre_usuario :
                        elemento.vendedor?.nombre_usuario }}
                </p>
            </div>

        </div>
    </div>
    <div v-else class="card-vacia">
        No hay compras para mostrar en este momento.
    </div>
    <!-- Paginacion -->
    <!-- Soles mostra la paginacio si hi ha mes de una pagina -->
    <div v-if="pagination.last_page > 1" class="paginacion">
        <!-- El disabled lo que fa es bloquejar el 
             boto si esta en la pagina indica -->
        <button :disabled="paginaActual === 1"
            @click="props.eleccion === 'ventas' ? misVentas(paginaActual - 1) : misCompras(paginaActual - 1)"> Anterior
        </button>

        <span>Página {{ paginaActual }} de {{ pagination.last_page }}</span>

        <!-- El @click suma 1 o resta 1 al numero de 
             la pagina actual per a cambiar -->
        <button :disabled="paginaActual === pagination.last_page"
            @click="props.eleccion === 'ventas' ? misVentas(paginaActual + 1) : misCompras(paginaActual + 1)"> Siguiente
        </button>
    </div>
</template>

<style scoped>
.elemento-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    margin-bottom: 10px;
    font-family: sans-serif;
}

.imagen-contenedor {
    position: relative;
    width: 80px;
    height: 80px;
}

.imagen-contenedor img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}

/* El contenido textual */
.info-contenedor {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-contenedor h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #333;
}

.badge-estado {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    border: 1px solid #ccc;
    width: fit-content;
    margin-bottom: 5px;
}

.detalle {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #666;
    margin: 0;
}

.icono {
    width: 25px;
    height: 25px;
}

.card-vacia {
    background: white;
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    color: #999;
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