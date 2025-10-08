<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import {
  BriefcaseIcon,
  PlusCircleIcon,
  PencilIcon,
  TrashIcon,
  EyeIcon,
} from "@heroicons/vue/24/outline";
import api from "../../services/api";

const router = useRouter();

const jobs = ref([]);
const loading = ref(false);
const filter = ref("all");

const filterOptions = [
  { value: "all", label: "All Jobs" },
  { value: "open", label: "Open" },
  { value: "closed", label: "Closed" },
  { value: "filled", label: "Filled" },
];

onMounted(() => {
  fetchJobs();
});

const filteredJobs = computed(() => {
  if (filter.value === "all") return jobs.value;
  return jobs.value.filter((job) => job.status === filter.value);
});

async function fetchJobs() {
  loading.value = true;
  try {
    const response = await api.get("/employer/jobs");
    if (response.data.success) {
      jobs.value = response.data.data.data;
    }
  } catch (error) {
    console.error("Error fetching jobs:", error);
    window.showToast?.("Failed to load jobs", "error");
  } finally {
    loading.value = false;
  }
}

async function deleteJob(jobId) {
  if (
    !confirm(
      "Are you sure you want to delete this job? This action cannot be undone."
    )
  )
    return;

  try {
    const response = await api.delete(`/employer/jobs/${jobId}`);
    if (response.data.success) {
      window.showToast?.("Job deleted successfully", "success");
      await fetchJobs();
    }
  } catch (error) {
    window.showToast?.("Failed to delete job", "error");
  }
}

function getStatusColor(status) {
  const colors = {
    open: "badge-success",
    closed: "badge-gray",
    filled: "badge-primary",
  };
  return colors[status] || "badge-gray";
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">My Job Postings</h1>
          <p class="mt-2 text-gray-600">
            Manage your active and past job listings
          </p>
        </div>
        <RouterLink to="/employer/jobs/create" class="btn btn-primary">
          <PlusCircleIcon class="w-5 h-5 mr-2" />
          Post New Job
        </RouterLink>
      </div>

      <!-- Filter Tabs -->
      <div
        class="flex p-1 mb-6 space-x-1 overflow-x-auto bg-gray-100 rounded-lg"
      >
        <button
          v-for="option in filterOptions"
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

      <!-- Jobs List -->
      <div v-else-if="filteredJobs.length > 0" class="space-y-4">
        <div
          v-for="job in filteredJobs"
          :key="job.id"
          class="p-6 transition-shadow card hover:shadow-md"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-start justify-between mb-4">
                <div>
                  <h3 class="text-xl font-semibold text-gray-900">
                    {{ job.title }}
                  </h3>
                  <p class="mt-1 text-gray-600">{{ job.location }}</p>
                </div>
                <span :class="['badge', getStatusColor(job.status)]">
                  {{ job.status }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-4 mb-4 md:grid-cols-4">
                <div class="flex items-center text-sm text-gray-600">
                  <BriefcaseIcon class="w-5 h-5 mr-2 text-gray-400" />
                  <span>{{ job.job_type }}</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                  <EyeIcon class="w-5 h-5 mr-2 text-gray-400" />
                  <span>{{ job.views_count || 0 }} views</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                  <span class="font-medium">{{
                    job.applications_count || 0
                  }}</span>
                  <span class="ml-1">applications</span>
                </div>
                <div class="text-sm text-gray-500">
                  Posted {{ new Date(job.created_at).toLocaleDateString() }}
                </div>
              </div>

              <div class="flex flex-wrap gap-3">
                <RouterLink
                  :to="`/jobs/${job.id}`"
                  class="btn btn-outline btn-sm"
                >
                  View Job
                </RouterLink>
                <RouterLink
                  :to="`/employer/jobs/${job.id}/applications`"
                  class="btn btn-primary btn-sm"
                >
                  View Applications ({{ job.applications_count || 0 }})
                </RouterLink>
                <RouterLink
                  :to="`/employer/jobs/${job.id}/edit`"
                  class="btn btn-secondary btn-sm"
                >
                  <PencilIcon class="w-4 h-4 mr-1" />
                  Edit
                </RouterLink>
                <button
                  @click="deleteJob(job.id)"
                  class="btn btn-danger btn-sm"
                >
                  <TrashIcon class="w-4 h-4 mr-1" />
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 text-center card">
        <BriefcaseIcon class="w-16 h-16 mx-auto mb-4 text-gray-400" />
        <h3 class="mb-2 text-xl font-semibold text-gray-900">
          {{ filter === "all" ? "No jobs posted yet" : `No ${filter} jobs` }}
        </h3>
        <p class="mb-6 text-gray-600">
          {{
            filter === "all"
              ? "Start by creating your first job posting"
              : "Try selecting a different filter"
          }}
        </p>
        <RouterLink
          v-if="filter === 'all'"
          to="/employer/jobs/create"
          class="btn btn-primary"
        >
          <PlusCircleIcon class="w-5 h-5 mr-2" />
          Post Your First Job
        </RouterLink>
        <button v-else @click="filter = 'all'" class="btn btn-primary">
          View All Jobs
        </button>
      </div>
    </div>
  </div>
</template>
