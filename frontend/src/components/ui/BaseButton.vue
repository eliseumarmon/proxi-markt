<script setup>
defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'danger', 'ghost'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    type: {
        type: String,
        default: 'button',
        validator: (value) => ['button', 'submit', 'reset'].includes(value)
    },
    loading: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    block: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :aria-busy="loading || undefined"
        :class="[
            'base-button',
            `base-button--${variant}`,
            `base-button--${size}`,
            { 'base-button--block': block }
        ]"
    >
        <span v-if="loading" class="base-button__spinner" aria-hidden="true"></span>
        <span v-else-if="$slots['icon-left']" class="base-button__icon" aria-hidden="true">
            <slot name="icon-left" />
        </span>

        <span class="base-button__label">
            <slot />
        </span>

        <span v-if="!loading && $slots['icon-right']" class="base-button__icon" aria-hidden="true">
            <slot name="icon-right" />
        </span>
    </button>
</template>

<style scoped>
.base-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 1px solid transparent;
    border-radius: var(--radius-md);
    font-family: inherit;
    font-weight: 700;
    line-height: 1.2;
    cursor: pointer;
    transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease,
        color 0.2s ease, transform 0.2s ease;
}

.base-button:hover:not(:disabled) {
    transform: translateY(-1px);
}

.base-button:active:not(:disabled) {
    transform: translateY(0);
}

.base-button:focus-visible {
    outline: none;
    box-shadow: var(--focus-ring);
}

.base-button:disabled {
    cursor: not-allowed;
    opacity: 0.58;
}

.base-button--primary {
    background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-accent) 100%);
    color: #fff;
}

.base-button--primary:hover:not(:disabled) {
    background: linear-gradient(90deg, var(--color-primary-dark) 0%, #01683b 100%);
}

.base-button--secondary {
    background: var(--color-surface);
    border-color: #9ca3af;
    color: #4b5563;
}

.base-button--secondary:hover:not(:disabled) {
    background: #f3f4f6;
    border-color: #6b7280;
    color: #374151;
}

.base-button--danger {
    background: #fee2e2;
    border-color: var(--color-danger);
    color: var(--color-danger-dark);
}

.base-button--danger:hover:not(:disabled) {
    background: var(--color-danger);
    color: #fff;
}

.base-button--ghost {
    background: transparent;
    color: var(--color-primary-dark);
}

.base-button--ghost:hover:not(:disabled) {
    background: rgb(76 166 38 / 10%);
}

.base-button--sm {
    min-height: 34px;
    padding: 7px 12px;
    font-size: 0.8125rem;
}

.base-button--md {
    min-height: 42px;
    padding: 10px 18px;
    font-size: 0.9375rem;
}

.base-button--lg {
    min-height: 48px;
    padding: 12px 24px;
    font-size: 1rem;
}

.base-button--block {
    width: 100%;
}

.base-button__spinner {
    width: 1em;
    height: 1em;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: base-button-spin 0.7s linear infinite;
}

.base-button__icon {
    display: inline-flex;
}

@keyframes base-button-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .base-button,
    .base-button__spinner {
        transition: none;
        animation-duration: 1.5s;
    }
}
</style>
