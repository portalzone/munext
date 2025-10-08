<script setup>
import { ref, onMounted } from "vue";
import { useAuthStore } from "../../stores/auth";
import { DocumentArrowUpIcon, TrashIcon } from "@heroicons/vue/24/outline";
import api from "../../services/api";

const authStore = useAuthStore();

const form = ref({
  student_number: "",
  program: "",
  faculty: "",
  graduation_year: new Date().getFullYear(),
  gpa: "",
  bio: "",
  skills: [],
  linkedin_url: "",
  github_url: "",
  portfolio_url: "",
  phone: "",
  location: "",
  available_from: "",
  work_authorization: "",
});

const skillInput = ref("");
const loading = ref(false);
const resumeFile = ref(null);
const resumeUrl = ref(null);
const uploadingResume = ref(false);

const faculties = [
  "Faculty of Arts",
  "Faculty of Business Administration",
  "Faculty of Education",
  "Faculty of Engineering and Applied Science",
  "Faculty of Medicine",
  "Faculty of Science",
  "School of Music",
  "School of Nursing",
  "Other",
];

onMounted(async () => {
  await fetchProfile();
});

async function fetchProfile() {
  loading.value = true;
  try {
    const response = await api.get("/student/profile");
    if (response.data.success) {
      const profile = response.data.data;
      Object.assign(form.value, profile);
      resumeUrl.value = profile.resume_url;
    }
  } catch (error) {
    if (error.response?.status !== 404) {
      console.error("Error fetching profile:", error);
    }
  } finally {
    loading.value = false;
  }
}

async function saveProfile() {
  loading.value = true;
  try {
    const response = await api.post("/student/profile", form.value);
    if (response.data.success) {
      window.showToast?.("Profile saved successfully", "success");
      authStore.updateUser({ student_profile: response.data.data });
    }
  } catch (error) {
    window.showToast?.(
      error.response?.data?.message || "Failed to save profile",
      "error"
    );
  } finally {
    loading.value = false;
  }
}

function addSkill() {
  if (
    skillInput.value.trim() &&
    !form.value.skills.includes(skillInput.value.trim())
  ) {
    form.value.skills.push(skillInput.value.trim());
    skillInput.value = "";
  }
}

function removeSkill(index) {
  form.value.skills.splice(index, 1);
}

function handleResumeChange(event) {
  resumeFile.value = event.target.files[0];
  uploadResume();
}

