<script setup>
import ToastViewport from '@/components/ui/ToastViewport.vue';
</script>

<template>
    <router-view />
    <ToastViewport />
</template>

<style>
* {
    margin: 0;
    padding: 0;
}

body {
    min-height: 100vh;
}
</style>
