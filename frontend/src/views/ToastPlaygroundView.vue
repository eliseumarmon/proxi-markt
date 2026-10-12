<script setup>
import { computed, reactive } from 'vue';
import { RouterLink } from 'vue-router';
import BaseButton from '@/components/ui/BaseButton.vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const form = reactive({
    type: 'success',
    title: '',
    message: 'Este es un mensaje de prueba para revisar la notificación.',
    duration: 4000
});

const activeCount = computed(() => toast.toasts.length);

const showCustomToast = () => {
    toast.show({
        type: form.type,
        title: form.title.trim(),
        message: form.message.trim(),
        duration: Number(form.duration)
    });
};

const showStack = () => {
    toast.success('El producto se ha guardado correctamente.');
    toast.warning('El stock de este producto está a punto de agotarse.');
    toast.info('Tienes una conversación pendiente de respuesta.');
};

const testQueueLimit = () => {
    toast.info('Primera notificación: será retirada al superar el límite.', { duration: 0 });
    toast.success('Segunda notificación.', { duration: 0 });
    toast.warning('Tercera notificación.', { duration: 0 });
    toast.error('Cuarta notificación: deben permanecer visibles las tres últimas.', { duration: 0 });
};

const dismissAll = () => {
    [...toast.toasts].forEach(({ id }) => toast.dismiss(id));
};
</script>

