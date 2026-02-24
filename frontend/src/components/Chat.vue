<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from "vue";
import api from "@/api/axios";

// id_receptor es la persona que rep el mensatge
const props = defineProps(['id_receptor', 'id_producto', 'chatid', 'mi_id']);
const mensajes = ref([]);
const nuevoMensaje = ref("");
let polling = null;

const bajarMensajes = ref(null);

//funcio de formatear fecha
const formatearFecha = (fechaRaw) => {
    //si no nia fecha acaba la funcio
    if (!fechaRaw) return "";

    // es crea un objecte tipo fecha per al formato
    const fecha = new Date(fechaRaw);

    // li donem el formato que volem que es mostre
    return new Intl.DateTimeFormat('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(fecha);
};

const hacerScrollAlFinal = async () => {
    //espera a que vue acabe de renderizar els mensatges
    await nextTick();
    //comproba que la referencia al contenedor existix
    if (bajarMensajes.value) {
        //fa el moviment del scroll
        bajarMensajes.value.scrollTo({
            //baixa hasta baix del contenedor de mensatges
            top: bajarMensajes.value.scrollHeight,
            behavior: 'instant' // fa que no hi haja animacio de baixar
        });
    }
};
// obtindre tots els mensatges del chat de les persones
const cargarMensajes = async () => {
    //si no hi ha id de chat para la funcio
    if (!props.chatid) return;
    try {
        const res = await api.get(`/chat/${props.chatid}`);

        //comparar si els mensatges han aumentat
        const mensajesNuevos = res.data.mensajes.length !== mensajes.value.length;

        //actualizem la llista de mensatges
        mensajes.value = res.data.mensajes;

        //si nian mensatges nous baixem el scroll
        if (mensajesNuevos) {
            hacerScrollAlFinal();
        }
    } catch (e) {
        console.error("Error al cargar mensajes:", e);
    }
};

const enviarMensaje = async () => {
    // per a que el mensatge este bonico sense espais inneccesaris
    if (!nuevoMensaje.value.trim()) return;
    try {
        //peticio al backend
        await api.post('/enviar-mensaje', {
            id_chat: props.chatid,   // id del chat actual
            id_vendedor: props.id_receptor, // a qui li arriba el mensatge
            id_producto: props.id_producto, // el producte del que estem parlant
            contenido: nuevoMensaje.value   // el mensatge que senvia
        });

        //netejar el mensatge
        nuevoMensaje.value = "";

        //tornar a carregar els mensatges per a que el nou aparega
        cargarMensajes();
    } catch (e) {
        console.error("Error al enviar mensaje:", e);
    }
};

const iniciarPolling = () => {
    //si ja hi habia un polling funcionant el para
    if (polling) clearInterval(polling);

    //crea un interval y ejecuta la funcio cada 2 segons
    polling = setInterval(() => {
        cargarMensajes();
    }, 2000);
};

//vigila si la variable cambia
watch(() => props.chatid, () => {
    //borra els mensajes vells
    cargarMensajes();
    //para el polling vell i comença uno nou
    iniciarPolling();
});

onMounted(() => {
    cargarMensajes();
    iniciarPolling();
});

// tancar el polling per a que no este tot el rato recargant
// sense que estigues en la pagina del chat
onUnmounted(() => {
    if (polling) clearInterval(polling);
});
</script>

<template>
    <div class="chat-hijo">
        <div class="caja-mensajes" ref="bajarMensajes">
            <div v-for="m in mensajes" :key="m.id"
                :class="['burbuja', m.id_envio === props.mi_id ? 'propio' : 'ajeno']">
                <!-- açi lo que fem es que si el id del que envia el mensatge no es el
                 que ta la sesio inicia es pinta a un costat o al altre -->
                <div class="contenido">{{ m.contenido }}</div>

                <div class="hora">{{ formatearFecha(m.created_at) }}</div>

            </div>
        </div>
        <div class="input-area">
            <input v-model="nuevoMensaje" @keyup.enter="enviarMensaje" placeholder="Escribe algo..." />
            <button @click="enviarMensaje">Enviar</button>
        </div>
    </div>
</template>

<style scoped>
.chat-hijo {
    display: flex;
    flex-direction: column;
    height: 100%;
    background-color: #ffffff;
}

.caja-mensajes {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background-color: #fcfcfc;
}

.burbuja {
    max-width: 70%;
    padding: 10px 15px;
    border-radius: 18px;
    font-size: 0.95rem;
    line-height: 1.4;
    word-wrap: break-word;
}

.propio {
    align-self: flex-end;
    background-color: #4ca626;
    color: white;
    border-bottom-right-radius: 4px;
}

.ajeno {
    align-self: flex-start;
    background-color: #e9e9eb;
    color: #333;
    border-bottom-left-radius: 4px;
}

.input-area {
    padding: 15px 20px;
    background-color: #ffffff;
    border-top: 1px solid #eeeeee;
    display: flex;
    gap: 10px;
    align-items: center;
}

.input-area input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #dddddd;
    border-radius: 25px;
    outline: none;
}

.input-area input:focus {
    border-color: #4ca626;
}

.input-area button {
    background-color: #4ca626;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    font-weight: bold;
    cursor: pointer;
}

.caja-mensajes::-webkit-scrollbar {
    width: 6px;
}

.caja-mensajes::-webkit-scrollbar-thumb {
    background-color: #ddd;
    border-radius: 10px;
}

.burbuja {
    display: flex;
    flex-direction: column;
    position: relative;
}

.hora {
    font-size: 0.7rem;
    margin-top: 4px;
    opacity: 0.7;
    align-self: flex-end;
}

.propio .hora {
    color: #e0e0e0;
}

.ajeno .hora {
    color: #888;
}
</style>