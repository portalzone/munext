import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../services/api";

export const useAdminStore = defineStore("admin", () => {
  const stats = ref(null);
  const loading = ref(false);
  const error = ref(null);

  async function fetchDashboard() {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.get("/admin/dashboard");
      if (response.data.success) {
        stats.value = response.data.data;
      }
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to load dashboard";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchUsers(params = {}) {
    const response = await api.get("/admin/users", { params });
    return response.data;
  }

  async function verifyUser(userId) {
    const response = await api.put(`/admin/users/${userId}/verify`);
    return response.data;
  }

  async function deleteUser(userId) {
    const response = await api.delete(`/admin/users/${userId}`);
    return response.data;
  }

  async function fetchAnalytics() {
    const response = await api.get("/admin/analytics");
    return response.data;
  }

  return {
    stats,
    loading,
    error,
    fetchDashboard,
    fetchUsers,
    verifyUser,
    deleteUser,
    fetchAnalytics,
  };
});
