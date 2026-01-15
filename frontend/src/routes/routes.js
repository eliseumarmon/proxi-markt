import login from '../views/login.vue';
import PerfilUsuario from '../components/PerfilUsuario.vue';
import { createRouter, createWebHistory } from 'vue-router';
import publicar from '../components/publicar.vue';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: login
    },
    {
        path: '/mapa',
        name: 'mapa',
        component: PerfilUsuario
    },

    {
        path: '/publicar',
        name: 'publicar',
        component: publicar
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;