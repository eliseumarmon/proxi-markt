<script setup>
import { useToast } from '@/composables/useToast';

const { toasts, dismiss } = useToast();

const icons = {
    success: 'bi-check-circle-fill',
    error: 'bi-x-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    info: 'bi-info-circle-fill'
};
</script>

<template>
    <Teleport to="body">
        <div class="toast-viewport" aria-live="polite" aria-atomic="false">
            <TransitionGroup name="toast" tag="div" class="toast-viewport__list">
                <article
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="['toast-message', `toast-message--${toast.type}`]"
                    :role="toast.type === 'error' ? 'alert' : 'status'"
                >
                    <i :class="['bi', icons[toast.type], 'toast-message__icon']" aria-hidden="true"></i>

                    <div class="toast-message__content">
                        <strong v-if="toast.title" class="toast-message__title">{{ toast.title }}</strong>
                        <p class="toast-message__text">{{ toast.message }}</p>
                    </div>

                    <button
                        type="button"
                        class="toast-message__close"
                        aria-label="Cerrar notificación"
                        @click="dismiss(toast.id)"
                    >
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </article>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-viewport {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 99999;
    width: min(390px, calc(100vw - 32px));
    pointer-events: none;
}

.toast-viewport__list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.toast-message {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    gap: 12px;
    align-items: center;
    padding: 14px 14px 14px 16px;
    border-left: 4px solid currentColor;
    border-radius: var(--radius-lg);
    background: var(--color-surface);
    color: var(--color-text);
    box-shadow: var(--shadow-toast);
    pointer-events: auto;
}

.toast-message--success {
    color: var(--color-primary-dark);
}

.toast-message--error {
    color: var(--color-danger-dark);
}

.toast-message--warning {
    color: #9a6700;
}

.toast-message--info {
    color: var(--color-info);
}

.toast-message__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.25rem;
    height: 1.25rem;
    font-size: 1.15rem;
    line-height: 1;
}

.toast-message__content {
    min-width: 0;
}

.toast-message__title,
.toast-message__text {
    color: var(--color-text);
}

.toast-message__title {
    display: block;
    margin-bottom: 2px;
    font-size: 0.9rem;
}

.toast-message__text {
    margin: 0;
    font-size: 0.875rem;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.toast-message__close {
    display: inline-grid;
    place-items: center;
    width: 30px;
    height: 30px;
    margin-right: -5px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: var(--color-text-muted);
    cursor: pointer;
}

.toast-message__close:hover {
    background: #f1f5f9;
    color: var(--color-text);
}

.toast-message__close:focus-visible {
    outline: none;
    box-shadow: var(--focus-ring);
}

.toast-enter-active,
.toast-leave-active,
.toast-move {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(16px);
}

@media (max-width: 575px) {
    .toast-viewport {
        right: 16px;
        bottom: 16px;
        left: 16px;
        width: auto;
    }
}

@media (prefers-reduced-motion: reduce) {
    .toast-enter-active,
    .toast-leave-active,
    .toast-move {
        transition: none;
    }
}
</style>
