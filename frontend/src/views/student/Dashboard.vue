<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import {
  BriefcaseIcon,
  DocumentTextIcon,
  BookmarkIcon,
  CheckCircleIcon,
  ClockIcon,
  XCircleIcon,
} from "@heroicons/vue/24/outline";
import api from "../../services/api";

const router = useRouter();
const authStore = useAuthStore();

const stats = ref({
  applications: 0,
  savedJobs: 0,
  pending: 0,
  shortlisted: 0,
});

const recentApplications = ref([]);
const recommendedJobs = ref([]);
const loading = ref(false);

const hasProfile = computed(() => {
  return authStore.user?.student_profile !== undefined;
});

onMounted(async () => {
  await fetchDashboardData();
});

async function fetchDashboardData() {
  loading.value = true;
  try {
    // Fetch applications
    const appsResponse = await api.get("/student/applications", {
      params: { per_page: 5 },
    });
    if (appsResponse.data.success) {
      recentApplications.value = appsResponse.data.data.data;
      stats.value.applications = appsResponse.data.data.total;
      stats.value.pending = recentApplications.value.filter(
        (app) => app.status === "pending"
      ).length;
      stats.value.shortlisted = recentApplications.value.filter(
        (app) => app.status === "shortlisted"
      ).length;
    }

    // Fetch saved jobs count
    const savedResponse = await api.get("/student/jobs/saved", {
      params: { per_page: 1 },
    });
    if (savedResponse.data.success) {
      stats.value.savedJobs = savedResponse.data.data.total;
    }

    // Fetch recommended jobs
    const jobsResponse = await api.get("/jobs", { params: { per_page: 4 } });
    if (jobsResponse.data.success) {
      recommendedJobs.value = jobsResponse.data.data.data;
    }
  } catch (error) {
    console.error("Error fetching dashboard data:", error);
  } finally {
    loading.value = false;
  }
}

function getStatusColor(status) {
  const colors = {
    pending: "badge-warning",
    reviewed: "badge-primary",
    shortlisted: "badge-success",
    rejected: "badge-danger",
  };
  return colors[status] || "badge-gray";
}

function getStatusIcon(status) {
  const icons = {
    pending: ClockIcon,
    reviewed: DocumentTextIcon,
    shortlisted: CheckCircleIcon,
    rejected: XCircleIcon,
  };
  return icons[status] || ClockIcon;
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
          Welcome back, {{ authStore.user?.name }}!
        </h1>
        <p class="mt-2 text-gray-600">
          Here's what's happening with your job search
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
              Complete your profile to start applying for jobs.
              <RouterLink to="/student/profile" class="font-medium underline">
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
              <p class="text-sm text-gray-600">Total Applications</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ stats.applications }}
              </p>
            </div>
            <div class="p-3 rounded-full bg-primary-100">
              <DocumentTextIcon class="w-8 h-8 text-primary-600" />
            </div>
          </div>
        </div>

        <div class="p-6 card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Saved Jobs</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ stats.savedJobs }}
              </p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full">
              <BookmarkIcon class="w-8 h-8 text-blue-600" />
            </div>
          </div>
        </div>

        <div class="p-6 card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Pending</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ stats.pending }}
              </p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-full">
              <ClockIcon class="w-8 h-8 text-yellow-600" />
            </div>
          </div>
        </div>

        <div class="p-6 card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Shortlisted</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ stats.shortlisted }}
              </p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
              <CheckCircleIcon class="w-8 h-8 text-green-600" />
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- Recent Applications -->
        <div class="p-6 card">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Recent Applications</h2>
            <RouterLink to="/student/applications" class="text-sm link">
              View all →
            </RouterLink>
          </div>

          <div v-if="loading" class="py-8 text-center">
            <div
              class="inline-block w-8 h-8 border-b-2 rounded-full animate-spin border-primary-600"
            ></div>
          </div>

          <div v-else-if="recentApplications.length > 0" class="space-y-4">
            <div
              v-for="app in recentApplications"
              :key="app.id"
              class="p-4 transition-colors border border-gray-200 rounded-lg cursor-pointer hover:border-primary-200"
              @click="router.push(`/student/applications`)"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900">
                    {{ app.job.title }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-600">
                    {{ app.job.employer_profile?.company_name || "Company" }}
                  </p>
                  <p class="mt-2 text-xs text-gray-500">
                    Applied {{ new Date(app.applied_at).toLocaleDateString() }}
                  </p>
                </div>
                <span :class="['badge', getStatusColor(app.status)]">
                  {{ app.status }}
                </span>
              </div>
            </div>
          </div>

          <div v-else class="py-8 text-center text-gray-500">
            <DocumentTextIcon class="w-12 h-12 mx-auto mb-3 text-gray-400" />
            <p>No applications yet</p>
            <RouterLink to="/jobs" class="inline-block mt-2 text-sm link">
              Browse jobs →
            </RouterLink>
          </div>
        </div>

        <!-- Recommended Jobs -->
        <div class="p-6 card">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Recommended Jobs</h2>
            <RouterLink to="/jobs" class="text-sm link">
              View all →
            </RouterLink>
          </div>

          <div v-if="loading" class="py-8 text-center">
            <div
              class="inline-block w-8 h-8 border-b-2 rounded-full animate-spin border-primary-600"
            ></div>
          </div>

          <div v-else-if="recommendedJobs.length > 0" class="space-y-4">
            <div
              v-for="job in recommendedJobs"
              :key="job.id"
              class="p-4 transition-colors border border-gray-200 rounded-lg cursor-pointer hover:border-primary-200"
              @click="router.push(`/jobs/${job.id}`)"
            >
              <h3 class="font-semibold text-gray-900">{{ job.title }}</h3>
              <p class="mt-1 text-sm text-gray-600">
                {{ job.employer_profile?.company_name || "Company" }}
              </p>
              <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">
                <span class="badge badge-gray">{{ job.job_type }}</span>
                <span>{{ job.location }}</span>
              </div>
            </div>
          </div>

          <div v-else class="py-8 text-center text-gray-500">
            <BriefcaseIcon class="w-12 h-12 mx-auto mb-3 text-gray-400" />
            <p>No jobs available</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 gap-6 mt-8 md:grid-cols-3">
        <RouterLink
          to="/jobs"
          class="p-6 transition-shadow cursor-pointer card hover:shadow-lg"
        >
          <BriefcaseIcon class="w-10 h-10 mb-3 text-primary-600" />
          <h3 class="mb-2 font-semibold text-gray-900">Browse Jobs</h3>
          <p class="text-sm text-gray-600">Find your next opportunity</p>
        </RouterLink>

        <RouterLink
          to="/student/profile"
          class="p-6 transition-shadow cursor-pointer card hover:shadow-lg"
        >
          <DocumentTextIcon class="w-10 h-10 mb-3 text-primary-600" />
          <h3 class="mb-2 font-semibold text-gray-900">Update Profile</h3>
          <p class="text-sm text-gray-600">Keep your profile current</p>
        </RouterLink>

        <RouterLink
          to="/student/saved-jobs"
          class="p-6 transition-shadow cursor-pointer card hover:shadow-lg"
        >
          <BookmarkIcon class="w-10 h-10 mb-3 text-primary-600" />
          <h3 class="mb-2 font-semibold text-gray-900">Saved Jobs</h3>
          <p class="text-sm text-gray-600">View your saved jobs</p>
        </RouterLink>
      </div>
    </div>
  </div>
</template>
