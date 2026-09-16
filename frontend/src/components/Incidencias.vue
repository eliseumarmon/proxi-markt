<script setup>
import api from '@/api/axios';
import NavBar from "./NavBar.vue";
import Footer from "./Footer.vue";
import { onMounted, ref } from 'vue';
import { useAuth } from "@/composables/useAuth";
import { useToast } from '@/composables/useToast';
import BaseButton from '@/components/ui/BaseButton.vue';

const { usuario, fetchUsuario } = useAuth();
const mensaje = ref('');
const toast = useToast();

const enviarIncidencia = async () => {
    try {
        const enviar = await api.post('/incidencias', { mensaje: mensaje.value });
        if (enviar.status === 201) {
            toast.success('Incidencia enviada correctamente');
            mensaje.value = '';
        }
    } catch (error) {
        toast.error('No se pudo enviar la incidencia');
    }
};

onMounted(async () => {
    await fetchUsuario();
});
</script>
<template>
    <NavBar />

    <main class="pagina-incidencias">
        <section class="card-incidencia">
            <h1 class="titulo">Reportar incidencia</h1>
            <p class="subtitulo">
                Si has tenido un problema con un pedido o con la plataforma, cuéntanoslo y lo revisamos.
            </p>

            <form class="form-incidencia" @submit.prevent="enviarIncidencia">
                <label for="usuario" class="label-campo">Usuario</label>
                <p id="usuario" class="usuario-actual">{{ usuario?.nombre_usuario || 'Cargando...' }}</p>

                <label for="mensaje" class="label-campo">Mensaje</label>
                <textarea
                    id="mensaje"
                    v-model="mensaje"
                    class="input-mensaje"
                    placeholder="Describe tu incidencia con el máximo detalle posible..."
                    required
                ></textarea>

                <BaseButton type="submit" class="form-action">Enviar incidencia</BaseButton>
            </form>
        </section>
    </main>

    <Footer></Footer>
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", "Arial";
}

.pagina-incidencias {
    min-height: calc(100vh - 160px);
    margin-top: 80px;
    padding: 30px 20px 50px;
    background: linear-gradient(180deg, #F3FAF6 0%, #FFFFFF 100%);
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.card-incidencia {
    width: 100%;
    max-width: 760px;
    background-color: #FFFFFF;
    border: 1px solid #EAEAEA;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    padding: 28px;
}

.titulo {
    font-size: 30px;
    color: #1A202C;
    margin-bottom: 8px;
}

.subtitulo {
    font-size: 15px;
    color: #64748B;
    margin-bottom: 24px;
    line-height: 1.45;
}

.form-incidencia {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.label-campo {
    font-weight: 600;
    color: #334155;
    font-size: 14px;
}

.usuario-actual {
    min-height: 44px;
    border: 1px solid #DCE6DE;
    background-color: #F8FCF9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    padding: 0 14px;
    font-size: 14px;
    color: #334155;
    margin-bottom: 6px;
}

.input-mensaje {
    min-height: 150px;
    resize: vertical;
    border: 1px solid #CFE2D4;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 15px;
    color: #1E293B;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.input-mensaje:focus {
    border-color: #4CA626;
    box-shadow: 0 0 0 3px rgba(76, 166, 38, 0.15);
}

.form-action {
    margin-top: 8px;
    align-self: flex-end;
}

@media (max-width: 767px) {
    .pagina-incidencias {
        padding: 22px 12px 36px;
    }

    .card-incidencia {
        padding: 20px 16px;
        border-radius: 12px;
    }

    .titulo {
        font-size: 24px;
    }

    .form-action {
        width: 100%;
    }
}
</style>
