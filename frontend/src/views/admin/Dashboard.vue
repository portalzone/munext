<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAdminStore } from "../../stores/admin";
import {
  UsersIcon,
  BriefcaseIcon,
  DocumentTextIcon,
  CheckCircleIcon,
  ChartBarIcon,
} from "@heroicons/vue/24/outline";

const router = useRouter();
const adminStore = useAdminStore();

const stats = ref(null);
const recentUsers = ref([]);
const recentJobs = ref([]);
const loading = ref(false);

onMounted(async () => {
  await fetchDashboard();
});

async function fetchDashboard() {
  loading.value = true;
  try {
    await adminStore.fetchDashboard();
    stats.value = adminStore.stats.stats;
    recentUsers.value = adminStore.stats.recent_users;
    recentJobs.value = adminStore.stats.recent_jobs;
  } catch (error) {
    console.error("Error loading dashboard:", error);
    window.showToast?.("Failed to load dashboard", "error");
  } finally {
    loading.value = false;
  }
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString();
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="mt-2 text-gray-600">System overview and management</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="py-12 text-center">
        <div
          class="inline-block w-12 h-12 border-b-2 rounded-full animate-spin border-primary-600"
        ></div>
      </div>

      <div v-else-if="stats">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
          <div class="p-6 card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Total Users</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">
                  {{ stats.total_users }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                  {{ stats.verified_users }} verified
                </p>
              </div>
              <div class="p-3 bg-blue-100 rounded-full">
                <UsersIcon class="w-8 h-8 text-blue-600" />
              </div>
            </div>
          </div>

          <div class="p-6 card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Students</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">
                  {{ stats.total_students }}
                </p>
              </div>
              <div class="p-3 bg-green-100 rounded-full">
                <UsersIcon class="w-8 h-8 text-green-600" />
              </div>
            </div>
          </div>

          <div class="p-6 card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Total Jobs</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">
                  {{ stats.total_jobs }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                  {{ stats.active_jobs }} active
                </p>
              </div>
              <div class="p-3 bg-purple-100 rounded-full">
                <BriefcaseIcon class="w-8 h-8 text-purple-600" />
              </div>
            </div>
          </div>

          <div class="p-6 card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Applications</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">
                  {{ stats.total_applications }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                  {{ stats.pending_applications }} pending
                </p>
              </div>
              <div class="p-3 bg-yellow-100 rounded-full">
                <DocumentTextIcon class="w-8 h-8 text-yellow-600" />
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
          <RouterLink
            to="/admin/users"
            class="p-6 transition-shadow cursor-pointer card hover:shadow-lg"
          >
            <UsersIcon class="w-10 h-10 mb-3 text-primary-600" />
            <h3 class="mb-2 font-semibold text-gray-900">Manage Users</h3>
            <p class="text-sm text-gray-600">
              View, verify, and manage user accounts
            </p>
          </RouterLink>

          <RouterLink
            to="/admin/jobs"
            class="p-6 transition-shadow cursor-pointer card hover:shadow-lg"
          >
            <BriefcaseIcon class="w-10 h-10 mb-3 text-primary-600" />
            <h3 class="mb-2 font-semibold text-gray-900">Manage Jobs</h3>
            <p class="text-sm text-gray-600">View and moderate job postings</p>
          </RouterLink>

          <RouterLink
            to="/admin/analytics"
            class="p-6 transition-shadow cursor-pointer card hover:shadow-lg"
          >
            <ChartBarIcon class="w-10 h-10 mb-3 text-primary-600" />
            <h3 class="mb-2 font-semibold text-gray-900">Analytics</h3>
            <p class="text-sm text-gray-600">View detailed system analytics</p>
          </RouterLink>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
          <!-- Recent Users -->
          <div class="p-6 card">
            <h2 class="mb-4 text-xl font-bold text-gray-900">Recent Users</h2>
            <div class="space-y-3">
              <div
                v-for="user in recentUsers"
                :key="user.id"
                class="flex items-center justify-between p-3 rounded-lg bg-gray-50"
              >
                <div>
                  <p class="font-medium text-gray-900">{{ user.name }}</p>
                  <p class="text-sm text-gray-600">{{ user.email }}</p>
                </div>
                <div class="text-right">
                  <span
                    :class="[
                      'badge',
                      user.is_verified ? 'badge-success' : 'badge-warning',
                    ]"
                  >
                    {{ user.is_verified ? "Verified" : "Pending" }}
                  </span>
                  <p class="mt-1 text-xs text-gray-500">
                    {{ formatDate(user.created_at) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Jobs -->
          <div class="p-6 card">
            <h2 class="mb-4 text-xl font-bold text-gray-900">Recent Jobs</h2>
            <div class="space-y-3">
              <div
                v-for="job in recentJobs"
                :key="job.id"
                class="flex items-center justify-between p-3 rounded-lg bg-gray-50"
              >
                <div class="flex-1">
                  <p class="font-medium text-gray-900">{{ job.title }}</p>
                  <p class="text-sm text-gray-600">
                    {{ job.employer_profile?.company_name || "Company" }}
                  </p>
                </div>
                <div class="text-right">
                  <span
                    :class="[
                      'badge',
                      job.status === 'open' ? 'badge-success' : 'badge-gray',
                    ]"
                  >
                    {{ job.status }}
                  </span>
                  <p class="mt-1 text-xs text-gray-500">
                    {{ formatDate(job.created_at) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
