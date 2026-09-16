import { readonly, reactive } from 'vue';

const MAX_VISIBLE_TOASTS = 3;
const DEFAULT_DURATION = 4000;
const VALID_TYPES = new Set(['success', 'error', 'warning', 'info']);

const state = reactive({
    toasts: []
});

const timers = new Map();
let nextId = 1;

const dismiss = (id) => {
    const index = state.toasts.findIndex((toast) => toast.id === id);

    if (index !== -1) {
        state.toasts.splice(index, 1);
    }

    const timer = timers.get(id);
    if (timer) {
        clearTimeout(timer);
        timers.delete(id);
    }
};

const show = ({ message, type = 'info', title = '', duration = DEFAULT_DURATION }) => {
    if (!message) return null;

    const toast = {
        id: nextId++,
        message: String(message),
        title: title ? String(title) : '',
        type: VALID_TYPES.has(type) ? type : 'info'
    };

    state.toasts.push(toast);

    while (state.toasts.length > MAX_VISIBLE_TOASTS) {
        dismiss(state.toasts[0].id);
    }

    if (duration > 0) {
        timers.set(toast.id, setTimeout(() => dismiss(toast.id), duration));
    }

    return toast.id;
};

export const useToast = () => ({
    toasts: readonly(state.toasts),
    show,
    dismiss,
    success: (message, options = {}) => show({ ...options, message, type: 'success' }),
    error: (message, options = {}) => show({ ...options, message, type: 'error' }),
    warning: (message, options = {}) => show({ ...options, message, type: 'warning' }),
    info: (message, options = {}) => show({ ...options, message, type: 'info' })
});
