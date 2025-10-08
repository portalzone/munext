<script setup>
import { ref } from "vue";
import {
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XMarkIcon,
} from "@heroicons/vue/24/outline";

const toasts = ref([]);
let toastId = 0;

function show(message, type = "info", duration = 3000) {
  const id = toastId++;
  const toast = { id, message, type };
  toasts.value.push(toast);

  if (duration > 0) {
    setTimeout(() => {
      remove(id);
    }, duration);
  }

  return id;
}

function remove(id) {
  const index = toasts.value.findIndex((t) => t.id === id);
  if (index > -1) {
    toasts.value.splice(index, 1);
  }
}

function getIcon(type) {
  switch (type) {
    case "success":
      return CheckCircleIcon;
    case "error":
      return XCircleIcon;
    case "warning":
      return ExclamationTriangleIcon;
    default:
      return InformationCircleIcon;
  }
}

function getColorClasses(type) {
  switch (type) {
    case "success":
      return "bg-green-50 text-green-800 border-green-200";
    case "error":
      return "bg-red-50 text-red-800 border-red-200";
    case "warning":
      return "bg-yellow-50 text-yellow-800 border-yellow-200";
    default:
      return "bg-blue-50 text-blue-800 border-blue-200";
  }
}

// Expose show method globally
if (typeof window !== "undefined") {
  window.showToast = show;
}

defineExpose({ show, remove });
</script>

<template>
  <div class="fixed top-4 right-4 z-50 space-y-2">
    <transition-group
      enter-active-class="transition ease-out duration-300"
      enter-from-class="transform translate-x-full opacity-0"
      enter-to-class="transform translate-x-0 opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="transform translate-x-0 opacity-100"
      leave-to-class="transform translate-x-full opacity-0"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'flex items-center p-4 rounded-lg shadow-lg border min-w-[300px] max-w-md',
          getColorClasses(toast.type),
        ]"
      >
        <component
          :is="getIcon(toast.type)"
          class="h-6 w-6 mr-3 flex-shrink-0"
        />
        <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>
        <button
          @click="remove(toast.id)"
          class="ml-3 flex-shrink-0 hover:opacity-70 transition-opacity"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>
    </transition-group>
  </div>
</template>
