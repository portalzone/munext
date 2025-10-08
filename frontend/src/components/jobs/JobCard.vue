<script setup>
import { computed } from "vue";
import {
  MapPinIcon,
  BriefcaseIcon,
  CurrencyDollarIcon,
  ClockIcon,
  BuildingOfficeIcon,
  BookmarkIcon,
} from "@heroicons/vue/24/outline";
import { BookmarkIcon as BookmarkSolidIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
  job: {
    type: Object,
    required: true,
  },
  saved: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["save", "unsave"]);

const jobTypeColors = {
  "full-time": "badge-primary",
  "part-time": "badge-success",
  contract: "badge-warning",
  internship: "badge-gray",
  "co-op": "badge-gray",
};

const companyLogo = computed(() => {
  return props.job.employer_profile?.logo_url || null;
});

const companyName = computed(() => {
  return (
    props.job.employer_profile?.company_name ||
    props.job.employer?.name ||
    "Company"
  );
});

const timeAgo = computed(() => {
  const date = new Date(props.job.created_at);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);

  if (diff < 60) return "Just now";
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
  return date.toLocaleDateString();
});

function handleSaveToggle() {
  if (props.saved) {
    emit("unsave", props.job.id);
  } else {
    emit("save", props.job.id);
  }
}
</script>

<template>
  <div class="p-6 card card-hover">
    <div class="flex items-start justify-between">
      <div class="flex items-start flex-1 space-x-4">
        <!-- Company Logo -->
        <div class="flex-shrink-0">
          <div
            v-if="companyLogo"
            class="w-16 h-16 overflow-hidden border border-gray-200 rounded-lg"
          >
            <img
              :src="companyLogo"
              :alt="companyName"
              class="object-cover w-full h-full"
            />
          </div>
          <div
            v-else
            class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-lg"
          >
            <BuildingOfficeIcon class="w-8 h-8 text-gray-400" />
          </div>
        </div>

        <!-- Job Details -->
        <div class="flex-1 min-w-0">
          <RouterLink
            :to="`/jobs/${job.id}`"
            class="text-xl font-semibold text-gray-900 transition-colors hover:text-primary-600"
          >
            {{ job.title }}
          </RouterLink>

          <div class="flex items-center mt-1 text-sm text-gray-600">
            <BuildingOfficeIcon class="w-4 h-4 mr-1" />
            <span>{{ companyName }}</span>
          </div>

          <div class="flex flex-wrap gap-2 mt-3">
            <span
              :class="['badge', jobTypeColors[job.job_type] || 'badge-gray']"
            >
              {{ job.job_type }}
            </span>
            <span v-if="job.experience_level" class="badge badge-gray">
              {{ job.experience_level }}
            </span>
            <span v-if="job.is_remote" class="badge badge-primary">
              Remote
            </span>
          </div>

          <div class="flex flex-wrap gap-4 mt-4 text-sm text-gray-600">
            <div class="flex items-center">
              <MapPinIcon class="w-4 h-4 mr-1" />
              {{ job.location }}
            </div>

            <div
              v-if="job.salary_min && job.salary_max"
              class="flex items-center"
            >
              <CurrencyDollarIcon class="w-4 h-4 mr-1" />
              ${{ job.salary_min.toLocaleString() }} - ${{
                job.salary_max.toLocaleString()
              }}
            </div>

            <div class="flex items-center">
              <ClockIcon class="w-4 h-4 mr-1" />
              {{ timeAgo }}
            </div>
          </div>

          <!-- Skills -->
          <div
            v-if="job.skills_required && job.skills_required.length > 0"
            class="flex flex-wrap gap-2 mt-3"
          >
            <span
              v-for="skill in job.skills_required.slice(0, 5)"
              :key="skill"
              class="px-2 py-1 text-xs font-medium rounded text-primary-700 bg-primary-50"
            >
              {{ skill }}
            </span>
            <span
              v-if="job.skills_required.length > 5"
              class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded"
            >
              +{{ job.skills_required.length - 5 }} more
            </span>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <button
        @click.prevent="handleSaveToggle"
        class="flex-shrink-0 p-2 ml-4 transition-colors rounded-lg hover:bg-gray-100"
        :class="{ 'text-primary-600': saved, 'text-gray-400': !saved }"
      >
        <BookmarkSolidIcon v-if="saved" class="w-6 h-6" />
        <BookmarkIcon v-else class="w-6 h-6" />
      </button>
    </div>

    <!-- View Details Button -->
    <div class="pt-4 mt-4 border-t border-gray-200">
      <RouterLink
        :to="`/jobs/${job.id}`"
        class="text-sm font-medium text-primary-600 hover:text-primary-700"
      >
        View Details →
      </RouterLink>
    </div>
  </div>
</template>
