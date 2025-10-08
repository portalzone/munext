<script setup>
import { ref, onMounted } from "vue";
import { BookmarkIcon } from "@heroicons/vue/24/outline";
import api from "../../services/api";
import JobCard from "../../components/jobs/JobCard.vue";

const savedJobs = ref([]);
const loading = ref(false);

onMounted(() => {
  fetchSavedJobs();
});

async function fetchSavedJobs() {
  loading.value = true;
  try {
    const response = await api.get("/student/jobs/saved");
    if (response.data.success) {
      savedJobs.value = response.data.data.data;
    }
  } catch (error) {
    console.error("Error fetching saved jobs:", error);
    window.showToast?.("Failed to load saved jobs", "error");
  } finally {
    loading.value = false;
  }
}

async function handleUnsaveJob(jobId) {
  try {
    const response = await api.post(`/student/jobs/${jobId}/save`);
    if (response.data.success) {
      window.showToast?.("Job removed from saved list", "success");
      await fetchSavedJobs();
    }
  } catch (error) {
    window.showToast?.("Failed to unsave job", "error");
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Saved Jobs</h1>
        <p class="mt-2 text-gray-600">Jobs you've bookmarked for later</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="py-12 text-center">
        <div
          class="inline-block w-12 h-12 border-b-2 rounded-full animate-spin border-primary-600"
        ></div>
      </div>

      <!-- Saved Jobs List -->
      <div v-else-if="savedJobs.length > 0" class="grid grid-cols-1 gap-6">
        <JobCard
          v-for="job in savedJobs"
          :key="job.id"
          :job="job"
          :saved="true"
          @unsave="handleUnsaveJob"
        />
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 text-center card">
        <BookmarkIcon class="w-16 h-16 mx-auto mb-4 text-gray-400" />
        <h3 class="mb-2 text-xl font-semibold text-gray-900">
          No saved jobs yet
        </h3>
        <p class="mb-6 text-gray-600">
          Save jobs you're interested in to easily find them later
        </p>
        <RouterLink to="/jobs" class="btn btn-primary">
          Browse Jobs
        </RouterLink>
      </div>
    </div>
  </div>
</template>
