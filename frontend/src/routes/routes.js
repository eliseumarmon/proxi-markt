import { createRouter, createWebHistory } from 'vue-router';

import login from '../views/login.vue';
import PerfilUsuario from '../components/PerfilUsuario.vue';
import productos from '../components/productos.vue';
import dashboard from '../components/dashboard.vue';
import miCuenta from '../components/miCuenta.vue'
import Ubicacion from '../components/ubicacion.vue';
import publicar from '../components/publicar.vue'

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
    // }
    // {
    //     path: '/comandas',
    //     name: 'comandas',
    //     component: comandas
    // }
    {
        path: '/publicar',
        name: 'publicar',
        component: publicar
    },
    {
        path: '/cuenta',
        name: 'cuenta',
        component: miCuenta
    },
    {
        path: '/ubicacion',
        name: 'ubicacion',
        component: Ubicacion
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;