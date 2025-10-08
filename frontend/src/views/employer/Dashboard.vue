<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import {
  BriefcaseIcon,
  UserGroupIcon,
  EyeIcon,
  PlusCircleIcon,
  ClockIcon,
  CheckCircleIcon,
} from "@heroicons/vue/24/outline";
import api from "../../services/api";

const router = useRouter();
const authStore = useAuthStore();

const stats = ref({
  totalJobs: 0,
  activeJobs: 0,
  totalApplications: 0,
  pendingApplications: 0,
  totalViews: 0,
});

const recentJobs = ref([]);
const recentApplications = ref([]);
const loading = ref(false);

const hasProfile = computed(() => {
  return authStore.user?.employer_profile !== undefined;
});

onMounted(async () => {
  await fetchDashboardData();
});

async function fetchDashboardData() {
  loading.value = true;
  try {
    const jobsResponse = await api.get("/employer/jobs");
    if (jobsResponse.data.success) {
      const jobs = jobsResponse.data.data.data;
      recentJobs.value = jobs.slice(0, 5);
      stats.value.totalJobs = jobs.length;
      stats.value.activeJobs = jobs.filter((j) => j.status === "open").length;
      stats.value.totalViews = jobs.reduce(
        (sum, j) => sum + (j.views_count || 0),
        0
      );

      let totalApps = 0;
      for (const job of jobs) {
        totalApps += job.applications_count || 0;
      }
      stats.value.totalApplications = totalApps;
    }
  } catch (error) {
    console.error("Error fetching dashboard data:", error);
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Employer Dashboard</h1>
        <p class="mt-2 text-gray-600">
          Manage your job postings and applications
        </p>
      </div>

      <!-- Profile Alert -->
      <div
        v-if="!hasProfile"
        class="p-4 mb-8 border-l-4 border-yellow-400 bg-yellow-50"
      >
        <div class="flex">
          <div class="flex-shrink-0">
            <svg
              class="w-5 h-5 text-yellow-400"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-yellow-700">
              Complete your company profile to start posting jobs.
              <RouterLink to="/employer/profile" class="font-medium underline">
                Complete Profile →
              </RouterLink>
            </p>
          </div>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
        <div class="p-6 card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Total Jobs</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ stats.totalJobs }}
              </p>
            </div>
            <div class="p-3 rounded-full bg-primary-100">
              <BriefcaseIcon class="w-8 h-8 text-primary-600" />
            </div>
          </div>
        </div>

        <div class="p-6 card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Active Jobs</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ stats.activeJobs }}
              </p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
              <CheckCircleIcon class="w-8 h-8 text-green-600" />
            </div>
          </div>
        </div>

        <div class="p-6 card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Total Applications</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ stats.totalApplications }}
              </p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full">
              <UserGroupIcon class="w-8 h-8 text-blue-600" />
            </div>
          </div>
        </div>

        <div class="p-6 card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Total Views</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ stats.totalViews }}
              </p>
            </div>
            <div class="p-3 bg-purple-100 rounded-full">
              <EyeIcon class="w-8 h-8 text-purple-600" />
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- Recent Jobs -->
        <div class="p-6 card">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Recent Job Postings</h2>
            <RouterLink to="/employer/jobs" class="text-sm link">
              View all →
            </RouterLink>
          </div>

          <div v-if="loading" class="py-8 text-center">
            <div
              class="inline-block w-8 h-8 border-b-2 rounded-full animate-spin border-primary-600"
            ></div>
          </div>

          <div v-else-if="recentJobs.length > 0" class="space-y-4">
            <div
              v-for="job in recentJobs"
              :key="job.id"
              class="p-4 transition-colors border border-gray-200 rounded-lg cursor-pointer hover:border-primary-200"
              @click="router.push(`/employer/jobs/${job.id}/applications`)"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900">{{ job.title }}</h3>
                  <p class="mt-1 text-sm text-gray-600">{{ job.location }}</p>
                  <div
                    class="flex items-center gap-4 mt-3 text-xs text-gray-500"
                  >
                    <span>{{ job.applications_count || 0 }} applications</span>
                    <span>{{ job.views_count || 0 }} views</span>
                  </div>
                </div>
                <span
                  :class="[
                    'badge',
                    job.status === 'open' ? 'badge-success' : 'badge-gray',
                  ]"
                >
                  {{ job.status }}
                </span>
              </div>
            </div>
          </div>

          <div v-else class="py-8 text-center text-gray-500">
            <BriefcaseIcon class="w-12 h-12 mx-auto mb-3 text-gray-400" />
            <p>No jobs posted yet</p>
            <RouterLink
              to="/employer/jobs/create"
              class="inline-block mt-2 text-sm link"
            >
              Post your first job →
            </RouterLink>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-6">
          <div class="p-6 card">
            <h2 class="mb-4 text-xl font-bold text-gray-900">Quick Actions</h2>
            <div class="space-y-3">
              <RouterLink
                to="/employer/jobs/create"
                class="w-full btn btn-primary"
              >
                <PlusCircleIcon class="w-5 h-5 mr-2" />
                Post a New Job
              </RouterLink>
              <RouterLink to="/employer/jobs" class="w-full btn btn-outline">
                Manage Job Postings
              </RouterLink>
              <RouterLink
                to="/employer/profile"
                class="w-full btn btn-secondary"
              >
                Edit Company Profile
              </RouterLink>
            </div>
          </div>

          <!-- Tips -->
          <div class="p-6 card bg-primary-50 border-primary-200">
            <h3 class="mb-3 font-semibold text-gray-900">
              💡 Tips for Success
            </h3>
            <ul class="space-y-2 text-sm text-gray-700">
              <li>• Write clear, detailed job descriptions</li>
              <li>• Respond to applications promptly</li>
              <li>• Keep your company profile up-to-date</li>
              <li>• Use specific skills in job requirements</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
