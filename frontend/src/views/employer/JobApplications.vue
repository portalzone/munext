<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import {
  DocumentTextIcon,
  UserIcon,
  EnvelopeIcon,
  PhoneIcon,
} from "@heroicons/vue/24/outline";
import api from "../../services/api";

const route = useRoute();

const job = ref(null);
const applications = ref([]);
const loading = ref(false);
const filter = ref("all");
const selectedApplication = ref(null);
const showModal = ref(false);

const filterOptions = [
  { value: "all", label: "All" },
  { value: "pending", label: "Pending" },
  { value: "reviewed", label: "Reviewed" },
  { value: "shortlisted", label: "Shortlisted" },
  { value: "rejected", label: "Rejected" },
];

onMounted(async () => {
  await fetchJobAndApplications();
});

const filteredApplications = computed(() => {
  if (filter.value === "all") return applications.value;
  return applications.value.filter((app) => app.status === filter.value);
});

async function fetchJobAndApplications() {
  loading.value = true;
  try {
    // Fetch job details
    const jobResponse = await api.get(`/jobs/${route.params.id}`);
    if (jobResponse.data.success) {
      job.value = jobResponse.data.data;
    }

    // Fetch applications
    const appsResponse = await api.get(
      `/employer/jobs/${route.params.id}/applications`
    );
    if (appsResponse.data.success) {
      applications.value = appsResponse.data.data.data;
    }
  } catch (error) {
    console.error("Error fetching data:", error);
    window.showToast?.("Failed to load applications", "error");
  } finally {
    loading.value = false;
  }
}

function viewApplication(app) {
  selectedApplication.value = app;
  showModal.value = true;
}

