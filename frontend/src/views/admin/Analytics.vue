<script setup>
import { ref, onMounted } from "vue";
import { useAdminStore } from "../../stores/admin";
import { ChartBarIcon } from "@heroicons/vue/24/outline";

const adminStore = useAdminStore();

const analytics = ref(null);
const loading = ref(false);

onMounted(() => {
  fetchAnalytics();
});

async function fetchAnalytics() {
  loading.value = true;
  try {
    const response = await adminStore.fetchAnalytics();
    if (response.success) {
      analytics.value = response.data;
    }
  } catch (error) {
    console.error("Error fetching analytics:", error);
    window.showToast?.("Failed to load analytics", "error");
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Analytics</h1>
        <p class="mt-2 text-gray-600">Detailed system insights and metrics</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="py-12 text-center">
        <div
          class="inline-block w-12 h-12 border-b-2 rounded-full animate-spin border-primary-600"
        ></div>
      </div>

      <div v-else-if="analytics" class="space-y-8">
        <!-- Jobs by Category -->
        <div class="p-6 card">
          <h2 class="mb-6 text-xl font-bold text-gray-900">Jobs by Category</h2>
          <div class="space-y-3">
            <div
              v-for="item in analytics.jobs_by_category"
              :key="item.category"
              class="flex items-center justify-between"
            >
              <span class="text-gray-700">{{ item.category }}</span>
              <div class="flex items-center space-x-3">
                <div class="w-64 h-2 bg-gray-200 rounded-full">
                  <div
                    class="h-2 rounded-full bg-primary-600"
                    :style="{
                      width:
                        (item.count /
                          Math.max(
                            ...analytics.jobs_by_category.map((i) => i.count)
                          )) *
                          100 +
                        '%',
                    }"
                  ></div>
                </div>
                <span class="w-12 font-semibold text-right text-gray-900">{{
                  item.count
                }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Applications by Status -->
        <div class="p-6 card">
          <h2 class="mb-6 text-xl font-bold text-gray-900">
            Applications by Status
          </h2>
          <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div
              v-for="item in analytics.applications_by_status"
              :key="item.status"
              class="p-4 text-center rounded-lg bg-gray-50"
            >
              <p class="text-3xl font-bold text-gray-900">{{ item.count }}</p>
              <p class="mt-2 text-sm text-gray-600 capitalize">
                {{ item.status }}
              </p>
            </div>
          </div>
        </div>

        <!-- Top Employers -->
        <div class="p-6 card">
          <h2 class="mb-6 text-xl font-bold text-gray-900">
            Top Employers by Job Postings
          </h2>
          <div class="space-y-4">
            <div
              v-for="(employer, index) in analytics.top_employers"
              :key="employer.id"
              class="flex items-center justify-between p-4 rounded-lg bg-gray-50"
            >
              <div class="flex items-center space-x-4">
                <span class="text-2xl font-bold text-gray-400"
                  >#{{ index + 1 }}</span
                >
                <div>
                  <p class="font-medium text-gray-900">{{ employer.name }}</p>
                  <p class="text-sm text-gray-600">{{ employer.email }}</p>
                </div>
              </div>
              <span class="badge badge-primary"
                >{{ employer.jobs_count }} jobs</span
              >
            </div>
          </div>
        </div>

        <!-- Job Types Distribution -->
        <div class="p-6 card">
          <h2 class="mb-6 text-xl font-bold text-gray-900">
            Job Types Distribution
          </h2>
          <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
            <div
              v-for="item in analytics.jobs_by_type"
              :key="item.job_type"
              class="p-4 text-center rounded-lg bg-gray-50"
            >
              <p class="text-2xl font-bold text-gray-900">{{ item.count }}</p>
              <p class="mt-2 text-xs text-gray-600 capitalize">
                {{ item.job_type }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 text-center card">
        <ChartBarIcon class="w-16 h-16 mx-auto mb-4 text-gray-400" />
        <h3 class="mb-2 text-xl font-semibold text-gray-900">
          No analytics data available
        </h3>
        <p class="text-gray-600">
          Data will appear here once the system has activity
        </p>
      </div>
    </div>
  </div>
</template>
