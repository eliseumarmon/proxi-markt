<template>
    <div id="crearCuenta">
        <h3>Crear Cuenta</h3>
        <p>Únete a la comunidad de ProxiMarket</p>

        <div id="nombreCompleto">
            <label for="nombre">Nombre</label><br>
            <input type="text" name="nombre" id="nombre" placeholder="Juan">
        
            <label for="apellidos">Apellidos</label><br>
            <input type="text" name="apellidos" id="apellidos" placeholder="García">
        </div>

        <label for="email">Email</label><br>
        <input type="email" name="email" id="email" placeholder="ejemplo@gmail.com"><br>

        <label for="contrasenya">Contraseña</label><br>
        <input type="password" name="contrasenya" id="contrasenya" placeholder="••••••••"><br>

        <label for="telefono">Teléfono</label><br>
        <input type="text" name="telefono" id="telefono" placeholder="123456789"><br>

        <label for="direccion">Dirección</label><br>
        <input type="text" name="direccion" id="direccion" placeholder="Calle Ejemplo 123"><br>

        <button type="submit">Crear Cuenta</button>
    </div>
</template>

<script setup>
  import { ref } from 'vue';
  import axios from 'axios';

  const nombre = ref('');
  const apellidos = ref('');
  const email = ref('');
  const contrasenya = ref('');
  const telefono = ref('');
  const direccion = ref('');

  const enviarInfo = () => {
    if (!nombre.value || !email.value || !contrasenya.value || !telefono.value){
      alert("Rellena los campos obligatorios");
      return;
    }

    const info = {
      nombre: nombre.value,
      apellidos: apellidos.value,
      email: email.value,
      contrasenya: contrasenya.value,
      telefono: telefono.value,
      direccion: direccion.value
    };

    console.log("Enviando registro: ", info);

    axios.post('/api/register', info);
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

.row-split {
  display: flex;
  gap: 15px;
}

.form-group.half {
  flex: 1;
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
  box-sizing: border-box;
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