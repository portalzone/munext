<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import {
  BriefcaseIcon,
  UserIcon,
  BuildingOfficeIcon,
  AcademicCapIcon,
} from "@heroicons/vue/24/outline";

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  role: "student",
});

const errors = ref({});
const loading = ref(false);

const roles = [
  {
    value: "student",
    label: "Student",
    icon: AcademicCapIcon,
    description: "Current MUN student",
  },
  {
    value: "alumni",
    label: "Alumni",
    icon: UserIcon,
    description: "MUN graduate",
  },
  {
    value: "employer",
    label: "Employer",
    icon: BuildingOfficeIcon,
    description: "Looking to hire",
  },
];

async function handleSubmit() {
  errors.value = {};
  loading.value = true;

  try {
    await authStore.register(form.value);

    window.showToast?.("Registration successful!", "success");

    // Redirect based on role
    const user = authStore.user;
    if (user.role === "student" || user.role === "alumni") {
      router.push({ name: "student-profile" });
    } else if (user.role === "employer") {
      router.push({ name: "employer-profile" });
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      window.showToast?.(
        error.response?.data?.message || "Registration failed",
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
    <div class="max-w-2xl w-full space-y-8">
      <!-- Logo and Title -->
      <div class="text-center">
        <div class="flex justify-center">
          <BriefcaseIcon class="h-16 w-16 text-primary-600" />
        </div>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">
          Join <span class="text-primary-600">MUNext</span>
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Create your account to get started
        </p>
      </div>

      <!-- Registration Form -->
      <div class="card p-8">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Role Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">
              I am a...
            </label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <button
                v-for="role in roles"
                :key="role.value"
                type="button"
                @click="form.role = role.value"
                :class="[
                  'p-4 border-2 rounded-lg text-left transition-all',
                  form.role === role.value
                    ? 'border-primary-600 bg-primary-50'
                    : 'border-gray-200 hover:border-gray-300',
                ]"
              >
                <component
                  :is="role.icon"
                  class="h-8 w-8 mb-2"
                  :class="
                    form.role === role.value
                      ? 'text-primary-600'
                      : 'text-gray-400'
                  "
                />
                <div class="font-semibold text-gray-900">{{ role.label }}</div>
                <div class="text-xs text-gray-500 mt-1">
                  {{ role.description }}
                </div>
              </button>
            </div>
          </div>

          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
              Full Name
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              :class="['input mt-1', { 'input-error': errors.name }]"
              placeholder="John Doe"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
              {{ errors.name[0] }}
            </p>
          </div>

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
              :placeholder="
                form.role === 'employer' ? 'you@company.com' : 'you@mun.ca'
              "
            />
            <p
              v-if="form.role !== 'employer'"
              class="mt-1 text-xs text-gray-500"
            >
              Students and alumni must use their @mun.ca email address
            </p>
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email[0] }}
            </p>
          </div>

          <!-- Password -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

            <div>
              <label
                for="password_confirmation"
                class="block text-sm font-medium text-gray-700"
              >
                Confirm Password
              </label>
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                required
                :class="[
                  'input mt-1',
                  { 'input-error': errors.password_confirmation },
                ]"
                placeholder="••••••••"
              />
              <p
                v-if="errors.password_confirmation"
                class="mt-1 text-sm text-red-600"
              >
                {{ errors.password_confirmation[0] }}
              </p>
            </div>
          </div>

          <!-- Terms -->
          <div class="flex items-start">
            <input
              id="terms"
              type="checkbox"
              required
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded mt-1"
            />
            <label for="terms" class="ml-2 block text-sm text-gray-700">
              I agree to the
              <a href="#" class="link">Terms of Service</a>
              and
              <a href="#" class="link">Privacy Policy</a>
            </label>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="btn btn-primary w-full btn-lg"
          >
            <span v-if="!loading">Create Account</span>
            <span v-else>Creating Account...</span>
          </button>

          <!-- Login Link -->
          <p class="text-center text-sm text-gray-600">
            Already have an account?
            <RouterLink to="/login" class="link"> Sign in here </RouterLink>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>
