<template>
    <navbar></navbar>
    <div>HOLA MUNDO</div>
</template>

<script setup>
  import { ref } from "vue";
  import axios from "axios";
  import { useRouter } from "vue-router";
  import navbar from "./nav.vue";

  const router = useRouter();

</script>

<style>
body {
  min-width: 400px;
  padding-top: 100px;
}

@media (max-width: 768px) {
  body {
    padding-top: 150px; 
  }
}
</style>