<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import { EnvelopeIcon } from "@heroicons/vue/24/outline";
import api from "../../services/api";

const router = useRouter();
const authStore = useAuthStore();

const loading = ref(false);
const resending = ref(false);
const message = ref("");

async function resendVerification() {
  resending.value = true;
  message.value = "";

  try {
    // TODO: Implement resend verification endpoint
    window.showToast?.("Verification email sent!", "success");
    message.value =
      "A new verification email has been sent to your email address.";
  } catch (error) {
    window.showToast?.("Failed to resend verification email", "error");
  } finally {
    resending.value = false;
  }
}

function handleSkip() {
  if (authStore.user?.role === "student" || authStore.user?.role === "alumni") {
    router.push({ name: "student-dashboard" });
  } else if (authStore.user?.role === "employer") {
    router.push({ name: "employer-dashboard" });
  } else {
    router.push({ name: "home" });
  }
}
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-blue-100 py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full space-y-8">
      <!-- Icon -->
      <div class="text-center">
        <div class="flex justify-center">
          <div class="bg-primary-100 rounded-full p-6">
            <EnvelopeIcon class="h-16 w-16 text-primary-600" />
          </div>
        </div>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">Verify your email</h2>
        <p class="mt-2 text-sm text-gray-600">
          We've sent a verification link to
          <strong>{{ authStore.user?.email }}</strong>
        </p>
      </div>

      <!-- Content -->
      <div class="card p-8 space-y-6">
        <div class="text-center text-gray-700">
          <p class="mb-4">
            Please check your email and click the verification link to activate
            your account.
          </p>

          <p v-if="message" class="text-sm text-green-600 mb-4">
            {{ message }}
          </p>

          <p class="text-sm text-gray-600">
            Didn't receive the email? Check your spam folder or request a new
            one.
          </p>
        </div>

        <div class="space-y-3">
          <button
            @click="resendVerification"
            :disabled="resending"
            class="btn btn-primary w-full"
          >
            <span v-if="!resending">Resend Verification Email</span>
            <span v-else>Sending...</span>
          </button>

          <button @click="handleSkip" class="btn btn-secondary w-full">
            Skip for now
          </button>
        </div>

        <div class="text-center">
          <RouterLink to="/" class="text-sm link"> Return to home </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
