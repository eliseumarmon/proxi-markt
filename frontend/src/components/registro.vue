<template>
  <div class="form-card">
    <h3>Crear Cuenta</h3>
    <p class="subtitle">Únete a la comunidad de ProxiMarket</p>

    <form @submit.prevent="enviarInfo">
      <label for="nombre">Nombre</label>
      <input type="text" name="nombre" id="nombre" placeholder="Juan García"/><br><br>

      <label for="email">Email</label>
      <input type="email" name="email" id="email" placeholder="tu@gmail.com"/><br><br>

      <label for="contrasenya">Contraseña</label>
      <input type="password" name="contrasenya" id="contrasenya" placeholder="••••••••"/><br><br>

      <label for="telefono">Teléfono</label>
      <input type="text" name="telefono" id="telefono" placeholder="123456789"/><br><br>

      <button type="submit" class="btn-submit">Crear Cuenta</button>
    </form>
  </div>
</template>

<script setup>
  import { ref } from "vue";
  import axios from "axios";

  const nombre = ref("");
  const apellidos = ref("");
  const email = ref("");
  const contrasenya = ref("");
  const telefono = ref("");
  const direccion = ref("");

  const enviarInfo = () => {
    if (!nombre.value || !email.value || !contrasenya.value || !telefono.value) {
      alert("Rellena los campos obligatorios");
      return;
    }

  const info = {
    nombre: nombre.value,
    apellidos: apellidos.value,
    email: email.value,
    contrasenya: contrasenya.value,
    telefono: telefono.value,
    direccion: direccion.value,
  };

  console.log("Enviando registro: ", info);

  axios.post("/api/register", info);
};
</script>

<style scoped>
body, html {
  margin: 0;
  padding: 0;
}

.form-card {
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 35px 30px;
  text-align: left;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  width: 100%;
  max-width: none;
  box-sizing: border-box;
}

h3 {
  font-size: 1.3rem;
  font-weight: 700;
  margin-top: 0;
  margin-bottom: 8px;
  color: #111827;
}

.subtitle {
  color: #6b7280;
  font-size: 0.95rem;
  margin-bottom: 25px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 8px;
  color: #1F2937;
}

input {
  width: 100%;
  padding: 12px 15px;
  background-color: #F3F4F6;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #333;
  outline: none;
  transition: all 0.2s;
  box-sizing: border-box;
}

input:focus {
  background-color: #FFF;
  border-color: #D1D5DB;
  box-shadow: 0 0 0 3px rgba(0, 176, 80, 0.1);
}

input::placeholder {
  color: #9CA3AF;
}

.btn-submit {
  width: 100%;
  padding: 14px;
  background-color: #4CA626;
  color: #FFF;
  font-weight: bold;
  font-size: 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 10px;
  transition: background-color 0.2s;
}

.btn-submit:hover {
  background-color: #009E47;
}
</style>