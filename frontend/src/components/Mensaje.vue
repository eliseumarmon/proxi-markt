<script setup>
import { ref, onMounted, computed, onUnmounted } from "vue";
import api from "@/api/axios";
import { useAuth } from '@/composables/useAuth';
import NavBar from "./NavBar.vue";
import ChatDetalle from "./Chat.vue";
import Footer from "./Footer.vue";

const chats = ref([]);
const chatSeleccionadoId = ref(null);
const { usuario, fetchUsuario } = useAuth();
let intervaloChat = ref(null);

const chatActivo = computed(() => {
    return chats.value.find(c => c.id === chatSeleccionadoId.value);
});

const idReceptorDinamico = computed(() => {
    if (!chatActivo.value || !usuario.value?.id) return null;
    return chatActivo.value.id_vendedor === usuario.value.id
        ? chatActivo.value.id_comprador
        : chatActivo.value.id_vendedor;
});

const hayNotificacionesGlobales = computed(() => {
    if (!chats.value) return false;
    return chats.value.some(chat => chat.mensajes_no_leidos > 0);
});

// --- API CHATS ---
const obtenerChats = async () => {
    try {
        const respuesta = await api.get('/mis-chats');
        chats.value = respuesta.data;
    } catch (error) {
        console.error("Error obteniendo chats:", error);
    }
}

// --- NUEVA FUNCIÓN: SELECCIONAR Y MARCAR COMO LEÍDO ---
const seleccionarChat = async (chatId) => {
    // 1. Cambiamos la selección visualmente
    chatSeleccionadoId.value = chatId;

    // 2. Buscamos el chat en la lista
    const chat = chats.value.find(c => c.id === chatId);

    // 3. Si tiene mensajes sin leer, los limpiamos
    if (chat && chat.mensajes_no_leidos > 0) {
        // Truco visual: quitar el punto rojo inmediatamente
        chat.mensajes_no_leidos = 0;

        try {
            // Avisar a Laravel
            await api.put(`/chats/${chatId}/leer`);
        } catch (e) {
            console.error("Error marcando leído:", e);
        }
    }
}

onMounted(async () => {
    await fetchUsuario();
    if (usuario.value?.id) {
        obtenerChats();
        // Auto-refresco cada 3 segundos para ver si llegan mensajes nuevos
        intervaloChat.value = setInterval(obtenerChats, 3000);
    }
});

onUnmounted(() => {
    if (intervaloChat.value) clearInterval(intervaloChat);
});
</script>

<template>
    <NavBar :tiene-notificacion="hayNotificacionesGlobales" />

    <div class="contenedor-pagina">
        <div id="contenedor-titulo">
            <h1 class="titulo">Mensajes</h1>
            <p class="subtitulo">Conversaciones con compradores y vendedores</p>
        </div>
        <div id="layout-chat">

            <div class="lista-chats">
                <div v-for="chat in chats" :key="chat.id" @click="seleccionarChat(chat.id)"
                    :class="['item-chat', { activo: chatSeleccionadoId === chat.id }]">

                    <div v-if="chat.mensajes_no_leidos > 0" class="punto-rojo"></div>

                    <h3>
                        {{ chat.id_vendedor === usuario.id ? chat.comprador?.nombre_usuario :
                            chat.vendedor.nombre_usuario }}
                    </h3>
                    <p>{{ chat.producto.nombre_producto }}</p>
                </div>
            </div>

            <div class="ventana-mensajes">
                <!-- si es selecciona un chat i el usuari esta logejat enviem al component fill el qui el recibix, el producte, y el id de la persona logejada -->
                <ChatDetalle v-if="chatActivo && usuario?.id" :id_receptor="idReceptorDinamico"
                    :id_producto="chatActivo.id_producto" :chatid="chatActivo.id" :mi_id="usuario.id" />
                <div v-else class="vacio">
                    <p>Selecciona una conversación</p>
                </div>
            </div>
        </div>
    </div>
    <Footer></Footer>
</template>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", "Arial";
}

body {
    min-width: 400px;
}

.contenedor-pagina {
    padding: 20px 50px;
    padding-top: 130px;
    min-height: 100vh; 
    height: auto;
    box-sizing: border-box;
    background-color: #f5f5f5;
    padding-bottom: 40px; 
}

#contenedor-titulo {
    max-width: 90%;
    margin: 10px auto 0 auto;
}

.titulo {
    font-family: sans-serif;
    color: #4CA626;
    margin-bottom: 10px;
    font-weight: bold;
}

.subtitulo {
    font-family: sans-serif;
    color: #666666;
    margin-bottom: 20px;
}

#layout-chat {
    display: grid;
    grid-template-columns: 350px 1fr;
    height: 80vh;
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    max-width: 90%;
    margin: 20px auto;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.lista-chats {
    border-right: 1px solid #f0f0f0;
    overflow-y: auto;
    background: #fdfdfd;
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.item-chat {
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: white;
    position: relative;
}

.punto-rojo {
    position: absolute;
    top: 10px;
    right: 10px;
    min-width: 22px;
    height: 22px;

    padding: 0 4px;
    background-color: #ff3b30;
    color: white;
    font-size: 11px;
    font-weight: bold;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    z-index: 10;
}

.item-chat:hover {
    background-color: #f9f9f9;
    border-color: #ddd;
}

.item-chat.activo {
    background-color: #4ca626;
    border-color: #4ca626;
    color: white;
}

.item-chat h3 {
    margin: 0 0 5px 0;
    font-size: 1rem;
}

.item-chat p {
    margin: 0;
    font-size: 0.85rem;
    opacity: 0.8;
}

.ventana-mensajes {
    display: flex;
    flex-direction: column;
    background: #ffffff;
    height: 100%;
    overflow: hidden;
    position: relative;
}

.vacio {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #999;
}

@media (max-width: 768px) {
    .contenedor-pagina {
        padding: 20px 10px;
        padding-top: 100px;
    }

    #contenedor-titulo, 
    #layout-chat {
        max-width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    #layout-chat {
        grid-template-columns: 130px 1fr;
        height: 75vh;
    }

    .lista-chats {
        padding: 10px 5px;
        gap: 8px;
    }

    .item-chat {
        padding: 10px 8px;
    }

    .item-chat h3 {
        font-size: 0.85rem;
        margin-bottom: 3px;
        word-wrap: break-word;
    }

    .item-chat p {
        font-size: 0.75rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .punto-rojo {
        top: 5px;
        right: 5px;
        min-width: 18px;
        height: 18px;
        font-size: 10px;
    }
}
</style>