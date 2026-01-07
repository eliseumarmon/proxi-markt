<template>
  <div class="login-contenedor" :class="{ 'centrado': eleccionActual === 'login' }">
    <div class="contenido-responsive">
      <div>
        <img src="../assets/logos/logo_gr.png" alt="Logo grande ProxiMarket" class="logo"/>
        <p class="descripcion">Conecta con productores locales y disfruta de productos frescos</p>

        <nav class="nav-eleccion">
          <ul>
            <li>
                <button
                :class="{ active: eleccionActual === 'login' }"
                @click="eleccionActual = 'login'">
                Iniciar Sesión
                </button>
            </li>
            <li>
                <button
                :class="{ active: eleccionActual === 'register' }"
                @click="eleccionActual = 'register'">
                Registrarse
                </button>
            </li>
          </ul>
        </nav>
        
        <div class="form-contenedor">
          <LoginForm v-if="eleccionActual === 'login'" />
          <RegisterForm v-else />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
    import { ref } from 'vue';
    import LoginForm from '../components/login.vue'; 
    import RegisterForm from '../components/registro.vue';

    const eleccionActual = ref('login');
</script>

<style scoped>
body, html {
  margin: 0;
  padding: 0;
}

.login-contenedor {
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #ffffff;
  box-sizing: border-box;
  font-family: "Segoe UI", Helvetica, Arial, sans-serif;
  min-height: 100vh;
  width: 100%;
  padding: 20px;
}

.login-contenedor.centrado {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  padding: 0;
  overflow: hidden;
}

.contenido-responsive {
  width: 100%;
  max-width: 500px;
  text-align: center;
  margin: 0 auto;
}

.logo {
  width: 100%;
  height: 120px;
  object-fit: cover;
  display: block;
  margin-left: auto;
  margin-right: auto;
  margin-bottom: 15px;
  border-radius: 12px;
}

.descripcion {
  color: #6b7280;
  font-size: 1.2rem;
  margin-bottom: 20px;
  line-height: 1.4;
}

.nav-eleccion {
  margin-bottom: 25px;
}

.nav-eleccion ul {
  list-style: none;
  display: flex;
  background-color: #f3f4f6;
  padding: 5px;
  border-radius: 50px;
  border: 1px solid #e5e7eb;
  margin: 0;
}

.nav-eleccion li {
  flex: 1;
  display: flex;
}

.nav-eleccion button {
  flex: 1;
  padding: 10px 0;
  border: none;
  background: transparent;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  border-radius: 50px;
  color: #4b5563;
  transition: all 0.3s ease;
}

.nav-eleccion button.active {
  background-color: white;
  color: #000;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-contenedor {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>