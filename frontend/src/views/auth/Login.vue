<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import { BriefcaseIcon } from "@heroicons/vue/24/outline";

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: "",
  password: "",
});

const errors = ref({});
const loading = ref(false);

async function handleSubmit() {
  errors.value = {};
  loading.value = true;

  try {
    await authStore.login(form.value);

    window.showToast?.("Login successful!", "success");

    // Redirect based on role
    const user = authStore.user;
    if (user.role === "student" || user.role === "alumni") {
      router.push({ name: "student-dashboard" });
    } else if (user.role === "employer") {
      router.push({ name: "employer-dashboard" });
    } else {
      router.push({ name: "home" });
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      window.showToast?.(
        error.response?.data?.message || "Login failed",
        "error"
      );
    }
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-blue-100 py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full space-y-8">
      <!-- Logo and Title -->
      <div class="text-center">
        <div class="flex justify-center">
          <BriefcaseIcon class="h-16 w-16 text-primary-600" />
        </div>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">
          Welcome back to <span class="text-primary-600">MUNext</span>
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Sign in to your account to continue
        </p>
      </div>

      <!-- Login Form -->
      <div class="card p-8">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              :class="['input mt-1', { 'input-error': errors.email }]"
              placeholder="you@mun.ca"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email[0] }}
            </p>
          </div>

          <!-- Password -->
          <div>
            <label
              for="password"
              class="block text-sm font-medium text-gray-700"
            >
              Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              :class="['input mt-1', { 'input-error': errors.password }]"
              placeholder="••••••••"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password[0] }}
            </p>
          </div>

          <!-- Remember & Forgot Password -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <label for="remember" class="ml-2 block text-sm text-gray-700">
                Remember me
              </label>
            </div>

            <div class="text-sm">
              <a href="#" class="link"> Forgot password? </a>
            </div>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="btn btn-primary w-full btn-lg"
          >
            <span v-if="!loading">Sign in</span>
            <span v-else>Signing in...</span>
          </button>

          <!-- Register Link -->
          <p class="text-center text-sm text-gray-600">
            Don't have an account?
            <RouterLink to="/register" class="link"> Sign up here </RouterLink>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>
