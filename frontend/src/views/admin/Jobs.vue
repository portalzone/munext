<script setup>
import { ref, onMounted } from "vue";
import { BriefcaseIcon, TrashIcon, EyeIcon } from "@heroicons/vue/24/outline";
import api from "../../services/api";

const jobs = ref([]);
const loading = ref(false);
const filters = ref({
  status: "",
  search: "",
});

const statusOptions = [
  { value: "", label: "All Status" },
  { value: "open", label: "Open" },
  { value: "closed", label: "Closed" },
  { value: "filled", label: "Filled" },
];

onMounted(() => {
  fetchJobs();
});

async function fetchJobs() {
  loading.value = true;
  try {
    const response = await api.get("/admin/jobs", { params: filters.value });
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
  if (!confirm("Are you sure you want to delete this job?")) return;

  try {
    const response = await api.delete(`/admin/jobs/${jobId}`);
    if (response.data.success) {
      window.showToast?.("Job deleted successfully", "success");
      await fetchJobs();
    }
  } catch (error) {
    window.showToast?.("Failed to delete job", "error");
  }
}

async function updateJobStatus(jobId, status) {
  try {
    const response = await api.put(`/admin/jobs/${jobId}/status`, { status });
    if (response.data.success) {
      window.showToast?.("Job status updated", "success");
      await fetchJobs();
    }
  } catch (error) {
    window.showToast?.("Failed to update status", "error");
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

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString();
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Job Management</h1>
        <p class="mt-2 text-gray-600">Monitor and manage all job postings</p>
      </div>

      <!-- Filters -->
      <div class="p-6 mb-6 card">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700"
              >Search</label
            >
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search jobs..."
              class="input"
              @keyup.enter="fetchJobs"
            />
          </div>

          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700"
              >Status</label
            >
            <select v-model="filters.status" class="input" @change="fetchJobs">
              <option
                v-for="option in statusOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <button @click="fetchJobs" class="w-full btn btn-primary">
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="py-12 text-center">
        <div
          class="inline-block w-12 h-12 border-b-2 rounded-full animate-spin border-primary-600"
        ></div>
      </div>

      <!-- Jobs List -->
      <div v-else-if="jobs.length > 0" class="space-y-4">
        <div v-for="job in jobs" :key="job.id" class="p-6 card">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ job.title }}
                  </h3>
                  <p class="text-gray-600">
                    {{ job.employer_profile?.company_name || "Company" }}
                  </p>
                </div>
                <span :class="['badge', getStatusColor(job.status)]">
                  {{ job.status }}
                </span>
              </div>

              <div
                class="grid grid-cols-2 gap-4 mb-4 text-sm text-gray-600 md:grid-cols-4"
              >
                <div>
                  <span class="font-medium">Type:</span> {{ job.job_type }}
                </div>
                <div>
                  <span class="font-medium">Location:</span> {{ job.location }}
                </div>
                <div>
                  <span class="font-medium">Views:</span>
                  {{ job.views_count || 0 }}
                </div>
                <div>
                  <span class="font-medium">Posted:</span>
                  {{ formatDate(job.created_at) }}
                </div>
              </div>

              <div class="flex flex-wrap gap-3">
                <RouterLink
                  :to="`/jobs/${job.id}`"
                  class="btn btn-outline btn-sm"
                >
                  <EyeIcon class="w-4 h-4 mr-1" />
                  View Job
                </RouterLink>

                <select
                  v-model="job.status"
                  @change="updateJobStatus(job.id, job.status)"
                  class="text-sm border border-gray-300 rounded-lg px-3 py-1.5"
                >
                  <option value="open">Open</option>
                  <option value="closed">Closed</option>
                  <option value="filled">Filled</option>
                </select>

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
        <h3 class="mb-2 text-xl font-semibold text-gray-900">No jobs found</h3>
        <p class="text-gray-600">Try adjusting your filters</p>
      </div>
    </div>
  </div>
</template>
