import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "../services/api";
import router from "../router";

export const useAuthStore = defineStore("auth", () => {
  const user = ref(null);
  const token = ref(localStorage.getItem("token"));
  const loading = ref(false);
  const error = ref(null);

  const isAuthenticated = computed(() => !!token.value && !!user.value);
  const isStudent = computed(
    () => user.value?.role === "student" || user.value?.role === "alumni"
  );
  const isEmployer = computed(() => user.value?.role === "employer");
  const isAdmin = computed(() => user.value?.role === "admin");

  async function register(credentials) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.post("/auth/register", credentials);

      if (response.data.success) {
        token.value = response.data.data.token;
        user.value = response.data.data.user;
        localStorage.setItem("token", token.value);
        return response.data;
      }
    } catch (err) {
      error.value = err.response?.data?.message || "Registration failed";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function login(credentials) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.post("/auth/login", credentials);

      if (response.data.success) {
        token.value = response.data.data.token;
        user.value = response.data.data.user;
        localStorage.setItem("token", token.value);
        return response.data;
      }
    } catch (err) {
      error.value = err.response?.data?.message || "Login failed";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    loading.value = true;

    try {
      await api.post("/auth/logout");
    } catch (err) {
      console.error("Logout error:", err);
    } finally {
      user.value = null;
      token.value = null;
      localStorage.removeItem("token");
      loading.value = false;
      router.push({ name: "login" });
    }
  }

  async function fetchUser() {
    if (!token.value) return;

    loading.value = true;

    try {
      const response = await api.get("/auth/me");

      if (response.data.success) {
        user.value = response.data.data;
      }
    } catch (err) {
      console.error("Fetch user error:", err);
      // Only clear token if it's a 401 (Unauthorized)
      if (err.response?.status === 401) {
        token.value = null;
        user.value = null;
        localStorage.removeItem("token");
      }
    } finally {
      loading.value = false;
    }
  }

  async function updateUser(userData) {
    if (user.value) {
      user.value = { ...user.value, ...userData };
    }
  }

  function clearError() {
    error.value = null;
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isStudent,
    isEmployer,
    isAdmin,
    register,
    login,
    logout,
    fetchUser,
    updateUser,
    clearError,
  };
});
