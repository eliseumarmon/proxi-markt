<script setup>
    import { useRouter } from 'vue-router'

    const router = useRouter()

    const irALogin = () => {
        router.push('/login')
    }

    const irARegistro = () => {
        router.push('/register')
    }
</script>
<template>
    <div class="elementos">
        <h2>Bienvenido a Proximarket</h2>
        <div class="botones">
            <button @click="irALogin">Login</button>
            <button @click="irARegistro">Registrarse</button>
        </div>
    </div>

</template>
