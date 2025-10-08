<script setup>
import { ref, onMounted } from "vue";
import { useAdminStore } from "../../stores/admin";
import {
  MagnifyingGlassIcon,
  CheckCircleIcon,
  XCircleIcon,
  TrashIcon,
  UserCircleIcon,
} from "@heroicons/vue/24/outline";

const adminStore = useAdminStore();

const users = ref([]);
const loading = ref(false);
const pagination = ref({});

const filters = ref({
  role: "",
  is_verified: "",
  search: "",
});

const roleOptions = [
  { value: "", label: "All Roles" },
  { value: "student", label: "Students" },
  { value: "alumni", label: "Alumni" },
  { value: "employer", label: "Employers" },
  { value: "admin", label: "Admins" },
];

const verificationOptions = [
  { value: "", label: "All Users" },
  { value: "true", label: "Verified" },
  { value: "false", label: "Unverified" },
];

onMounted(() => {
  fetchUsers();
});

async function fetchUsers() {
  loading.value = true;
  try {
    const response = await adminStore.fetchUsers(filters.value);
    if (response.success) {
      users.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        total: response.data.total,
      };
    }
  } catch (error) {
    console.error("Error fetching users:", error);
    window.showToast?.("Failed to load users", "error");
  } finally {
    loading.value = false;
  }
}

async function verifyUser(userId) {
  if (!confirm("Verify this user?")) return;

  try {
    await adminStore.verifyUser(userId);
    window.showToast?.("User verified successfully", "success");
    await fetchUsers();
  } catch (error) {
    window.showToast?.("Failed to verify user", "error");
  }
}

async function deleteUser(userId) {
  if (
    !confirm(
      "Are you sure you want to delete this user? This action cannot be undone."
    )
  )
    return;

  try {
    await adminStore.deleteUser(userId);
    window.showToast?.("User deleted successfully", "success");
    await fetchUsers();
  } catch (error) {
    window.showToast?.(
      error.response?.data?.message || "Failed to delete user",
      "error"
    );
  }
}

function getRoleBadgeColor(role) {
  const colors = {
    student: "badge-primary",
    alumni: "badge-success",
    employer: "badge-warning",
    admin: "badge-danger",
  };
  return colors[role] || "badge-gray";
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString();
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
        <p class="mt-2 text-gray-600">Manage all users on the platform</p>
      </div>

      <!-- Filters -->
      <div class="p-6 mb-6 card">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700"
              >Search</label
            >
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search by name or email..."
                class="pl-10 input"
                @keyup.enter="fetchUsers"
              />
              <MagnifyingGlassIcon
                class="absolute w-5 h-5 text-gray-400 transform -translate-y-1/2 left-3 top-1/2"
              />
            </div>
          </div>

          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700"
              >Role</label
            >
            <select v-model="filters.role" class="input" @change="fetchUsers">
              <option
                v-for="option in roleOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700"
              >Verification</label
            >
            <select
              v-model="filters.is_verified"
              class="input"
              @change="fetchUsers"
            >
              <option
                v-for="option in verificationOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <button @click="fetchUsers" class="w-full btn btn-primary">
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Results Count -->
      <div class="mb-4">
        <p class="text-sm text-gray-600">
          Showing <strong>{{ users.length }}</strong> of
          <strong>{{ pagination.total || 0 }}</strong> users
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="py-12 text-center">
        <div
          class="inline-block w-12 h-12 border-b-2 rounded-full animate-spin border-primary-600"
        ></div>
      </div>

      <!-- Users Table -->
      <div v-else-if="users.length > 0" class="overflow-hidden card">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  User
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Role
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Status
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Joined
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <UserCircleIcon class="w-10 h-10 text-gray-400" />
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ user.name }}
                      </div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['badge', getRoleBadgeColor(user.role)]">
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'badge',
                      user.is_verified ? 'badge-success' : 'badge-warning',
                    ]"
                  >
                    {{ user.is_verified ? "Verified" : "Unverified" }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                  {{ formatDate(user.created_at) }}
                </td>
                <td
                  class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap"
                >
                  <div class="flex justify-end space-x-2">
                    <button
                      v-if="!user.is_verified"
                      @click="verifyUser(user.id)"
                      class="text-green-600 hover:text-green-900"
                      title="Verify User"
                    >
                      <CheckCircleIcon class="w-5 h-5" />
                    </button>
                    <button
                      v-if="user.role !== 'admin'"
                      @click="deleteUser(user.id)"
                      class="text-red-600 hover:text-red-900"
                      title="Delete User"
                    >
                      <TrashIcon class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 text-center card">
        <UserCircleIcon class="w-16 h-16 mx-auto mb-4 text-gray-400" />
        <h3 class="mb-2 text-xl font-semibold text-gray-900">No users found</h3>
        <p class="text-gray-600">Try adjusting your filters</p>
      </div>
    </div>
  </div>
</template>
