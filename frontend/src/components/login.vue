<template>
  <div id="iniciarSesion">
    <h3>Iniciar Sesión</h3>
    <p>Accede a tu cuenta de ProxiMarket</p>

    <label for="email">Email</label><br>
    <input type="email" name="email" id="email" placeholder="ejemplo@email.com"/><br/>

    <label for="contrasenya">Contraseña</label><br>
    <input type="password" name="contrasenya" id="contrasenya" placeholder="••••••••"><br>
  
    <button type="submit">Iniciar Sesión</button>
  </div>
</template>

<script setup>
  import { ref } from 'vue';
  import axios from 'axios';

  const email = ref('');
  const contrasenya = ref('');

const enviarInfo = () => {
    if (!email.value || !contrasenya.value) {
        alert("Rellena los campos obligatorios");
        return;
    }

    const info = {
        email: email.value,
        contrasenya: contrasenya.value
    };

    console.log("Enviando login:", info);
    
    axios.post('/api/login', info);
}
</script>

<style scoped>
.form-card {
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 30px;
  text-align: left;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  max-width: 450px;
  width: 100%;
  margin: 0 auto;
}

h3 {
  font-size: 1.2rem;
  font-weight: 700;
  margin-bottom: 5px;
  color: #111827;
}

.subtitle {
  color: #6B7280;
  font-size: 0.9rem;
  margin-bottom: 25px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 6px;
  color: #1F2937;
}

input {
  width: 100%;
  padding: 12px 15px;
  background-color: #F3F4F6;
  border: 1px solid transparent;
  border-radius: 6px;
  font-size: 0.95rem;
  color: #333;
  outline: none;
  transition: all 0.2s;
}

input:focus {
  background-color: #fff;
  border-color: #d1d5db;
}

input::placeholder {
  color: #9CA3AF;
}

.btn-submit {
  width: 100%;
  padding: 12px;
  background-color: #00B050;
  color: white;
  font-weight: bold;
  font-size: 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 10px;
  transition: background-color 0.2s;
}

.btn-submit:hover {
  background-color: #009e47;
}
</style>