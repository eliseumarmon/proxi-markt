import login from '../views/login.vue';
import PerfilUsuario from '../components/PerfilUsuario.vue';
import productos from '../components/productos.vue';
import dashboard from '../components/dashboard.vue';
import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: login
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: dashboard
    },
    {
        path: '/productos',
        name: 'productos',
        component: productos
    },
    {
        path: '/mapa',
        name: 'mapa',
        component: PerfilUsuario
    },
    // {
    //     path: '/mensajes',
    //     name: 'mensajes',
    //     component: mensajes
    // },
    // {
    //     path: '/comandas',
    //     name: 'comandas',
    //     component: comandas
    // },
    // {
    //     path: '/publicar',
    //     name: 'publicar',
    //     component: publicar
    // },
    // {
    //     path: '/cuenta',
    //     name: 'cuenta',
    //     component: miCuenta
    // }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;