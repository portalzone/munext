<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "../../services/api";

const router = useRouter();

const form = ref({
  title: "",
  description: "",
  requirements: "",
  responsibilities: "",
  job_type: "full-time",
  location: "",
  salary_min: "",
  salary_max: "",
  salary_period: "per year",
  experience_level: "entry",
  category: "",
  skills_required: [],
  benefits: [],
  application_deadline: "",
  start_date: "",
  is_remote: false,
});

const skillInput = ref("");
const benefitInput = ref("");
const loading = ref(false);

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

const salaryPeriods = [
  { value: "per hour", label: "Per Hour" },
  { value: "per month", label: "Per Month" },
  { value: "per year", label: "Per Year" },
];

function addSkill() {
  if (
    skillInput.value.trim() &&
    !form.value.skills_required.includes(skillInput.value.trim())
  ) {
    form.value.skills_required.push(skillInput.value.trim());
    skillInput.value = "";
  }
}

function removeSkill(index) {
  form.value.skills_required.splice(index, 1);
}

function addBenefit() {
  if (
    benefitInput.value.trim() &&
    !form.value.benefits.includes(benefitInput.value.trim())
  ) {
    form.value.benefits.push(benefitInput.value.trim());
    benefitInput.value = "";
  }
}

function removeBenefit(index) {
  form.value.benefits.splice(index, 1);
}

async function submitJob() {
  loading.value = true;
  try {
    const response = await api.post("/employer/jobs", form.value);
    if (response.data.success) {
      window.showToast?.("Job posted successfully!", "success");
      router.push({ name: "employer-jobs" });
    }
  } catch (error) {
    window.showToast?.(
      error.response?.data?.message || "Failed to post job",
      "error"
    );
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <div class="max-w-4xl mx-auto">
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Post a New Job</h1>
          <p class="mt-2 text-gray-600">
            Fill in the details to create a job posting
          </p>
        </div>

        <form @submit.prevent="submitJob" class="space-y-6">
          <!-- Basic Information -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
              Basic Information
            </h2>

            <div class="space-y-6">
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Job Title *
                </label>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="input"
                  placeholder="e.g., Software Developer"
                />
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-700">
                    Job Type *
                  </label>
                  <select v-model="form.job_type" required class="input">
                    <option
                      v-for="type in jobTypes"
                      :key="type.value"
                      :value="type.value"
                    >
                      {{ type.label }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-700">
                    Experience Level *
                  </label>
                  <select
                    v-model="form.experience_level"
                    required
                    class="input"
                  >
                    <option
                      v-for="level in experienceLevels"
                      :key="level.value"
                      :value="level.value"
                    >
                      {{ level.label }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-700">
                    Category *
                  </label>
                  <select v-model="form.category" required class="input">
                    <option value="">Select Category</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">
                      {{ cat }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-700">
                    Location *
                  </label>
                  <input
                    v-model="form.location"
                    type="text"
                    required
                    class="input"
                    placeholder="St. John's, NL"
                  />
                </div>
              </div>

              <div>
                <label class="flex items-center">
                  <input
                    v-model="form.is_remote"
                    type="checkbox"
                    class="border-gray-300 rounded text-primary-600 focus:ring-primary-500"
                  />
                  <span class="ml-2 text-sm text-gray-700"
                    >This is a remote position</span
                  >
                </label>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
              Job Description
            </h2>

            <div class="space-y-6">
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Description *
                </label>
                <textarea
                  v-model="form.description"
                  rows="6"
                  required
                  class="input"
                  placeholder="Provide a detailed description of the job..."
                ></textarea>
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Responsibilities
                </label>
                <textarea
                  v-model="form.responsibilities"
                  rows="6"
                  class="input"
                  placeholder="List the main responsibilities for this role..."
                ></textarea>
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Requirements
                </label>
                <textarea
                  v-model="form.requirements"
                  rows="6"
                  class="input"
                  placeholder="List the requirements and qualifications..."
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Skills -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
              Required Skills
            </h2>

            <div class="flex gap-2 mb-4">
              <input
                v-model="skillInput"
                type="text"
                class="flex-1 input"
                placeholder="Add a skill (e.g., Python, React)"
                @keyup.enter="addSkill"
              />
              <button type="button" @click="addSkill" class="btn btn-primary">
                Add
              </button>
            </div>

            <div
              v-if="form.skills_required.length > 0"
              class="flex flex-wrap gap-2"
            >
              <span
                v-for="(skill, index) in form.skills_required"
                :key="index"
                class="inline-flex items-center px-3 py-1.5 bg-primary-50 text-primary-700 rounded-lg"
              >
                {{ skill }}
                <button
                  type="button"
                  @click="removeSkill(index)"
                  class="ml-2 text-primary-600 hover:text-primary-800"
                >
                  ×
                </button>
              </span>
            </div>
          </div>

          <!-- Compensation -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
              Compensation
            </h2>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Minimum Salary
                </label>
                <input
                  v-model.number="form.salary_min"
                  type="number"
                  min="0"
                  class="input"
                  placeholder="50000"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Maximum Salary
                </label>
                <input
                  v-model.number="form.salary_max"
                  type="number"
                  min="0"
                  class="input"
                  placeholder="70000"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Salary Period
                </label>
                <select v-model="form.salary_period" class="input">
                  <option
                    v-for="period in salaryPeriods"
                    :key="period.value"
                    :value="period.value"
                  >
                    {{ period.label }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Benefits -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">Benefits</h2>

            <div class="flex gap-2 mb-4">
              <input
                v-model="benefitInput"
                type="text"
                class="flex-1 input"
                placeholder="Add a benefit (e.g., Health insurance)"
                @keyup.enter="addBenefit"
              />
              <button type="button" @click="addBenefit" class="btn btn-primary">
                Add
              </button>
            </div>

            <div v-if="form.benefits.length > 0" class="space-y-2">
              <div
                v-for="(benefit, index) in form.benefits"
                :key="index"
                class="flex items-center justify-between p-3 rounded-lg bg-gray-50"
              >
                <span class="text-gray-700">{{ benefit }}</span>
                <button
                  type="button"
                  @click="removeBenefit(index)"
                  class="text-red-600 hover:text-red-800"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>

          <!-- Dates -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
              Important Dates
            </h2>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Application Deadline
                </label>
                <input
                  v-model="form.application_deadline"
                  type="date"
                  class="input"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Start Date
                </label>
                <input v-model="form.start_date" type="date" class="input" />
              </div>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="flex justify-end space-x-3">
            <RouterLink to="/employer/dashboard" class="btn btn-secondary">
              Cancel
            </RouterLink>
            <button type="submit" :disabled="loading" class="btn btn-primary">
              {{ loading ? "Posting..." : "Post Job" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
