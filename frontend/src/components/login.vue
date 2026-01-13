<template>
  <div id="iniciarSesion" class="form-card">
    <h3>Iniciar Sesión</h3>
    <p class="subtitle">Accede a tu cuenta de ProxiMarkt</p>

    <form @submit.prevent="enviarInfo">
      <div class="form-group">
        <label for="email">Email</label>
        <input v-model="form.email" type="email" name="email" id="email" placeholder="tu@email.com"/>
      </div>

      <div class="form-group">
        <label for="contrasenya">Contraseña</label>
        <input v-model="form.contrasenya" type="password" name="contrasenya" id="contrasenya" placeholder="••••••••"/>
      </div>

      <button type="submit" class="btn-submit">Iniciar Sesión</button>
    </form>
  </div>
</template>

<script setup>
  import { ref } from 'vue';
  import axios from 'axios';
  import { useRouter } from 'vue-router'

  const router = useRouter()

  const form = ref({
    email: "",
    contrasenya:""
  })

  const enviarInfo = async () => {

    const { email, contrasenya } = form.value;

    if (!email || !contrasenya) {
        alert("Rellena los campos obligatorios");
        return;
    }

    console.log("Enviando login:", form.value);
    
    const login = await axios.post('http://localhost:8080/api/login', form.value);

    if(login.status === 200){

      console.log(login.data)

      router.push('/mapa')

      form.value = {
        email: '',
        contrasenya: ''
      }; 
    }
  }
</script>

<style scoped>
* {
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
  color: #6B7280;
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