async function uploadResume() {
  if (!resumeFile.value) return;

  uploadingResume.value = true;
  try {
    const formData = new FormData();
    formData.append("resume", resumeFile.value);

    const response = await api.post("/student/profile/resume", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    if (response.data.success) {
      resumeUrl.value = response.data.data.resume_url;
      window.showToast?.("Resume uploaded successfully", "success");
    }
  } catch (error) {
    window.showToast?.("Failed to upload resume", "error");
  } finally {
    uploadingResume.value = false;
    resumeFile.value = null;
  }
}

async function deleteResume() {
  if (!confirm("Are you sure you want to delete your resume?")) return;

  try {
    const response = await api.delete("/student/profile/resume");
    if (response.data.success) {
      resumeUrl.value = null;
      window.showToast?.("Resume deleted successfully", "success");
    }
  } catch (error) {
    window.showToast?.("Failed to delete resume", "error");
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <div class="max-w-4xl mx-auto">
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Student Profile</h1>
          <p class="mt-2 text-gray-600">
            Complete your profile to start applying for jobs
          </p>
        </div>

        <form @submit.prevent="saveProfile" class="space-y-6">
          <!-- Basic Information -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
              Basic Information
            </h2>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Student Number
                </label>
                <input
                  v-model="form.student_number"
                  type="text"
                  class="input"
                  placeholder="202012345"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Phone Number
                </label>
                <input
                  v-model="form.phone"
                  type="tel"
                  class="input"
                  placeholder="(709) 555-1234"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Faculty *
                </label>
                <select v-model="form.faculty" required class="input">
                  <option value="">Select Faculty</option>
                  <option
                    v-for="faculty in faculties"
                    :key="faculty"
                    :value="faculty"
                  >
                    {{ faculty }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Program *
                </label>
                <input
                  v-model="form.program"
                  type="text"
                  required
                  class="input"
                  placeholder="e.g., Computer Science"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Graduation Year *
                </label>
                <input
                  v-model.number="form.graduation_year"
                  type="number"
                  required
                  min="2000"
                  max="2050"
                  class="input"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  GPA (Optional)
                </label>
                <input
                  v-model.number="form.gpa"
                  type="number"
                  step="0.01"
                  min="0"
                  max="4.0"
                  class="input"
                  placeholder="3.75"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Location
                </label>
                <input
                  v-model="form.location"
                  type="text"
                  class="input"
                  placeholder="St. John's, NL"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Available From
                </label>
                <input
                  v-model="form.available_from"
                  type="date"
                  class="input"
                />
              </div>
            </div>

            <div class="mt-6">
              <label class="block mb-2 text-sm font-medium text-gray-700">
                Work Authorization
              </label>
              <select v-model="form.work_authorization" class="input">
                <option value="">Select work authorization</option>
                <option value="Canadian Citizen">Canadian Citizen</option>
                <option value="Permanent Resident">Permanent Resident</option>
                <option value="Work Permit">Work Permit</option>
                <option value="Study Permit">Study Permit</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="mt-6">
              <label class="block mb-2 text-sm font-medium text-gray-700">
                Bio
              </label>
              <textarea
                v-model="form.bio"
                rows="4"
                class="input"
                placeholder="Tell employers about yourself..."
              ></textarea>
            </div>
          </div>

          <!-- Skills -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">Skills</h2>

            <div class="flex gap-2 mb-4">
              <input
                v-model="skillInput"
                type="text"
                class="flex-1 input"
                placeholder="Add a skill (e.g., Python, React, SQL)"
                @keyup.enter="addSkill"
              />
              <button type="button" @click="addSkill" class="btn btn-primary">
                Add
              </button>
            </div>

            <div v-if="form.skills.length > 0" class="flex flex-wrap gap-2">
              <span
                v-for="(skill, index) in form.skills"
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

          <!-- Links -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
              Professional Links
            </h2>

            <div class="space-y-4">
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  LinkedIn URL
                </label>
                <input
                  v-model="form.linkedin_url"
                  type="url"
                  class="input"
                  placeholder="https://linkedin.com/in/yourprofile"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  GitHub URL
                </label>
                <input
                  v-model="form.github_url"
                  type="url"
                  class="input"
                  placeholder="https://github.com/yourusername"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Portfolio URL
                </label>
                <input
                  v-model="form.portfolio_url"
                  type="url"
                  class="input"
                  placeholder="https://yourportfolio.com"
                />
              </div>
            </div>
          </div>

          <!-- Resume -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">Resume</h2>

            <div v-if="resumeUrl" class="mb-4">
              <div
                class="flex items-center justify-between p-4 border border-green-200 rounded-lg bg-green-50"
              >
                <div class="flex items-center">
                  <DocumentArrowUpIcon class="w-8 h-8 mr-3 text-green-600" />
                  <div>
                    <p class="font-medium text-green-900">Resume uploaded</p>
                    <a
                      :href="resumeUrl"
                      target="_blank"
                      class="text-sm text-green-700 hover:underline"
                    >
                      View resume →
                    </a>
                  </div>
                </div>
                <button
                  type="button"
                  @click="deleteResume"
                  class="btn btn-danger btn-sm"
                >
                  <TrashIcon class="w-4 h-4 mr-1" />
                  Delete
                </button>
              </div>
            </div>

            <div>
              <label class="block">
                <span class="sr-only">Choose resume file</span>
                <input
                  type="file"
                  accept=".pdf,.doc,.docx"
                  @change="handleResumeChange"
                  :disabled="uploadingResume"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                />
              </label>
              <p class="mt-2 text-xs text-gray-500">
                PDF, DOC, or DOCX (Max 5MB)
              </p>
              <p v-if="uploadingResume" class="mt-2 text-sm text-primary-600">
                Uploading...
              </p>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3">
            <RouterLink to="/student/dashboard" class="btn btn-secondary">
              Cancel
            </RouterLink>
            <button type="submit" :disabled="loading" class="btn btn-primary">
              {{ loading ? "Saving..." : "Save Profile" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
