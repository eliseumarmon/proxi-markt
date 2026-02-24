import AuthView from "../views/AuthView.vue";
import { createRouter, createWebHistory } from "vue-router";
import Productos from "../components/Productos.vue";
import Dashboard from "../components/Dashboard.vue";
import Comandas from "../components/Comandas.vue";
import MiCuenta from "../components/MiCuenta.vue";
import Ubicacion from "../components/Ubicacion.vue";
import Publicar from "../components/Publicar.vue";
import Mensaje from "../components/Mensaje.vue";
import Mapa from "../components/Mapa.vue";
import DetalleProducto from "../components/DetalleProducto.vue";
import Principal from "../components/Principal.vue";
import EditarProducto from "../components/EditarProducto.vue";

const routes = [
    {
        path: "/",
        name: "principal",
        component: Principal,
    },
    {
        path: "/auth",
        name: "auth",
        component: AuthView,
    },
    {
        path: "/productos/:id",
        name: "detalle-productos",
        props: true,
        component: DetalleProducto,
    },
    {
        path: "/dashboard",
        name: "dashboard",
        component: Dashboard,
    },
    {
        path: "/ubicacion",
        name: "ubicacion",
        component: Ubicacion,
    },
    {
        path: "/productos",
        name: "productos",
        component: Productos,
    },
    {
        path: "/productos/:id/editar",
        name: "editar_producto",
        component: EditarProducto,
        props: true,
    },
    {
        path: "/mensaje",
        name: "mensaje",
        component: Mensaje,
    },
    {
        path: "/comandas",
        name: "comandas",
        component: Comandas,
    },
    {
        path: "/publicar",
        name: "publicar",
        component: Publicar,
    },
    {
        path: "/cuenta",
        name: "cuenta",
        component: MiCuenta,
    },
    {
        path: "/mapa",
        name: "mapa",
        component: Mapa,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const token = localStorage.getItem('token');

    // definim les rutes que no son privaes
    const publicRoutes = ['/auth', '/'];

    // si la ruta a la que va esta en la llista se li deixa pasar
    if (publicRoutes.includes(to.path)) {
        return true;
    }

    // si no nia token tenvia al login de cap
    if (!token || token === "") {
        return {
            path: '/auth',
            query: { next: to.fullPath }
        };
    }

    return true;
});

export default router;