<template>
    <main class="playground">
        <header class="playground__header">
            <div>
                <span class="playground__eyebrow">Entorno de desarrollo</span>
                <h1>Laboratorio de toasts</h1>
                <p>
                    Comprueba variantes, contenido, duración, cola y comportamiento responsive sin depender de la API.
                </p>
            </div>

            <RouterLink class="playground__back" to="/">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Volver a la aplicación
            </RouterLink>
        </header>

        <section class="playground__status" aria-live="polite">
            <div>
                <span class="playground__status-label">Notificaciones visibles</span>
                <strong>{{ activeCount }} / 3</strong>
            </div>
            <BaseButton variant="ghost" size="sm" :disabled="activeCount === 0" @click="dismissAll">
                <template #icon-left>
                    <i class="bi bi-trash3" aria-hidden="true"></i>
                </template>
                Cerrar todas
            </BaseButton>
        </section>

        <div class="playground__grid">
            <section class="test-card">
                <div class="test-card__header">
                    <span class="test-card__number">01</span>
                    <div>
                        <h2>Variantes</h2>
                        <p>Revisa icono, color, texto y botón de cierre.</p>
                    </div>
                </div>

                <div class="button-grid">
                    <BaseButton @click="toast.success('Producto guardado correctamente.')">
                        <template #icon-left><i class="bi bi-check-circle" aria-hidden="true"></i></template>
                        Éxito
                    </BaseButton>

                    <BaseButton variant="danger" @click="toast.error('No se pudo completar la operación.')">
                        <template #icon-left><i class="bi bi-x-circle" aria-hidden="true"></i></template>
                        Error
                    </BaseButton>

                    <BaseButton variant="secondary" @click="toast.warning('Revisa los campos obligatorios.')">
                        <template #icon-left><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></template>
                        Aviso
                    </BaseButton>

                    <BaseButton variant="ghost" @click="toast.info('La información se ha actualizado.')">
                        <template #icon-left><i class="bi bi-info-circle" aria-hidden="true"></i></template>
                        Información
                    </BaseButton>
                </div>
            </section>

            <section class="test-card">
                <div class="test-card__header">
                    <span class="test-card__number">02</span>
                    <div>
                        <h2>Contenido</h2>
                        <p>Prueba títulos y saltos de línea en mensajes largos.</p>
                    </div>
                </div>

                <div class="test-card__actions">
                    <BaseButton
                        block
                        @click="toast.success('La publicación ya está disponible para compradores cercanos.', { title: 'Producto publicado' })"
                    >
                        Toast con título
                    </BaseButton>

                    <BaseButton
                        block
                        variant="secondary"
                        @click="toast.info('Este es un mensaje deliberadamente largo para comprobar cómo se distribuye el contenido cuando ocupa varias líneas en pantallas estrechas y cómo se mantiene alineado el icono con el bloque de texto.')"
                    >
                        Mensaje largo
                    </BaseButton>
                </div>
            </section>

            <section class="test-card">
                <div class="test-card__header">
                    <span class="test-card__number">03</span>
                    <div>
                        <h2>Cola</h2>
                        <p>El viewport admite un máximo de tres notificaciones.</p>
                    </div>
                </div>

                <div class="test-card__actions">
                    <BaseButton block @click="showStack">Mostrar tres simultáneas</BaseButton>
                    <BaseButton block variant="secondary" @click="testQueueLimit">
                        Probar límite con cuatro
                    </BaseButton>
                </div>
            </section>

            <section class="test-card">
                <div class="test-card__header">
                    <span class="test-card__number">04</span>
                    <div>
                        <h2>Duración</h2>
                        <p>Compara el cierre automático con una notificación persistente.</p>
                    </div>
                </div>

                <div class="test-card__actions">
                    <BaseButton block @click="toast.success('Desaparecerá automáticamente en cuatro segundos.')">
                        Duración normal
                    </BaseButton>
                    <BaseButton
                        block
                        variant="secondary"
                        @click="toast.warning('Permanecerá visible hasta que la cierres.', { title: 'Notificación persistente', duration: 0 })"
                    >
                        Sin cierre automático
                    </BaseButton>
                </div>
            </section>
        </div>

        <section class="custom-card">
            <div class="test-card__header">
                <span class="test-card__number">05</span>
                <div>
                    <h2>Prueba personalizada</h2>
                    <p>Configura exactamente el caso visual que quieras revisar.</p>
                </div>
            </div>

            <form class="custom-form" @submit.prevent="showCustomToast">
                <div class="form-field">
                    <label for="toast-type">Variante</label>
                    <select id="toast-type" v-model="form.type">
                        <option value="success">Éxito</option>
                        <option value="error">Error</option>
                        <option value="warning">Aviso</option>
                        <option value="info">Información</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="toast-duration">Duración en milisegundos</label>
                    <input id="toast-duration" v-model.number="form.duration" type="number" min="0" step="500" />
                    <small>Usa 0 para mantenerla visible.</small>
                </div>

                <div class="form-field custom-form__full">
                    <label for="toast-title">Título opcional</label>
                    <input id="toast-title" v-model="form.title" type="text" placeholder="Ejemplo: Producto publicado" />
                </div>

                <div class="form-field custom-form__full">
                    <label for="toast-message">Mensaje</label>
                    <textarea id="toast-message" v-model="form.message" rows="3" required></textarea>
                </div>

                <BaseButton type="submit" class="custom-form__submit">
                    <template #icon-left><i class="bi bi-play-fill" aria-hidden="true"></i></template>
                    Mostrar prueba
                </BaseButton>
            </form>
        </section>

        <aside class="checklist">
            <h2>Qué revisar</h2>
            <ul>
                <li>Alineación vertical del icono, el texto y el cierre.</li>
                <li>Contraste de las cuatro variantes.</li>
                <li>Textos largos a 320 y 375 píxeles de ancho.</li>
                <li>Foco visible y cierre con teclado.</li>
                <li>Recolocación fluida de la cola al cerrar un elemento.</li>
                <li>Desaparición automática alrededor de los cuatro segundos.</li>
            </ul>
        </aside>
    </main>
</template>