async function updateStatus(appId, status) {
  try {
    const response = await api.put(`/employer/applications/${appId}/status`, {
      status,
    });
    if (response.data.success) {
      window.showToast?.(`Application ${status}`, "success");
      await fetchJobAndApplications();
      if (selectedApplication.value?.id === appId) {
        selectedApplication.value = response.data.data;
      }
    }
  } catch (error) {
    window.showToast?.("Failed to update status", "error");
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
      <!-- Header -->
      <div class="mb-8">
        <button
          @click="$router.back()"
          class="mb-4 text-gray-600 hover:text-gray-900"
        >
          ← Back
        </button>
        <h1 class="text-3xl font-bold text-gray-900">{{ job?.title }}</h1>
        <p class="mt-2 text-gray-600">
          {{ applications.length }} application(s) received
        </p>
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

      <!-- Applications List -->
      <div
        v-else-if="filteredApplications.length > 0"
        class="grid grid-cols-1 gap-4"
      >
        <div
          v-for="app in filteredApplications"
          :key="app.id"
          class="p-6 transition-shadow cursor-pointer card hover:shadow-md"
          @click="viewApplication(app)"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ app.student.name }}
                  </h3>
                  <p class="text-sm text-gray-600">{{ app.student.email }}</p>
                </div>
                <span :class="['badge', getStatusColor(app.status)]">
                  {{ app.status }}
                </span>
              </div>

              <div
                v-if="app.student_profile"
                class="mb-3 space-y-1 text-sm text-gray-600"
              >
                <p>
                  <strong>Program:</strong> {{ app.student_profile.program }}
                </p>
                <p>
                  <strong>Graduation:</strong>
                  {{ app.student_profile.graduation_year }}
                </p>
                <p v-if="app.student_profile.gpa">
                  <strong>GPA:</strong> {{ app.student_profile.gpa }}
                </p>
              </div>

              <div class="text-sm text-gray-500">
                Applied on {{ formatDate(app.applied_at) }}
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
        <p class="text-gray-600">
          {{
            filter === "all"
              ? "Applications will appear here when candidates apply"
              : "Try selecting a different filter"
          }}
        </p>
      </div>
    </div>

    <!-- Application Detail Modal -->
    <div
      v-if="showModal && selectedApplication"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-black bg-opacity-50"
      @click.self="showModal = false"
    >
      <div
        class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto p-8"
      >
        <div class="flex items-start justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">
              {{ selectedApplication.student.name }}
            </h2>
            <p class="mt-1 text-gray-600">
              {{ selectedApplication.student.email }}
            </p>
          </div>
          <button
            @click="showModal = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
          <span
            :class="[
              'badge text-base',
              getStatusColor(selectedApplication.status),
            ]"
          >
            {{ selectedApplication.status }}
          </span>
        </div>

        <!-- Student Profile -->
        <div
          v-if="selectedApplication.student_profile"
          class="p-6 mb-6 card bg-gray-50"
        >
          <h3 class="mb-4 font-semibold text-gray-900">Profile Information</h3>
          <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
            <div>
              <span class="text-gray-600">Program:</span>
              <span class="ml-2 font-medium">{{
                selectedApplication.student_profile.program
              }}</span>
            </div>
            <div>
              <span class="text-gray-600">Faculty:</span>
              <span class="ml-2 font-medium">{{
                selectedApplication.student_profile.faculty
              }}</span>
            </div>
            <div>
              <span class="text-gray-600">Graduation Year:</span>
              <span class="ml-2 font-medium">{{
                selectedApplication.student_profile.graduation_year
              }}</span>
            </div>
            <div v-if="selectedApplication.student_profile.gpa">
              <span class="text-gray-600">GPA:</span>
              <span class="ml-2 font-medium">{{
                selectedApplication.student_profile.gpa
              }}</span>
            </div>
            <div v-if="selectedApplication.student_profile.phone">
              <span class="text-gray-600">Phone:</span>
              <span class="ml-2 font-medium">{{
                selectedApplication.student_profile.phone
              }}</span>
            </div>
            <div v-if="selectedApplication.student_profile.location">
              <span class="text-gray-600">Location:</span>
              <span class="ml-2 font-medium">{{
                selectedApplication.student_profile.location
              }}</span>
            </div>
          </div>

          <!-- Skills -->
          <div
            v-if="selectedApplication.student_profile.skills?.length > 0"
            class="mt-4"
          >
            <span class="text-sm text-gray-600">Skills:</span>
            <div class="flex flex-wrap gap-2 mt-2">
              <span
                v-for="skill in selectedApplication.student_profile.skills"
                :key="skill"
                class="px-2 py-1 text-xs rounded bg-primary-50 text-primary-700"
              >
                {{ skill }}
              </span>
            </div>
          </div>
        </div>

        <!-- Cover Letter -->
        <div class="mb-6">
          <h3 class="mb-3 font-semibold text-gray-900">Cover Letter</h3>
          <div
            class="p-6 text-gray-700 whitespace-pre-line rounded-lg bg-gray-50"
          >
            {{ selectedApplication.cover_letter }}
          </div>
        </div>

        <!-- Resume -->
        <div v-if="selectedApplication.resume_url" class="mb-6">
          <h3 class="mb-3 font-semibold text-gray-900">Resume</h3>
          <a
            :href="selectedApplication.resume_url"
            target="_blank"
            class="btn btn-outline"
          >
            <DocumentTextIcon class="w-5 h-5 mr-2" />
            View Resume
          </a>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200">
          <button
            v-if="selectedApplication.status === 'pending'"
            @click="updateStatus(selectedApplication.id, 'reviewed')"
            class="btn btn-primary"
          >
            Mark as Reviewed
          </button>
          <button
            v-if="selectedApplication.status !== 'shortlisted'"
            @click="updateStatus(selectedApplication.id, 'shortlisted')"
            class="btn btn-primary"
          >
            Shortlist
          </button>
          <button
            v-if="selectedApplication.status !== 'rejected'"
            @click="updateStatus(selectedApplication.id, 'rejected')"
            class="btn btn-danger"
          >
            Reject
          </button>
          <button @click="showModal = false" class="btn btn-secondary">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
