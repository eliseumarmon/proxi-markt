import { createApp } from 'vue';
import App from './App.vue';
import router from './routes/routes.js';
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'leaflet/dist/leaflet.css'

const app = createApp(App);

app.use(router);
app.mount('#app');