<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import {
  BriefcaseIcon,
  AcademicCapIcon,
  BuildingOfficeIcon,
  MagnifyingGlassIcon,
  CheckCircleIcon,
} from "@heroicons/vue/24/outline";
import api from "../services/api";
import JobCard from "../components/jobs/JobCard.vue";

const router = useRouter();
const authStore = useAuthStore();

const recentJobs = ref([]);
const loading = ref(false);

const features = [
  {
    icon: BriefcaseIcon,
    title: "Find Your Dream Job",
    description:
      "Browse hundreds of opportunities from top employers looking to hire MUN talent.",
  },
  {
    icon: AcademicCapIcon,
    title: "For Students & Alumni",
    description:
      "Connect your MUN experience with career opportunities tailored for you.",
  },
  {
    icon: BuildingOfficeIcon,
    title: "For Employers",
    description:
      "Access a talented pool of MUN students and alumni ready to make an impact.",
  },
];

const benefits = [
  "Direct connection to MUN talent pool",
  "Easy application process",
  "Personalized job recommendations",
  "Career resources and support",
  "Verified student and employer profiles",
  "Real-time notifications",
];

onMounted(async () => {
  await fetchRecentJobs();
});

async function fetchRecentJobs() {
  loading.value = true;
  try {
    const response = await api.get("/jobs", {
      params: { per_page: 6 },
    });
    if (response.data.success) {
      recentJobs.value = response.data.data.data;
    }
  } catch (error) {
    console.error("Error fetching jobs:", error);
  } finally {
    loading.value = false;
  }
}

function handleGetStarted() {
  if (authStore.isAuthenticated) {
    router.push({ name: "jobs" });
  } else {
    router.push({ name: "register" });
  }
}
</script>

<template>
  <div>
    <!-- Hero Section -->
    <section
      class="relative text-white bg-gradient-to-br from-primary-600 via-primary-700 to-blue-800"
    >
      <div class="py-20 container-custom md:py-32">
        <div class="max-w-4xl">
          <h1 class="text-4xl font-bold leading-tight md:text-6xl">
            Connect MUN Talent with
            <span class="text-primary-200">Career Opportunities</span>
          </h1>
          <p class="mt-6 text-xl md:text-2xl text-primary-100">
            The premier job board connecting Memorial University students and
            alumni with employers.
          </p>

          <div class="flex flex-col gap-4 mt-10 sm:flex-row">
            <button
              @click="handleGetStarted"
              class="bg-white btn text-primary-600 hover:bg-gray-100 btn-lg"
            >
              <MagnifyingGlassIcon class="w-5 h-5 mr-2" />
              Browse Jobs
            </button>
            <RouterLink
              v-if="!authStore.isAuthenticated"
              to="/register"
              class="text-white border-white btn btn-outline hover:bg-white hover:text-primary-600 btn-lg"
            >
              Sign Up Free
            </RouterLink>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-3 gap-8 mt-16">
            <div>
              <div class="text-3xl font-bold md:text-4xl">500+</div>
              <div class="mt-1 text-primary-200">Active Jobs</div>
            </div>
            <div>
              <div class="text-3xl font-bold md:text-4xl">1,200+</div>
              <div class="mt-1 text-primary-200">Students</div>
            </div>
            <div>
              <div class="text-3xl font-bold md:text-4xl">150+</div>
              <div class="mt-1 text-primary-200">Employers</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Wave Decoration -->
      <div class="absolute bottom-0 left-0 right-0">
        <svg
          viewBox="0 0 1440 120"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
            fill="rgb(249, 250, 251)"
          />
        </svg>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-gray-50">
      <div class="container-custom">
        <div class="mb-16 text-center">
          <h2 class="text-3xl font-bold text-gray-900 md:text-4xl">
            Why Choose MUNext?
          </h2>
          <p class="mt-4 text-xl text-gray-600">
            The perfect platform to launch your career or find top talent
          </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
          <div
            v-for="feature in features"
            :key="feature.title"
            class="p-8 text-center transition-shadow card hover:shadow-lg"
          >
            <div class="flex justify-center mb-4">
              <div class="p-4 rounded-full bg-primary-100">
                <component
                  :is="feature.icon"
                  class="w-10 h-10 text-primary-600"
                />
              </div>
            </div>
            <h3 class="mb-3 text-xl font-semibold text-gray-900">
              {{ feature.title }}
            </h3>
            <p class="text-gray-600">
              {{ feature.description }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent Jobs Section -->
    <section class="py-20 bg-white">
      <div class="container-custom">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h2 class="text-3xl font-bold text-gray-900">
              Recent Opportunities
            </h2>
            <p class="mt-2 text-gray-600">
              Start your job search with these latest postings
            </p>
          </div>
          <RouterLink to="/jobs" class="btn btn-outline">
            View All Jobs
          </RouterLink>
        </div>

        <div v-if="loading" class="py-12 text-center">
          <div
            class="inline-block w-12 h-12 border-b-2 rounded-full animate-spin border-primary-600"
          ></div>
        </div>

        <div
          v-else-if="recentJobs.length > 0"
          class="grid grid-cols-1 gap-6 md:grid-cols-2"
        >
          <JobCard v-for="job in recentJobs" :key="job.id" :job="job" />
        </div>

        <div v-else class="py-12 text-center text-gray-500">
          No jobs available at the moment. Check back soon!
        </div>
      </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-20 bg-primary-50">
      <div class="container-custom">
        <div class="max-w-3xl mx-auto">
          <div class="mb-12 text-center">
            <h2 class="text-3xl font-bold text-gray-900 md:text-4xl">
              Everything You Need
            </h2>
            <p class="mt-4 text-xl text-gray-600">
              Powerful features to streamline your job search or hiring process
            </p>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div
              v-for="benefit in benefits"
              :key="benefit"
              class="flex items-start p-4 space-x-3 bg-white rounded-lg"
            >
              <CheckCircleIcon
                class="h-6 w-6 text-primary-600 flex-shrink-0 mt-0.5"
              />
              <span class="text-gray-700">{{ benefit }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section
      class="py-20 text-white bg-gradient-to-r from-primary-600 to-primary-700"
    >
      <div class="text-center container-custom">
        <h2 class="text-3xl font-bold md:text-4xl">Ready to Get Started?</h2>
        <p class="mt-4 text-xl text-primary-100">
          Join thousands of MUN students, alumni, and employers today.
        </p>
        <div class="flex flex-col justify-center gap-4 mt-8 sm:flex-row">
          <RouterLink
            v-if="!authStore.isAuthenticated"
            to="/register"
            class="bg-white btn text-primary-600 hover:bg-gray-100 btn-lg"
          >
            Create Free Account
          </RouterLink>
          <RouterLink
            to="/jobs"
            class="text-white border-white btn btn-outline hover:bg-white hover:text-primary-600 btn-lg"
          >
            Explore Jobs
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>
