<script setup>
import { onMounted } from "vue";
import { useAuthStore } from "./stores/auth";
import Navbar from "./components/layout/Navbar.vue";
import Footer from "./components/layout/Footer.vue";
import Toast from "./components/common/Toast.vue";

const authStore = useAuthStore();

onMounted(async () => {
  // Initialize auth state from token
  const token = localStorage.getItem("token");
  if (token && !authStore.user) {
    await authStore.fetchUser();
  }
});
</script>

<template>
  <div id="app" class="min-h-screen flex flex-col">
    <Navbar />

    <main class="flex-grow">
      <RouterView />
    </main>

    <Footer />

    <Toast />
  </div>
</template>

<style scoped>
/* Additional app-level styles if needed */
</style>
