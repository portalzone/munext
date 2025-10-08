<script setup>
import { ExclamationTriangleIcon } from "@heroicons/vue/24/outline";
</script>

<template>
  <div class="flex items-center justify-center min-h-screen px-4 bg-gray-50">
    <div class="text-center">
      <ExclamationTriangleIcon class="w-24 h-24 mx-auto mb-6 text-gray-400" />
      <h1 class="mb-4 text-6xl font-bold text-gray-900">404</h1>
      <h2 class="mb-4 text-2xl font-semibold text-gray-700">Page Not Found</h2>
      <p class="max-w-md mx-auto mb-8 text-gray-600">
        The page you're looking for doesn't exist or has been moved.
      </p>
      <div class="flex flex-col justify-center gap-4 sm:flex-row">
        <RouterLink to="/" class="btn btn-primary"> Go to Homepage </RouterLink>
        <button @click="$router.back()" class="btn btn-secondary">
          Go Back
        </button>
      </div>
    </div>
  </div>
</template>
