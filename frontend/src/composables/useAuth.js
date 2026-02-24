import { ref, computed } from "vue";
import api from "@/api/axios";

const _usuario = ref(null);
const _loading = ref(false);
const _error = ref(null);
const _token = ref(localStorage.getItem("token"));

export const useAuth = () => {
    const usuario = computed(() => _usuario.value);
    const loading = computed(() => _loading.value);
    const error = computed(() => _error.value);
    const estarAutenticado = computed(() => !!_token.value);
    
    const setLoading = (estado) => _loading.value = estado;

    const fetchUsuario = async () => {
        if (!_token.value) return;

        _loading.value = true;
        _error.value = null;

        try {
            const response = await api.get("/datosuser");
            _usuario.value = response.data;
        } catch (err) {
            _error.value = err.response?.data?.message || "Error de conexión";
            console.error(err);
        } finally {
            _loading.value = false;
        }
    };

    const login = (nuevoToken, datosUsuario) => {
        localStorage.setItem('token', nuevoToken);
        _token.value = nuevoToken;
        _usuario.value = datosUsuario;
    }

    const logout = () => {
        localStorage.removeItem("token");
        _usuario.value = null;
        _token.value = null;
    };

    return {
        usuario,
        loading,
        error,
        setLoading,
        login,
        logout,
        fetchUsuario,
        estarAutenticado
    };
};
