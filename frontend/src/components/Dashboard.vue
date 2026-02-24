<script setup>
import { ref, onMounted, computed } from "vue";
import api from "@/api/axios";
import { useAuth } from '@/composables/useAuth';
import NavBar from "./NavBar.vue";
import Footer from "./Footer.vue";

const productosUser = ref([]);
const misCompras = ref([]);
const misVentas = ref([]);
const { usuario, fetchUsuario } = useAuth();

const stockTotal = computed(() => {
    return productosUser.value.reduce((total, p) => total + (p.stock_total || 0), 0);
});

const ingresos = computed(() => {
    return ventasCompletadas.value.reduce((total, v) => {
        return total + (v.cantidad * (v.producto?.precio || 0));
    }, 0);
});

// sols està carregant els productes de la pàgina 1
// data.data perque Laravel esta retornant un objecte de paginacio
// que en este cas carrega fins a 7 productes
const cargarProductosUser = async () => {
    try {
        const response = await api.get(`/usuarios/${usuario.value?.id}/productos`);
        productosUser.value = response.data.data;
        // console.log(response.data.data);
    } catch (error) {
        console.error("Error cargando productos:", error);
    }
};

const obtenerCompras = async () => {
    const response = await api.get('/mis-compras');
    misCompras.value = response.data.data;
};

const obtenerVentas = async () => {
    const response = await api.get('/mis-ventas');
    misVentas.value = response.data.data;
};

const comprasCompletadas = computed(() => {
    return misCompras.value.filter(c => c.estado === 'completado');
});

const ventasCompletadas = computed(() => {
    return misVentas.value.filter(v => v.estado === 'completado');
});

const productosOrdenados = computed(() => {
    return [...productosUser.value].sort((a, b) => a.id - b.id);
});

const cargarDatosDashboard = async () => {
    try {
        const response = await api.get('/dashboard'); 
        
        productosUser.value = response.data.productos;
        misVentas.value = response.data.ventas;
        misCompras.value = response.data.compras;
        
    } catch (error) {
        console.error("Error cargando el dashboard:", error);
    }
};

onMounted(async () => {
    await fetchUsuario();
    if (usuario.value?.id) {
        await cargarDatosDashboard();
    }
});
</script>

<template>
    <NavBar />
    <div class="contenedor-pagina">
        <div id="contenedor-titulo">
            <h1 class="titulo">Dashboard</h1>
            <p class="subtitulo">Resumen de tu actividad en ProxiMarkt</p>
        </div>

        <div class="cajas-informacion-uno">
            <div class="caja">
                <h3>Mis productos</h3>
                <p>{{ productosUser.length }}</p>
                <img src="../assets/iconos/brote.png" class="icono" />
            </div>

            <div class="caja">
                <h3>Stock total</h3>
                <p>{{ stockTotal }}</p>
                <img src="../assets/iconos/ingresos.png" />
            </div>

            <div class="caja">
                <h3>Mis Ventas</h3>
                <p>{{ ventasCompletadas.length }}</p>
                <img src="../assets/iconos/info.png" />
            </div>

            <div class="caja">
                <h3>Ingresos</h3>
                <p>{{ ingresos.toFixed(2) }}€</p>
                <img src="../assets/iconos/euro.png" />
            </div>
        </div>

        <div class="cajas-informacion-dos">
            <div class="ventas">
                <img src="../assets/iconos/carrito.png" class="icono" />
                <h3>Mis Ventas</h3>

                <div v-if="ventasCompletadas.length > 0" class="lista-scroll">
                    <div class="producto-ventas" v-for="venta in ventasCompletadas" :key="venta.id">

                        <img :src="venta.producto?.imagen ? `http://localhost:8080/storage/${venta.producto.imagen}` : 'https://via.placeholder.com/150'"
                            class="imagen-producto">

                        <p id="nombre-producto">{{ venta.producto?.nombre_producto || 'Producto eliminado' }}</p>

                        <p id="precio">{{ (venta.cantidad * (venta.producto?.precio || 0)).toFixed(2) }}€</p>

                        <p id="info">{{ venta.comprador?.nombre_usuario || 'Usuario desconocido' }}</p>
                        <p id="estado">{{ venta.estado }}</p>
                    </div>
                </div>
                <p class="no-producto" v-else>No has vendido nada aún.</p>
            </div>

            <div class="productos">
                <img src="../assets/iconos/disponibles.png" class="icono" />
                <h3>Productos disponibles</h3>

                <div v-if="productosUser.length > 0" class="lista-scroll">
                    <div class="producto-disponible" v-for="producto in productosOrdenados" :key="producto.id">
                        <img :src="producto.imagen ? `http://localhost:8080/storage/${producto.imagen}` : 'https://via.placeholder.com/150'"
                            class="imagen-producto">
                        <p id="nombre-producto">{{ producto.nombre_producto }}</p>
                        <p id="precio-producto">{{ producto.precio }}€</p>
                        <p id="stock-disponible">{{ producto.stock_total }} disponibles</p>
                    </div>
                </div>
                <p class="no-producto" v-else>No has publicado ningún producto aún.</p>
            </div>
        </div>

        <div class="cajas-informacion-tres">
            <div class="productos">
                <img src="../assets/iconos/stock.png" class="icono" />
                <h3>Mis Compras</h3>

                <div v-if="comprasCompletadas.length > 0" class="lista-scroll">
                    <div class="compras-producto" v-for="compra in comprasCompletadas" :key="compra.id">
                        <img :src="compra.producto?.imagen ? `http://localhost:8080/storage/${compra.producto.imagen}` : 'https://via.placeholder.com/150'"
                            class="imagen-producto">

                        <p id="nombre-producto">{{ compra.producto?.nombre_producto || 'Producto eliminado' }}</p>
                        
                        <p id="info">{{ compra.vendedor?.nombre_usuario || 'Vendedor desconocido' }}</p>
                        <p id="estado">{{ compra.estado }}</p>
                        <p id="precio">{{ (compra.cantidad * (compra.producto?.precio || 0)).toFixed(2) }}€</p>
                    </div>
                </div>
                <p class="no-producto" v-else>No has comprado nada aún.</p>
            </div>
        </div>
    </div>
    <Footer></Footer>
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", "Arial";
}

