<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import {
  MapPinIcon,
  BriefcaseIcon,
  CurrencyDollarIcon,
  ClockIcon,
  BuildingOfficeIcon,
  CalendarIcon,
  BookmarkIcon,
} from "@heroicons/vue/24/outline";
import { BookmarkIcon as BookmarkSolidIcon } from "@heroicons/vue/24/solid";
import api from "../../services/api";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const job = ref(null);
const loading = ref(false);
const applying = ref(false);
const saved = ref(false);

const showApplicationModal = ref(false);
const applicationForm = ref({
  cover_letter: "",
  resume: null,
});

onMounted(() => {
  fetchJob();
});

const canApply = computed(() => {
  return authStore.isAuthenticated && authStore.isStudent;
});

const companyLogo = computed(() => {
  return job.value?.employer_profile?.logo_url || null;
});

const companyName = computed(() => {
  return (
    job.value?.employer_profile?.company_name ||
    job.value?.employer?.name ||
    "Company"
  );
});

async function fetchJob() {
  loading.value = true;
  try {
    const response = await api.get(`/jobs/${route.params.id}`);
    if (response.data.success) {
      job.value = response.data.data;
    }
  } catch (error) {
    console.error("Error fetching job:", error);
    window.showToast?.("Failed to load job", "error");
    router.push({ name: "jobs" });
  } finally {
    loading.value = false;
  }
}

async function toggleSave() {
  if (!authStore.isAuthenticated) {
    window.showToast?.("Please login to save jobs", "warning");
    router.push({ name: "login" });
    return;
  }

  try {
    const response = await api.post(`/student/jobs/${job.value.id}/save`);
    if (response.data.success) {
      saved.value = response.data.data.is_saved;
      window.showToast?.(
        saved.value ? "Job saved successfully" : "Job removed from saved list",
        "success"
      );
    }
  } catch (error) {
    window.showToast?.("Failed to save job", "error");
  }
}

function openApplicationModal() {
  if (!authStore.isAuthenticated) {
    window.showToast?.("Please login to apply", "warning");
    router.push({ name: "login", query: { redirect: route.fullPath } });
    return;
  }

  if (!authStore.isStudent) {
    window.showToast?.("Only students can apply for jobs", "error");
    return;
  }

  showApplicationModal.value = true;
}

function handleFileChange(event) {
  applicationForm.value.resume = event.target.files[0];
}

async function submitApplication() {
  if (!applicationForm.value.cover_letter) {
    window.showToast?.("Please write a cover letter", "error");
    return;
  }

  applying.value = true;

  try {
    const formData = new FormData();
    formData.append("cover_letter", applicationForm.value.cover_letter);
    if (applicationForm.value.resume) {
      formData.append("resume", applicationForm.value.resume);
    }

    const response = await api.post(
      `/student/applications/${job.value.id}`,
      formData,
      {
        headers: { "Content-Type": "multipart/form-data" },
      }
    );

    if (response.data.success) {
      window.showToast?.("Application submitted successfully!", "success");
      showApplicationModal.value = false;
      router.push({ name: "student-applications" });
    }
  } catch (error) {
    window.showToast?.(
      error.response?.data?.message || "Failed to submit application",
      "error"
    );
  } finally {
    applying.value = false;
  }
}

