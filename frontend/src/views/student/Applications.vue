<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import {
  DocumentTextIcon,
  ClockIcon,
  CheckCircleIcon,
  XCircleIcon,
} from "@heroicons/vue/24/outline";
import api from "../../services/api";

const router = useRouter();

const applications = ref([]);
const loading = ref(false);
const filter = ref("all");

const statusOptions = [
  { value: "all", label: "All Applications" },
  { value: "pending", label: "Pending" },
  { value: "reviewed", label: "Reviewed" },
  { value: "shortlisted", label: "Shortlisted" },
  { value: "rejected", label: "Rejected" },
];

onMounted(() => {
  fetchApplications();
});

async function fetchApplications() {
  loading.value = true;
  try {
    const response = await api.get("/student/applications");
    if (response.data.success) {
      applications.value = response.data.data.data;
    }
  } catch (error) {
    console.error("Error fetching applications:", error);
    window.showToast?.("Failed to load applications", "error");
  } finally {
    loading.value = false;
  }
}

const filteredApplications = computed(() => {
  if (filter.value === "all") return applications.value;
  return applications.value.filter((app) => app.status === filter.value);
});

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

async function withdrawApplication(appId) {
  if (!confirm("Are you sure you want to withdraw this application?")) return;

  try {
    const response = await api.delete(
      `/student/applications/${appId}/withdraw`
    );
    if (response.data.success) {
      window.showToast?.("Application withdrawn successfully", "success");
      await fetchApplications();
    }
  } catch (error) {
    window.showToast?.(
      error.response?.data?.message || "Failed to withdraw application",
      "error"
    );
  }
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Applications</h1>
        <p class="mt-2 text-gray-600">
          Track the status of your job applications
        </p>
      </div>

      <!-- Filter Tabs -->
      <div
        class="flex p-1 mb-6 space-x-1 overflow-x-auto bg-gray-100 rounded-lg"
      >
        <button
          v-for="option in statusOptions"
          :key="option.value"
          @click="filter = option.value"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors',
            filter === option.value
              ? 'bg-white text-primary-600 shadow-sm'
              : 'text-gray-600 hover:text-gray-900',
          ]"
        >
          {{ option.label }}
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="py-12 text-center">
        <div
          class="inline-block w-12 h-12 border-b-2 rounded-full animate-spin border-primary-600"
        ></div>
      </div>

      <!-- Applications List -->
      <div v-else-if="filteredApplications.length > 0" class="space-y-4">
        <div
          v-for="app in filteredApplications"
          :key="app.id"
          class="p-6 transition-shadow card hover:shadow-md"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="text-xl font-semibold text-gray-900">
                    {{ app.job.title }}
                  </h3>
                  <p class="mt-1 text-gray-600">
                    {{ app.job.employer_profile?.company_name || "Company" }}
                  </p>
                </div>
                <span :class="['badge', getStatusColor(app.status)]">
                  {{ app.status }}
                </span>
              </div>

              <div
                class="grid grid-cols-1 gap-4 mt-4 text-sm text-gray-600 md:grid-cols-3"
              >
                <div>
                  <span class="font-medium">Applied:</span>
                  {{ formatDate(app.applied_at) }}
                </div>
                <div>
                  <span class="font-medium">Job Type:</span>
                  {{ app.job.job_type }}
                </div>
                <div>
                  <span class="font-medium">Location:</span>
                  {{ app.job.location }}
                </div>
              </div>

              <!-- Cover Letter Preview -->
              <div v-if="app.cover_letter" class="mt-4">
                <p class="mb-2 text-sm font-medium text-gray-700">
                  Cover Letter:
                </p>
                <p class="text-sm text-gray-600 line-clamp-2">
                  {{ app.cover_letter }}
                </p>
              </div>

              <!-- Actions -->
              <div class="flex flex-wrap gap-3 mt-4">
                <RouterLink
                  :to="`/jobs/${app.job.id}`"
                  class="btn btn-outline btn-sm"
                >
                  View Job
                </RouterLink>
                <button
                  v-if="app.status === 'pending'"
                  @click="withdrawApplication(app.id)"
                  class="btn btn-danger btn-sm"
                >
                  Withdraw Application
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 text-center card">
        <DocumentTextIcon class="w-16 h-16 mx-auto mb-4 text-gray-400" />
        <h3 class="mb-2 text-xl font-semibold text-gray-900">
          {{
            filter === "all"
              ? "No applications yet"
              : `No ${filter} applications`
          }}
        </h3>
        <p class="mb-6 text-gray-600">
          {{
            filter === "all"
              ? "Start applying to jobs to see them here"
              : "Try selecting a different filter"
          }}
        </p>
        <RouterLink v-if="filter === 'all'" to="/jobs" class="btn btn-primary">
          Browse Jobs
        </RouterLink>
        <button v-else @click="filter = 'all'" class="btn btn-primary">
          View All Applications
        </button>
      </div>
    </div>
  </div>
</template>