body {
    min-width: 400px;
}

.contenedor-pagina {
    margin-top: 80px;
    padding: 20px 50px;
}

#contenedor-titulo {
    max-width: 90%;
    margin: 10px auto 0 auto;
}

.titulo {
    font-family: sans-serif;
    color: #4CA626;
    margin-bottom: 10px;
    font-weight: bold;
}

.subtitulo {
    font-family: sans-serif;
    color: #666666;
    margin-bottom: 20px;
}

.cajas-informacion-uno,
.cajas-informacion-dos,
.cajas-informacion-tres {
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
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.caja h3 {
    font-size: 20px;
    opacity: 0.9;
    z-index: 2;
}

.caja p {
    font-size: 28px;
    font-weight: bold;
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
    background: linear-gradient(to left, #009E5B, #20C97E);
}

.cajas-informacion-uno .caja:nth-child(2) {
    background: linear-gradient(to left, #1060C4, #4FACFE);
}

.cajas-informacion-uno .caja:nth-child(3) {
    background: linear-gradient(to left, #D95000, #FF8C42);
}

.cajas-informacion-uno .caja:nth-child(4) {
    background: linear-gradient(to left, #801AC0, #B845FC);
}

.cajas-informacion-dos,
.cajas-informacion-tres {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.ventas,
.productos {
    background: white;
    border-radius: 12px;
    padding: 20px;
    flex: 1;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid #EEEEEE;
}

.ventas h3,
.productos h3 {
    display: inline-block;
    vertical-align: middle;
    margin: 0;
    margin-bottom: 20px;
    font-size: 18px;
    color: #333333;
}

.ventas .icono,
.productos .icono {
    display: inline-block;
    vertical-align: middle;
    width: 30px;
    margin-right: 10px;
    margin-bottom: 20px;
}

.producto-disponible,
.compras-producto,
.producto-ventas {
    display: grid;
    grid-template-columns: auto 1fr auto;
    grid-template-rows: auto auto;
    gap: 0px 15px;
    padding: 15px;
    background-color: #F9FAFB;
    border-radius: 12px;
    margin-bottom: 10px;
    align-items: center;
}

.producto-disponible .imagen-producto,
.compras-producto .imagen-producto,
.producto-ventas .imagen-producto {
    grid-column: 1;
    grid-row: 1 / 3;
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
}

.producto-disponible #nombre-producto,
.compras-producto #nombre-producto,
.producto-ventas #nombre-producto {
    grid-column: 2;
    grid-row: 1;
    font-weight: bold;
    color: #333333;
    font-size: 15px;
    margin-bottom: 2px;
    align-self: end;
}

.producto-disponible #info,
.compras-producto #info,
.producto-ventas #info {
    grid-column: 2;
    grid-row: 2;
    font-size: 13px;
    color: #64748B;
    align-self: start;
}

.producto-disponible #precio-producto,
.compras-producto #precio,
.producto-ventas #precio {
    grid-column: 3;
    grid-row: 1;
    text-align: right;
    font-weight: bold;
    color: #333333;
    font-size: 15px;
    align-self: end;
}

.producto-disponible #stock-disponible,
.compras-producto #estado,
.producto-ventas #estado {
    grid-column: 3;
    grid-row: 2;
    text-align: right;
    font-size: 12px;
    align-self: start;
}

.producto-disponible #stock-disponible {
    color: #64748B;
}

.compras-producto #estado {
    background: #E0F8E9;
    color: #00B86B;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: bold;
    margin-top: 2px;
}

.producto-ventas #estado {
    background: #E0F8E9;
    color: #00B86B;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: bold;
    margin-top: 2px;
}

.no-producto {
    padding: 15px;
    color: grey;
}

.lista-scroll {
    max-height: 300px;
    overflow-y: auto;
    padding-right: 5px;
}

.lista-scroll::-webkit-scrollbar {
    width: 8px;
}

.lista-scroll::-webkit-scrollbar-track {
    background: #F1F1F1;
    border-radius: 4px;
}

.lista-scroll::-webkit-scrollbar-thumb {
    background: #CCCCCC;
    border-radius: 4px;
}

.lista-scroll::-webkit-scrollbar-thumb:hover {
    background: #AAAAAA;
}

@media (max-width: 768px) {
    .contenedor-pagina {
        padding: 20px; 
    }

    #contenedor-titulo {
        max-width: 100%;
    }

    .cajas-informacion-uno,
    .cajas-informacion-dos,
    .cajas-informacion-tres {
        flex-direction: column;
        max-width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    .caja img {
        width: 60px;
        padding: 8px;
    }
}
</style> 