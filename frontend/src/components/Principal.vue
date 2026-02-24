<script setup>
import { useRouter } from "vue-router";
import NavBar from "./NavBar.vue";
import Footer from "./Footer.vue";
const router = useRouter();

const irAuth = (modo) => {
    const token = localStorage.getItem('token');
    if (!token) {
        router.push({ name: 'auth', state: { modo: modo } });
    } else {
        router.push('/cuenta');
    }
};
</script>

<template>
    <div class="contenedor-principal">
        <header>
            <NavBar />
        </header>
        <section class="seccion-portada">
            <div class="contenido-portada">
                <h1>Conectamos Productores Locales <br> <span class="texto-verde">con Consumidores Conscientes</span>
                </h1>
                <p class="texto-portada">
                    ProxiMarkt es el marketplace líder en productos frescos de proximidad. Una solución profesional para
                    la compra-venta de frutas y verduras de kilómetro cero, con sistema de geolocalización avanzado y
                    gestión integral de pedidos.
                </p>
                <button class="boton-primario" @click="irAuth('register')">Comenzar Ahora</button>
            </div>
        </section>

        <section class="seccion-caracteristicas">
            <h2>¿Por qué elegir <span class="texto-verde">ProxiMarkt?</span></h2>
            <p class="subtitulo-seccion">Tecnología avanzada al servicio del comercio local de productos frescos</p>

            <div class="grid-caracteristicas">
                <div class="tarjeta-caracteristica">
                    <div class="caja-icono icono-verde"><img src="../assets/iconos/ubicacion.png" alt="ubicacion"></div>
                    <h3>Sistema de Proximidad</h3>
                    <p>Algoritmo de geolocalización que permite búsquedas personalizadas con radio configurable de 1 a
                        50 kilómetros. Optimiza tus compras encontrando productos en tu zona.</p>
                </div>

                <div class="tarjeta-caracteristica">
                    <div class="caja-icono icono-azul"><img src="../assets/iconos/manzana.png" alt="manzana"></div>
                    <h3>Trazabilidad Completa</h3>
                    <p>Productos verificados de origen local con información detallada del productor. Garantía de
                        frescura, calidad y sostenibilidad en cada transacción.</p>
                </div>

                <div class="tarjeta-caracteristica">
                    <div class="caja-icono icono-naranja"><img src="../assets/iconos/social.png" alt="personas"></div>
                    <h3>Gestión Profesional</h3>
                    <p>Plataforma integral con sistema de mensajería, gestión de pedidos, valoraciones y panel de
                        control para vendedores y compradores.</p>
                </div>
            </div>
        </section>

        <section class="seccion-como-funciona">
            <h2>Cómo Funciona</h2>
            <p class="subtitulo-seccion">Simple, rápido y directo. Así de fácil es usar ProxiMarkt</p>

            <div class="grid-pasos">
                <div class="paso">
                    <div class="circulo-paso circulo-verde">1</div>
                    <h3>Regístrate Gratis</h3>
                    <p>Crea tu cuenta en segundos y configura tu radio de búsqueda preferido.</p>
                </div>
                <div class="paso">
                    <div class="circulo-paso circulo-azul">2</div>
                    <h3>Explora Productos</h3>
                    <p>Navega por el catálogo de productos frescos disponibles cerca de ti.</p>
                </div>
                <div class="paso">
                    <div class="circulo-paso circulo-naranja">3</div>
                    <h3>Conecta y Compra</h3>
                    <p>Chatea con el vendedor, acuerda la entrega y disfruta de productos frescos.</p>
                </div>
            </div>
        </section>

        <section class="seccion-apoyo-local">
            <div class="contenido-apoyo">
                <div class="sostenible"><img src="../assets/iconos/brote_verde.png" alt="brote"> Sostenibilidad</div>
                <h2>Apoya el <span class="texto-verde">Comercio Local</span></h2>
                <p>Al comprar en ProxiMarkt, estás apoyando directamente a productores locales, reduciendo la huella de
                    carbono y asegurando que tu dinero se quede en tu comunidad.</p>

                <div class="elemento-check">
                    <span class="icono-check"><img src="../assets/iconos/aceptar.png" alt="icono check"></span>
                    <div>
                        <strong>Productos Frescos</strong>
                        <p>Directamente del huerto a tu mesa, máxima frescura garantizada.</p>
                    </div>
                </div>

                <div class="elemento-check">
                    <span class="icono-check"><img src="../assets/iconos/aceptar.png" alt="icono check"></span>
                    <div>
                        <strong>Sin Intermediarios</strong>
                        <p>Precios justos tanto para compradores como para vendedores</p>
                    </div>
                </div>

                <div class="elemento-check">
                    <span class="icono-check"><img src="../assets/iconos/aceptar.png" alt="icono check"></span>
                    <div>
                        <strong>Economía Circular</strong>
                        <p>Fortalece la economía de tu vecindario</p>
                    </div>
                </div>
            </div>

            <div class="imagen-apoyo">
                <img src="../assets/iconos/Mercado.jpeg" alt="Verduras frescas">
                <div class="etiqueta-flotante">
                    <div class="icono-badge-verde">
                        <img src="../assets/logos/logo_blanco.png" alt="icono hoja">
                    </div>
                    <div class="texto-badge">
                        <span class="porcentaje">100%</span>
                        <span class="descripcion">Local y Fresco</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="seccion-llamada">
            <h2>¿Listo para Comenzar?</h2>
            <p>Únete a ProxiMarkt hoy y descubre la mejor manera de comprar y vender productos frescos locales</p>
            <button class="boton-blanco" @click="irAuth('register')">Crear Cuenta Gratis</button>
        </section>
    </div>
    <Footer></Footer>
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', 'Arial';
}

