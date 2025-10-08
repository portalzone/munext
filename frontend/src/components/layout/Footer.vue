<script setup>
import { BriefcaseIcon } from "@heroicons/vue/24/outline";
</script>

<template>
  <footer class="bg-gray-900 text-gray-300">
    <div class="container-custom py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Brand -->
        <div class="col-span-1">
          <div class="flex items-center space-x-2 mb-4">
            <BriefcaseIcon class="h-8 w-8 text-primary-400" />
            <span class="text-2xl font-bold text-white">
              MU<span class="text-primary-400">Next</span>
            </span>
          </div>
          <p class="text-sm text-gray-400">
            Connecting Memorial University students and alumni with career
            opportunities.
          </p>
        </div>

        <!-- For Students -->
        <div>
          <h3 class="text-white font-semibold mb-4">For Students</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <RouterLink
                to="/jobs"
                class="hover:text-primary-400 transition-colors"
              >
                Browse Jobs
              </RouterLink>
            </li>
            <li>
              <RouterLink
                to="/student/dashboard"
                class="hover:text-primary-400 transition-colors"
              >
                Dashboard
              </RouterLink>
            </li>
            <li>
              <RouterLink
                to="/student/profile"
                class="hover:text-primary-400 transition-colors"
              >
                My Profile
              </RouterLink>
            </li>
          </ul>
        </div>

        <!-- For Employers -->
        <div>
          <h3 class="text-white font-semibold mb-4">For Employers</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <RouterLink
                to="/employer/jobs/create"
                class="hover:text-primary-400 transition-colors"
              >
                Post a Job
              </RouterLink>
            </li>
            <li>
              <RouterLink
                to="/employer/dashboard"
                class="hover:text-primary-400 transition-colors"
              >
                Dashboard
              </RouterLink>
            </li>
            <li>
              <RouterLink
                to="/employer/profile"
                class="hover:text-primary-400 transition-colors"
              >
                Company Profile
              </RouterLink>
            </li>
          </ul>
        </div>

        <!-- About -->
        <div>
          <h3 class="text-white font-semibold mb-4">About</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <a href="#" class="hover:text-primary-400 transition-colors">
                About Us
              </a>
            </li>
            <li>
              <a href="#" class="hover:text-primary-400 transition-colors">
                Contact
              </a>
            </li>
            <li>
              <a href="#" class="hover:text-primary-400 transition-colors">
                Privacy Policy
              </a>
            </li>
            <li>
              <a href="#" class="hover:text-primary-400 transition-colors">
                Terms of Service
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div
        class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400"
      >
        <p>
          &copy; {{ new Date().getFullYear() }} MUNext. All rights reserved.
        </p>
      </div>
    </div>
  </footer>
</template>