<style scoped>
.playground {
    min-height: 100vh;
    padding: 48px 24px 72px;
    background:
        radial-gradient(circle at top left, rgb(76 166 38 / 12%), transparent 34rem),
        linear-gradient(180deg, #f8fcf9 0%, #f8fafc 100%);
    color: var(--color-text);
}

.playground__header,
.playground__status,
.playground__grid,
.custom-card,
.checklist {
    width: min(1100px, 100%);
    margin-inline: auto;
}

.playground__header {
    display: flex;
    justify-content: space-between;
    gap: 32px;
    align-items: flex-start;
    margin-bottom: 28px;
}

.playground__eyebrow {
    display: inline-block;
    margin-bottom: 8px;
    color: var(--color-primary-dark);
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.playground h1 {
    margin: 0 0 10px;
    font-size: clamp(2rem, 5vw, 3.25rem);
    line-height: 1.05;
}

.playground__header p {
    max-width: 700px;
    margin: 0;
    color: var(--color-text-muted);
    line-height: 1.6;
}

.playground__back {
    display: inline-flex;
    flex: 0 0 auto;
    gap: 8px;
    align-items: center;
    min-height: 42px;
    padding: 10px 16px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-surface);
    color: var(--color-text);
    font-weight: 700;
    text-decoration: none;
}

.playground__back:focus-visible {
    outline: none;
    box-shadow: var(--focus-ring);
}

.playground__status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
    padding: 14px 16px;
    border: 1px solid #dce8df;
    border-radius: var(--radius-lg);
    background: rgb(255 255 255 / 82%);
    box-shadow: 0 8px 24px rgb(15 23 42 / 6%);
}

.playground__status > div {
    display: flex;
    gap: 10px;
    align-items: baseline;
}

.playground__status-label {
    color: var(--color-text-muted);
    font-size: 0.875rem;
}

.playground__status strong {
    color: var(--color-primary-dark);
    font-size: 1.1rem;
}

.playground__grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.test-card,
.custom-card,
.checklist {
    border: 1px solid var(--color-border);
    border-radius: 16px;
    background: var(--color-surface);
    box-shadow: 0 10px 30px rgb(15 23 42 / 6%);
}

.test-card {
    padding: 24px;
}

.test-card__header {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    margin-bottom: 22px;
}

.test-card__number {
    display: inline-grid;
    flex: 0 0 auto;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgb(76 166 38 / 12%);
    color: var(--color-primary-dark);
    font-size: 0.75rem;
    font-weight: 800;
}

.test-card h2,
.custom-card h2,
.checklist h2 {
    margin: 0 0 5px;
    font-size: 1.1rem;
}

.test-card p,
.custom-card p {
    margin: 0;
    color: var(--color-text-muted);
    font-size: 0.875rem;
    line-height: 1.45;
}

.button-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.test-card__actions {
    display: grid;
    gap: 10px;
}

.custom-card {
    margin-top: 20px;
    padding: 24px;
}

.custom-form {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.form-field {
    display: grid;
    gap: 7px;
}

.form-field label {
    font-size: 0.85rem;
    font-weight: 700;
}

.form-field input,
.form-field select,
.form-field textarea {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: var(--radius-md);
    background: #fff;
    color: var(--color-text);
    font: inherit;
}

.form-field input,
.form-field select {
    min-height: 44px;
    padding: 9px 12px;
}

.form-field textarea {
    min-height: 92px;
    padding: 12px;
    resize: vertical;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: var(--focus-ring);
}

.form-field small {
    color: var(--color-text-muted);
}

.custom-form__full {
    grid-column: 1 / -1;
}

.custom-form__submit {
    justify-self: start;
}

.checklist {
    margin-top: 20px;
    padding: 24px;
}

.checklist ul {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px 32px;
    margin: 14px 0 0;
    padding-left: 20px;
    color: var(--color-text-muted);
    line-height: 1.45;
}

.checklist li::marker {
    color: var(--color-primary);
}

@media (max-width: 760px) {
    .playground {
        padding: 32px 16px 56px;
    }

    .playground__header {
        flex-direction: column;
        gap: 20px;
    }

    .playground__grid,
    .custom-form,
    .checklist ul {
        grid-template-columns: 1fr;
    }

    .custom-form__full {
        grid-column: auto;
    }
}

@media (max-width: 420px) {
    .playground__status {
        align-items: stretch;
        flex-direction: column;
    }

    .button-grid {
        grid-template-columns: 1fr;
    }

    .test-card,
    .custom-card,
    .checklist {
        padding: 18px;
    }
}
</style>