body {
    min-width: 400px;
}

header {
    width: 100%;
    background-color: #FFFFFF;
    box-shadow: 0px 4px 15px -5px #CFCFCF;
    height: 80px;
    display: flex;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
}

.contenedor-principal {
    width: 100%;
    overflow-x: hidden;
}

#nav-contenedor {
    justify-content: center;
    align-items: center;
    width: 100%;
    padding: 0 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;

}

#logo {
    display: grid;
    grid-template-columns: min-content max-content;
    grid-template-rows: auto auto;
    column-gap: 12px;
    align-items: center;
    margin-right: 40px;
}

#logo img {
    grid-row: 1 / 3;
    height: 50px;
    width: auto;
    border-radius: 12px;
}

.titulo {
    grid-column: 2;
    font-size: 25px;
    font-weight: 600;
    color: #4CA626;
    line-height: 1.2;
}

.subtitulo {
    grid-column: 2;
    font-size: 15px;
    color: #757575;
    line-height: 1.2;
}

.boton-iniciosesion {
    background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    color: white;
    font-weight: bold;
    text-decoration: none;
    border: none;
    font-size: 18px;
}

.boton-iniciosesion:hover {
    background: linear-gradient(90deg, #008F4C 0%, rgb(1, 104, 59) 100%);
}

.texto-verde {
    color: #4CA626;
}

.subtitulo-seccion {
    color: #666666;
    margin-bottom: 40px;
    font-size: 16px;
}

.seccion-portada {
    margin-top: 50px;
    text-align: center;
    padding: 80px 20px;
    background: linear-gradient(180deg, #E8F6F0 0%, #FFFFFF 100%);

}

.contenido-portada {
    max-width: 800px;
    margin: 0 auto;
}

.seccion-portada h1 {
    font-size: 42px;
    font-weight: bold;
    color: #1A202C;
    line-height: 1.2;
    margin-bottom: 20px;
}

.texto-portada {
    font-size: 18px;
    color: #4A5568;
    margin-bottom: 30px;
    line-height: 1.6;
}

.boton-primario {
    background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
    color: white;
    border: none;
    padding: 12px 30px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
}

.boton-primario:hover {
    background: linear-gradient(90deg, #008F4C 0%, rgb(1, 104, 59) 100%);
}

.seccion-caracteristicas {
    padding: 60px 20px;
    text-align: center;
    background: white;
}

.seccion-caracteristicas h2 {
    font-size: 32px;
    margin-bottom: 10px;
}

.grid-caracteristicas {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
    max-width: 1200px;
    margin: 0 auto;
}

.tarjeta-caracteristica {
    flex: 1;
    min-width: 300px;
    padding: 30px;
    border-radius: 12px;
    border: 1px solid #EEEEEE;
    text-align: left;
    transition: transform 0.2s;
}

.tarjeta-caracteristica p {
    text-align: justify;
}

.tarjeta-caracteristica:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
}

.caja-icono {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 24px;
    margin-bottom: 20px;
}

.caja-icono img {
    width: 40px;
    height: 40px;
}

.icono-verde {
    background-color: #E6F7EF;
}

.icono-azul {
    background-color: #E6F0FF;
}

.icono-naranja {
    background-color: #FFF4E6;
}

.tarjeta-caracteristica h3 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #2D3748;
}

.tarjeta-caracteristica p {
    font-size: 14px;
    color: #718096;
    line-height: 1.5;
}

.seccion-como-funciona {
    padding: 60px 20px;
    text-align: center;
    background-color: #FFFFFF;
}

.seccion-como-funciona h2 {
    font-size: 32px;
    color: #1A202C;
    margin-bottom: 10px;
}

.grid-pasos {
    display: flex;
    justify-content: center;
    gap: 50px;
    flex-wrap: wrap;
    max-width: 1000px;
    margin: 40px auto 0;
}

.paso {
    flex: 1;
    min-width: 250px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.circulo-paso {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
}

.circulo-verde {
    background-color: #E6F7EF;
    color: #4CA626;
}

.circulo-azul {
    background-color: #E6F0FF;
    color: #0066FF;
}

.circulo-naranja {
    background-color: #FFF4E6;
    color: #FF9900;
}

.paso h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.paso p {
    font-size: 14px;
    color: #718096;
    max-width: 250px;
}

.seccion-apoyo-local {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 50px;
    max-width: 1200px;
    margin: 80px auto;
    padding: 0 20px;
}

.contenido-apoyo {
    flex: 1;
}

.imagen-apoyo {
    flex: 1;
    position: relative;
    display: flex;
    justify-content: center;
}

.imagen-apoyo img {
    width: 100%;
    max-width: 500px;
    height: 400px;
    object-fit: cover;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.etiqueta-flotante {
    position: absolute;
    bottom: 30px;
    left: -30px;
    background-color: #FFFFFF;
    padding: 12px 20px;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 10;
    min-width: 180px;
}

.icono-badge-verde {
    background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.icono-badge-verde img {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.texto-badge {
    display: flex;
    flex-direction: column;
}

.texto-badge .porcentaje {
    font-size: 20px;
    font-weight: 700;
    color: #4CA626;
    line-height: 1;
}

.texto-badge .descripcion {
    font-size: 13px;
    color: #5F6368;
    font-weight: 600;
}

.sostenible {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: #E6F7EF;
    color: #4CA626;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 15px;
}

.sostenible img {
    width: 20px;
    height: 20px;
}

.contenido-apoyo h2 {
    font-size: 32px;
    margin-bottom: 20px;
    line-height: 1.2;
}

.contenido-apoyo p {
    color: #718096;
    line-height: 1.6;
    margin-bottom: 20px;
}

.elemento-check {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.elemento-check img {
    width: 25px;
    height: 25px;
    background-color: #E6F7EF;
    border-radius: 50%;
}

.elemento-check strong {
    display: block;
    color: #2D3748;
    margin-bottom: 2px;
}

.elemento-check p {
    font-size: 13px;
    margin: 0;
}

.seccion-llamada {
    padding: 80px 20px;
    background: linear-gradient(90deg, #4CA626 0%, #009B58 100%);
    color: white;
    text-align: center;
}

.seccion-llamada h2 {
    font-size: 32px;
    margin-bottom: 15px;
}

.seccion-llamada p {
    font-size: 16px;
    margin-bottom: 30px;
}

.boton-blanco {
    background-color: white;
    color: #4CA626;
    border: none;
    padding: 12px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: transform 0.2s;
}

.boton-blanco:hover {
    transform: scale(1.05);
}
</style>