function formatDate(dateString) {
  if (!dateString) return "Not specified";
  return new Date(dateString).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="loading" class="py-12 text-center container-custom">
      <div
        class="inline-block w-12 h-12 border-b-2 rounded-full animate-spin border-primary-600"
      ></div>
    </div>

    <div v-else-if="job" class="py-8 container-custom">
      <!-- Back Button -->
      <button
        @click="router.back()"
        class="flex items-center mb-6 text-gray-600 hover:text-gray-900"
      >
        ← Back to jobs
      </button>

      <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="space-y-6 lg:col-span-2">
          <!-- Job Header -->
          <div class="p-8 card">
            <div class="flex items-start space-x-6">
              <div v-if="companyLogo" class="flex-shrink-0">
                <img
                  :src="companyLogo"
                  :alt="companyName"
                  class="object-cover w-20 h-20 border border-gray-200 rounded-lg"
                />
              </div>
              <div
                v-else
                class="flex items-center justify-center flex-shrink-0 w-20 h-20 bg-gray-100 rounded-lg"
              >
                <BuildingOfficeIcon class="w-10 h-10 text-gray-400" />
              </div>

              <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">
                  {{ job.title }}
                </h1>
                <p class="mt-2 text-xl text-gray-600">{{ companyName }}</p>

                <div class="flex flex-wrap gap-4 mt-4 text-sm text-gray-600">
                  <div class="flex items-center">
                    <MapPinIcon class="w-5 h-5 mr-2" />
                    {{ job.location }}
                  </div>
                  <div class="flex items-center">
                    <BriefcaseIcon class="w-5 h-5 mr-2" />
                    {{ job.job_type }}
                  </div>
                  <div
                    v-if="job.salary_min && job.salary_max"
                    class="flex items-center"
                  >
                    <CurrencyDollarIcon class="w-5 h-5 mr-2" />
                    ${{ job.salary_min.toLocaleString() }} - ${{
                      job.salary_max.toLocaleString()
                    }}
                  </div>
                </div>

                <div class="flex flex-wrap gap-2 mt-4">
                  <span class="badge badge-primary">{{
                    job.experience_level
                  }}</span>
                  <span v-if="job.is_remote" class="badge badge-success"
                    >Remote</span
                  >
                  <span class="badge badge-gray">{{ job.category }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="p-8 card">
            <h2 class="mb-4 text-2xl font-bold text-gray-900">
              Job Description
            </h2>
            <div class="prose text-gray-700 whitespace-pre-line max-w-none">
              {{ job.description }}
            </div>
          </div>

          <!-- Responsibilities -->
          <div v-if="job.responsibilities" class="p-8 card">
            <h2 class="mb-4 text-2xl font-bold text-gray-900">
              Responsibilities
            </h2>
            <div class="prose text-gray-700 whitespace-pre-line max-w-none">
              {{ job.responsibilities }}
            </div>
          </div>

          <!-- Requirements -->
          <div v-if="job.requirements" class="p-8 card">
            <h2 class="mb-4 text-2xl font-bold text-gray-900">Requirements</h2>
            <div class="prose text-gray-700 whitespace-pre-line max-w-none">
              {{ job.requirements }}
            </div>
          </div>

          <!-- Skills -->
          <div
            v-if="job.skills_required && job.skills_required.length > 0"
            class="p-8 card"
          >
            <h2 class="mb-4 text-2xl font-bold text-gray-900">
              Required Skills
            </h2>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="skill in job.skills_required"
                :key="skill"
                class="px-3 py-1.5 bg-primary-50 text-primary-700 rounded-lg font-medium"
              >
                {{ skill }}
              </span>
            </div>
          </div>

          <!-- Benefits -->
          <div v-if="job.benefits && job.benefits.length > 0" class="p-8 card">
            <h2 class="mb-4 text-2xl font-bold text-gray-900">Benefits</h2>
            <ul class="space-y-2">
              <li
                v-for="benefit in job.benefits"
                :key="benefit"
                class="flex items-start"
              >
                <span class="mr-2 text-primary-600">✓</span>
                <span class="text-gray-700">{{ benefit }}</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <div class="sticky space-y-6 top-4">
            <!-- Apply Card -->
            <div class="p-6 card">
              <button
                v-if="canApply"
                @click="openApplicationModal"
                class="w-full mb-3 btn btn-primary btn-lg"
              >
                Apply Now
              </button>
              <button
                v-else-if="!authStore.isAuthenticated"
                @click="router.push({ name: 'login' })"
                class="w-full mb-3 btn btn-primary btn-lg"
              >
                Login to Apply
              </button>
              <button @click="toggleSave" class="w-full btn btn-outline">
                <BookmarkSolidIcon v-if="saved" class="w-5 h-5 mr-2" />
                <BookmarkIcon v-else class="w-5 h-5 mr-2" />
                {{ saved ? "Saved" : "Save Job" }}
              </button>
            </div>

            <!-- Job Info -->
            <div class="p-6 space-y-4 card">
              <h3 class="font-semibold text-gray-900">Job Information</h3>

              <div class="space-y-3 text-sm">
                <div class="flex items-start">
                  <CalendarIcon class="h-5 w-5 text-gray-400 mr-3 mt-0.5" />
                  <div>
                    <div class="text-gray-600">Posted</div>
                    <div class="font-medium text-gray-900">
                      {{ formatDate(job.created_at) }}
                    </div>
                  </div>
                </div>

                <div v-if="job.application_deadline" class="flex items-start">
                  <ClockIcon class="h-5 w-5 text-gray-400 mr-3 mt-0.5" />
                  <div>
                    <div class="text-gray-600">Application Deadline</div>
                    <div class="font-medium text-gray-900">
                      {{ formatDate(job.application_deadline) }}
                    </div>
                  </div>
                </div>

                <div v-if="job.start_date" class="flex items-start">
                  <CalendarIcon class="h-5 w-5 text-gray-400 mr-3 mt-0.5" />
                  <div>
                    <div class="text-gray-600">Start Date</div>
                    <div class="font-medium text-gray-900">
                      {{ formatDate(job.start_date) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Company Info -->
            <div v-if="job.employer_profile" class="p-6 card">
              <h3 class="mb-4 font-semibold text-gray-900">
                About {{ companyName }}
              </h3>
              <p class="text-sm text-gray-600">
                {{ job.employer_profile.company_description }}
              </p>

              <div v-if="job.employer_profile.website" class="mt-4">
                <a
                  :href="job.employer_profile.website"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-sm link"
                >
                  Visit Website →
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Application Modal -->
    <div
      v-if="showApplicationModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
      @click.self="showApplicationModal = false"
    >
      <div
        class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto p-8"
      >
        <h2 class="mb-6 text-2xl font-bold text-gray-900">
          Apply for {{ job.title }}
        </h2>

        <form @submit.prevent="submitApplication" class="space-y-6">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700">
              Cover Letter *
            </label>
            <textarea
              v-model="applicationForm.cover_letter"
              rows="10"
              required
              class="input"
              placeholder="Tell us why you're a great fit for this position..."
            ></textarea>
          </div>

          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700">
              Resume (Optional)
            </label>
            <input
              type="file"
              accept=".pdf,.doc,.docx"
              @change="handleFileChange"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
            />
            <p class="mt-1 text-xs text-gray-500">
              If not uploaded, we'll use the resume from your profile
            </p>
          </div>

          <div class="flex space-x-3">
            <button
              type="submit"
              :disabled="applying"
              class="flex-1 btn btn-primary"
            >
              {{ applying ? "Submitting..." : "Submit Application" }}
            </button>
            <button
              type="button"
              @click="showApplicationModal = false"
              class="btn btn-secondary"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
