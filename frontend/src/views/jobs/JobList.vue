<script setup>
import { ref, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import { MagnifyingGlassIcon, FunnelIcon } from "@heroicons/vue/24/outline";
import api from "../../services/api";
import JobCard from "../../components/jobs/JobCard.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const jobs = ref([]);
const loading = ref(false);
const pagination = ref({});

const filters = ref({
  search: route.query.search || "",
  category: route.query.category || "",
  job_type: route.query.job_type || "",
  experience_level: route.query.experience_level || "",
  location: route.query.location || "",
  is_remote: route.query.is_remote || "",
});

const showFilters = ref(false);

const categories = [
  "Technology",
  "Engineering",
  "Business",
  "Healthcare",
  "Education",
  "Marketing",
  "Sales",
  "Design",
  "Finance",
  "Other",
];

const jobTypes = [
  { value: "full-time", label: "Full-time" },
  { value: "part-time", label: "Part-time" },
  { value: "contract", label: "Contract" },
  { value: "internship", label: "Internship" },
  { value: "co-op", label: "Co-op" },
];

const experienceLevels = [
  { value: "entry", label: "Entry Level" },
  { value: "intermediate", label: "Intermediate" },
  { value: "senior", label: "Senior" },
  { value: "executive", label: "Executive" },
];

onMounted(() => {
  fetchJobs();
});

watch(
  () => route.query,
  () => {
    fetchJobs();
  }
);

async function fetchJobs(page = 1) {
  loading.value = true;
  try {
    const response = await api.get("/jobs", {
      params: {
        ...filters.value,
        page,
      },
    });

    if (response.data.success) {
      jobs.value = response.data.data.data;
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        total: response.data.data.total,
      };
    }
  } catch (error) {
    console.error("Error fetching jobs:", error);
    window.showToast?.("Failed to load jobs", "error");
  } finally {
    loading.value = false;
  }
}

function applyFilters() {
  router.push({ query: filters.value });
}

function clearFilters() {
  filters.value = {
    search: "",
    category: "",
    job_type: "",
    experience_level: "",
    location: "",
    is_remote: "",
  };
  router.push({ query: {} });
}

async function handleSaveJob(jobId) {
  if (!authStore.isAuthenticated) {
    window.showToast?.("Please login to save jobs", "warning");
    router.push({ name: "login" });
    return;
  }

  try {
    const response = await api.post(`/student/jobs/${jobId}/save`);
    if (response.data.success) {
      window.showToast?.("Job saved successfully", "success");
    }
  } catch (error) {
    window.showToast?.("Failed to save job", "error");
  }
}

async function handleUnsaveJob(jobId) {
  try {
    const response = await api.post(`/student/jobs/${jobId}/save`);
    if (response.data.success) {
      window.showToast?.("Job removed from saved list", "success");
    }
  } catch (error) {
    window.showToast?.("Failed to unsave job", "error");
  }
}

function goToPage(page) {
  fetchJobs(page);
  window.scrollTo({ top: 0, behavior: "smooth" });
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="py-8 container-custom">
        <h1 class="text-3xl font-bold text-gray-900">Browse Jobs</h1>
        <p class="mt-2 text-gray-600">
          Find your next opportunity from {{ pagination.total || 0 }} available
          positions
        </p>
      </div>
    </div>

    <div class="py-8 container-custom">
      <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
          <div class="sticky p-6 card top-4">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
              <button
                @click="clearFilters"
                class="text-sm text-primary-600 hover:text-primary-700"
              >
                Clear all
              </button>
            </div>

            <div class="space-y-6">
              <!-- Search -->
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Search
                </label>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Job title or keyword..."
                  class="input"
                  @keyup.enter="applyFilters"
                />
              </div>

              <!-- Category -->
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Category
                </label>
                <select v-model="filters.category" class="input">
                  <option value="">All Categories</option>
                  <option v-for="cat in categories" :key="cat" :value="cat">
                    {{ cat }}
                  </option>
                </select>
              </div>

              <!-- Job Type -->
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Job Type
                </label>
                <select v-model="filters.job_type" class="input">
                  <option value="">All Types</option>
                  <option
                    v-for="type in jobTypes"
                    :key="type.value"
                    :value="type.value"
                  >
                    {{ type.label }}
                  </option>
                </select>
              </div>

              <!-- Experience Level -->
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Experience Level
                </label>
                <select v-model="filters.experience_level" class="input">
                  <option value="">All Levels</option>
                  <option
                    v-for="level in experienceLevels"
                    :key="level.value"
                    :value="level.value"
                  >
                    {{ level.label }}
                  </option>
                </select>
              </div>

              <!-- Location -->
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Location
                </label>
                <input
                  v-model="filters.location"
                  type="text"
                  placeholder="City or province..."
                  class="input"
                />
              </div>

              <!-- Remote -->
              <div>
                <label class="flex items-center">
                  <input
                    v-model="filters.is_remote"
                    type="checkbox"
                    true-value="true"
                    false-value=""
                    class="border-gray-300 rounded text-primary-600 focus:ring-primary-500"
                  />
                  <span class="ml-2 text-sm text-gray-700">Remote only</span>
                </label>
              </div>

              <!-- Apply Button -->
              <button @click="applyFilters" class="w-full btn btn-primary">
                <FunnelIcon class="w-5 h-5 mr-2" />
                Apply Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Jobs List -->
        <div class="lg:col-span-3">
          <!-- Loading State -->
          <div v-if="loading" class="py-12 text-center">
            <div
              class="inline-block w-12 h-12 border-b-2 rounded-full animate-spin border-primary-600"
            ></div>
          </div>

          <!-- Jobs Grid -->
          <div v-else-if="jobs.length > 0" class="space-y-6">
            <JobCard
              v-for="job in jobs"
              :key="job.id"
              :job="job"
              @save="handleSaveJob"
              @unsave="handleUnsaveJob"
            />

            <!-- Pagination -->
            <div
              v-if="pagination.last_page > 1"
              class="flex items-center justify-center mt-8 space-x-2"
            >
              <button
                @click="goToPage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="btn btn-secondary"
                :class="{
                  'opacity-50 cursor-not-allowed':
                    pagination.current_page === 1,
                }"
              >
                Previous
              </button>

              <span class="text-gray-700">
                Page {{ pagination.current_page }} of {{ pagination.last_page }}
              </span>

              <button
                @click="goToPage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="btn btn-secondary"
                :class="{
                  'opacity-50 cursor-not-allowed':
                    pagination.current_page === pagination.last_page,
                }"
              >
                Next
              </button>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="p-12 text-center card">
            <MagnifyingGlassIcon class="w-16 h-16 mx-auto mb-4 text-gray-400" />
            <h3 class="mb-2 text-xl font-semibold text-gray-900">
              No jobs found
            </h3>
            <p class="mb-6 text-gray-600">
              Try adjusting your filters to see more results
            </p>
            <button @click="clearFilters" class="btn btn-primary">
              Clear Filters
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
