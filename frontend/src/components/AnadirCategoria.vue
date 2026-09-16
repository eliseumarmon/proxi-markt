<script setup>
import api from '@/api/axios';
import { ref } from 'vue';
import NavBar from './NavBar.vue';
import Footer from "./Footer.vue";
import { useToast } from '@/composables/useToast';
import BaseButton from '@/components/ui/BaseButton.vue';

const nombrecategoria = ref('');

const toast = useToast();

const nuevacategoria = async () => {
    try {
        const respuesta = await api.post('/categorias', {
            nombre_categoria: nombrecategoria.value
        });
        if (respuesta.status === 201) {
            toast.success('Categoría insertada correctamente');
            nombrecategoria.value = '';
        }
    } catch (error) {
        toast.error(error.response?.data?.message || 'No se pudo insertar la categoría');
    }
};
</script>

<template>
    <NavBar />

    <main class="pagina-categoria">
        <section class="card-categoria">
            <h1 class="titulo">Anadir categoria</h1>
            <p class="subtitulo">Crea una nueva categoria para clasificar productos.</p>

            <form class="form-categoria" @submit.prevent="nuevacategoria">
                <label for="categoria" class="label-campo">Nombre de la nueva categoria</label>
                <input
                    id="categoria"
                    v-model="nombrecategoria"
                    type="text"
                    class="input-categoria"
                    placeholder="Ejemplo: Verduras de temporada"
                    required
                />

                <BaseButton type="submit" class="form-action">Guardar categoría</BaseButton>
            </form>
        </section>
    </main>

    <Footer />
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", "Arial";
}

.pagina-categoria {
    min-height: calc(100vh - 160px);
    margin-top: 80px;
    padding: 30px 20px 50px;
    background: linear-gradient(180deg, #F3FAF6 0%, #FFFFFF 100%);
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.card-categoria {
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

.form-categoria {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.label-campo {
    font-weight: 600;
    color: #334155;
    font-size: 14px;
}

.input-categoria {
    min-height: 44px;
    border: 1px solid #CFE2D4;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 15px;
    color: #1E293B;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.input-categoria:focus {
    border-color: #4CA626;
    box-shadow: 0 0 0 3px rgba(76, 166, 38, 0.15);
}

.form-action {
    margin-top: 8px;
    align-self: flex-end;
}

@media (max-width: 767px) {
    .pagina-categoria {
        padding: 22px 12px 36px;
    }

    .card-categoria {
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
