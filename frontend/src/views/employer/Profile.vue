<script setup>
import { ref, onMounted } from "vue";
import { useAuthStore } from "../../stores/auth";
import { PhotoIcon, TrashIcon } from "@heroicons/vue/24/outline";
import api from "../../services/api";

const authStore = useAuthStore();

const form = ref({
  company_name: "",
  company_description: "",
  industry: "",
  company_size: "",
  website: "",
  contact_person: "",
  contact_email: "",
  contact_phone: "",
  location: "",
  founded_year: "",
});

const loading = ref(false);
const logoFile = ref(null);
const logoUrl = ref(null);
const uploadingLogo = ref(false);

const industries = [
  "Technology",
  "Finance",
  "Healthcare",
  "Education",
  "Manufacturing",
  "Retail",
  "Consulting",
  "Energy",
  "Government",
  "Non-Profit",
  "Other",
];

const companySizes = [
  "1-10 employees",
  "11-50 employees",
  "51-200 employees",
  "201-500 employees",
  "501-1000 employees",
  "1000+ employees",
];

onMounted(async () => {
  await fetchProfile();
});

async function fetchProfile() {
  loading.value = true;
  try {
    const response = await api.get("/employer/profile");
    if (response.data.success) {
      const profile = response.data.data;
      Object.assign(form.value, profile);
      logoUrl.value = profile.logo_url;
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
    const response = await api.post("/employer/profile", form.value);
    if (response.data.success) {
      window.showToast?.("Profile saved successfully", "success");
      authStore.updateUser({ employer_profile: response.data.data });
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

function handleLogoChange(event) {
  logoFile.value = event.target.files[0];
  uploadLogo();
}

async function uploadLogo() {
  if (!logoFile.value) return;

  uploadingLogo.value = true;
  try {
    const formData = new FormData();
    formData.append("logo", logoFile.value);

    const response = await api.post("/employer/profile/logo", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    if (response.data.success) {
      logoUrl.value = response.data.data.logo_url;
      window.showToast?.("Logo uploaded successfully", "success");
    }
  } catch (error) {
    window.showToast?.("Failed to upload logo", "error");
  } finally {
    uploadingLogo.value = false;
    logoFile.value = null;
  }
}

async function deleteLogo() {
  if (!confirm("Are you sure you want to delete your logo?")) return;

  try {
    const response = await api.delete("/employer/profile/logo");
    if (response.data.success) {
      logoUrl.value = null;
      window.showToast?.("Logo deleted successfully", "success");
    }
  } catch (error) {
    window.showToast?.("Failed to delete logo", "error");
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-8 container-custom">
      <div class="max-w-4xl mx-auto">
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Company Profile</h1>
          <p class="mt-2 text-gray-600">Tell students about your company</p>
        </div>

        <form @submit.prevent="saveProfile" class="space-y-6">
          <!-- Company Logo -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
              Company Logo
            </h2>

            <div class="flex items-center space-x-6">
              <div class="flex-shrink-0">
                <div
                  v-if="logoUrl"
                  class="w-32 h-32 overflow-hidden border-2 border-gray-200 rounded-lg"
                >
                  <img
                    :src="logoUrl"
                    alt="Company logo"
                    class="object-cover w-full h-full"
                  />
                </div>
                <div
                  v-else
                  class="flex items-center justify-center w-32 h-32 bg-gray-100 border-2 border-gray-300 border-dashed rounded-lg"
                >
                  <PhotoIcon class="w-12 h-12 text-gray-400" />
                </div>
              </div>

              <div class="flex-1">
                <label class="block mb-2">
                  <span class="sr-only">Choose logo</span>
                  <input
                    type="file"
                    accept="image/*"
                    @change="handleLogoChange"
                    :disabled="uploadingLogo"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                  />
                </label>
                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                <p v-if="uploadingLogo" class="mt-2 text-sm text-primary-600">
                  Uploading...
                </p>

                <button
                  v-if="logoUrl"
                  type="button"
                  @click="deleteLogo"
                  class="mt-3 btn btn-danger btn-sm"
                >
                  <TrashIcon class="w-4 h-4 mr-1" />
                  Remove Logo
                </button>
              </div>
            </div>
          </div>

          <!-- Company Information -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
              Company Information
            </h2>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div class="md:col-span-2">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Company Name *
                </label>
                <input
                  v-model="form.company_name"
                  type="text"
                  required
                  class="input"
                  placeholder="Your Company Inc."
                />
              </div>

              <div class="md:col-span-2">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Company Description *
                </label>
                <textarea
                  v-model="form.company_description"
                  rows="4"
                  required
                  class="input"
                  placeholder="Tell students what your company does..."
                ></textarea>
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Industry *
                </label>
                <select v-model="form.industry" required class="input">
                  <option value="">Select Industry</option>
                  <option
                    v-for="industry in industries"
                    :key="industry"
                    :value="industry"
                  >
                    {{ industry }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Company Size
                </label>
                <select v-model="form.company_size" class="input">
                  <option value="">Select Size</option>
                  <option
                    v-for="size in companySizes"
                    :key="size"
                    :value="size"
                  >
                    {{ size }}
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

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Founded Year
                </label>
                <input
                  v-model.number="form.founded_year"
                  type="number"
                  min="1800"
                  :max="new Date().getFullYear()"
                  class="input"
                  placeholder="2010"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Website
                </label>
                <input
                  v-model="form.website"
                  type="url"
                  class="input"
                  placeholder="https://yourcompany.com"
                />
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="p-6 card">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
              Contact Information
            </h2>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Contact Person *
                </label>
                <input
                  v-model="form.contact_person"
                  type="text"
                  required
                  class="input"
                  placeholder="John Doe"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Contact Email *
                </label>
                <input
                  v-model="form.contact_email"
                  type="email"
                  required
                  class="input"
                  placeholder="contact@company.com"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Contact Phone
                </label>
                <input
                  v-model="form.contact_phone"
                  type="tel"
                  class="input"
                  placeholder="(709) 555-1234"
                />
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3">
            <RouterLink to="/employer/dashboard" class="btn btn-secondary">
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
