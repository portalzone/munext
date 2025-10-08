<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue";
import {
  Bars3Icon,
  XMarkIcon,
  BellIcon,
  UserCircleIcon,
  BriefcaseIcon,
  BookmarkIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
  ChartBarIcon,
} from "@heroicons/vue/24/outline";
import api from "../../services/api";

const authStore = useAuthStore();
const router = useRouter();
const mobileMenuOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

const navigation = [
  { name: "Home", href: "/" },
  { name: "Browse Jobs", href: "/jobs" },
];

onMounted(async () => {
  if (authStore.isAuthenticated) {
    await fetchNotifications();
  }
});

async function fetchNotifications() {
  try {
    const response = await api.get("/notifications/unread-count");
    if (response.data.success) {
      unreadCount.value = response.data.data.count;
    }
  } catch (error) {
    console.error("Error fetching notifications:", error);
  }
}

function handleLogout() {
  authStore.logout();
}

function getDashboardRoute() {
  if (authStore.isStudent) {
    return "/student/dashboard";
  } else if (authStore.isEmployer) {
    return "/employer/dashboard";
  }
  return "/";
}
</script>

<template>
  <nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="container-custom">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center">
          <RouterLink to="/" class="flex items-center space-x-2">
            <BriefcaseIcon class="w-8 h-8 text-primary-600" />
            <span class="text-2xl font-bold text-gray-900">
              MU<span class="text-primary-600">Next</span>
            </span>
          </RouterLink>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex md:items-center md:space-x-8">
          <RouterLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            class="px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:text-primary-600"
            active-class="text-primary-600"
          >
            {{ item.name }}
          </RouterLink>

          <!-- Authenticated User Menu -->
          <div
            v-if="authStore.isAuthenticated"
            class="flex items-center space-x-4"
          >
            <!-- Notifications -->
            <Menu as="div" class="relative">
              <MenuButton
                class="relative p-2 text-gray-700 transition-colors hover:text-primary-600"
              >
                <BellIcon class="w-6 h-6" />
                <span
                  v-if="unreadCount > 0"
                  class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full"
                >
                  {{ unreadCount > 9 ? "9+" : unreadCount }}
                </span>
              </MenuButton>

              <transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
              >
                <MenuItems
                  class="absolute right-0 z-10 mt-2 origin-top-right bg-white rounded-lg shadow-lg w-80 ring-1 ring-black ring-opacity-5 focus:outline-none"
                >
                  <div class="p-4">
                    <h3 class="text-sm font-semibold text-gray-900">
                      Notifications
                    </h3>
                    <p
                      v-if="unreadCount === 0"
                      class="mt-2 text-sm text-gray-500"
                    >
                      No new notifications
                    </p>
                    <RouterLink
                      v-else
                      to="/notifications"
                      class="mt-2 text-sm text-primary-600 hover:text-primary-700"
                    >
                      View all notifications
                    </RouterLink>
                  </div>
                </MenuItems>
              </transition>
            </Menu>

            <!-- User Menu -->
            <Menu as="div" class="relative">
              <MenuButton
                class="flex items-center p-2 space-x-2 transition-colors rounded-lg hover:bg-gray-100"
              >
                <UserCircleIcon class="w-8 h-8 text-gray-700" />
                <span class="hidden text-sm font-medium text-gray-700 lg:block">
                  {{ authStore.user?.name }}
                </span>
              </MenuButton>

              <transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
              >
                <MenuItems
                  class="absolute right-0 z-10 w-56 mt-2 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                >
                  <div class="p-2">
                    <MenuItem v-slot="{ active }">
                      <RouterLink
                        :to="getDashboardRoute()"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'flex items-center px-4 py-2 text-sm text-gray-700 rounded-md',
                        ]"
                      >
                        <BriefcaseIcon class="w-5 h-5 mr-3 text-gray-400" />
                        Dashboard
                      </RouterLink>
                    </MenuItem>

                    <MenuItem v-if="authStore.isAdmin" v-slot="{ active }">
                      <RouterLink
                        to="/admin/dashboard"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'flex items-center px-4 py-2 text-sm text-gray-700 rounded-md',
                        ]"
                      >
                        <ChartBarIcon class="w-5 h-5 mr-3 text-gray-400" />
                        Admin Portal
                      </RouterLink>
                    </MenuItem>

                    <MenuItem v-if="authStore.isStudent" v-slot="{ active }">
                      <RouterLink
                        to="/student/profile"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'flex items-center px-4 py-2 text-sm text-gray-700 rounded-md',
                        ]"
                      >
                        <UserCircleIcon class="w-5 h-5 mr-3 text-gray-400" />
                        My Profile
                      </RouterLink>
                    </MenuItem>

                    <MenuItem v-if="authStore.isStudent" v-slot="{ active }">
                      <RouterLink
                        to="/student/saved-jobs"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'flex items-center px-4 py-2 text-sm text-gray-700 rounded-md',
                        ]"
                      >
                        <BookmarkIcon class="w-5 h-5 mr-3 text-gray-400" />
                        Saved Jobs
                      </RouterLink>
                    </MenuItem>

                    <MenuItem v-if="authStore.isEmployer" v-slot="{ active }">
                      <RouterLink
                        to="/employer/profile"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'flex items-center px-4 py-2 text-sm text-gray-700 rounded-md',
                        ]"
                      >
                        <Cog6ToothIcon class="w-5 h-5 mr-3 text-gray-400" />
                        Company Profile
                      </RouterLink>
                    </MenuItem>

                    <MenuItem v-if="authStore.isEmployer" v-slot="{ active }">
                      <RouterLink
                        to="/employer/jobs"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'flex items-center px-4 py-2 text-sm text-gray-700 rounded-md',
                        ]"
                      >
                        <BriefcaseIcon class="w-5 h-5 mr-3 text-gray-400" />
                        My Jobs
                      </RouterLink>
                    </MenuItem>

                    <div class="my-2 border-t border-gray-200"></div>

                    <MenuItem v-slot="{ active }">
                      <button
                        @click="handleLogout"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'flex w-full items-center px-4 py-2 text-sm text-red-600 rounded-md',
                        ]"
                      >
                        <ArrowRightOnRectangleIcon
                          class="w-5 h-5 mr-3 text-red-600"
                        />
                        Logout
                      </button>
                    </MenuItem>
                  </div>
                </MenuItems>
              </transition>
            </Menu>
          </div>

          <!-- Guest Buttons -->
          <div v-else class="flex items-center space-x-3">
            <RouterLink
              to="/login"
              class="px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:text-primary-600"
            >
              Login
            </RouterLink>
            <RouterLink to="/register" class="btn btn-primary btn-sm">
              Sign Up
            </RouterLink>
          </div>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="p-2 text-gray-700 transition-colors rounded-lg hover:bg-gray-100"
          >
            <Bars3Icon v-if="!mobileMenuOpen" class="w-6 h-6" />
            <XMarkIcon v-else class="w-6 h-6" />
          </button>
        </div>
      </div>

      <!-- Mobile menu -->
      <div
        v-if="mobileMenuOpen"
        class="py-4 border-t border-gray-200 md:hidden"
      >
        <div class="space-y-1">
          <RouterLink
            v-for="item in navigation"
            :key="item.name"
            :to="item.href"
            class="block px-3 py-2 text-base font-medium text-gray-700 rounded-lg hover:bg-gray-100"
            @click="mobileMenuOpen = false"
          >
            {{ item.name }}
          </RouterLink>

          <template v-if="authStore.isAuthenticated">
            <div class="my-2 border-t border-gray-200"></div>
            <RouterLink
              :to="getDashboardRoute()"
              class="block px-3 py-2 text-base font-medium text-gray-700 rounded-lg hover:bg-gray-100"
              @click="mobileMenuOpen = false"
            >
              Dashboard
            </RouterLink>
            <RouterLink
              v-if="authStore.isStudent"
              to="/student/profile"
              class="block px-3 py-2 text-base font-medium text-gray-700 rounded-lg hover:bg-gray-100"
              @click="mobileMenuOpen = false"
            >
              My Profile
            </RouterLink>
            <RouterLink
              v-if="authStore.isEmployer"
              to="/employer/profile"
              class="block px-3 py-2 text-base font-medium text-gray-700 rounded-lg hover:bg-gray-100"
              @click="mobileMenuOpen = false"
            >
              Company Profile
            </RouterLink>
            <button
              @click="handleLogout"
              class="w-full px-3 py-2 text-base font-medium text-left text-red-600 rounded-lg hover:bg-gray-100"
            >
              Logout
            </button>
          </template>

          <template v-else>
            <div class="my-2 border-t border-gray-200"></div>
            <RouterLink
              to="/login"
              class="block px-3 py-2 text-base font-medium text-gray-700 rounded-lg hover:bg-gray-100"
              @click="mobileMenuOpen = false"
            >
              Login
            </RouterLink>
            <RouterLink
              to="/register"
              class="block px-3 py-2 text-base font-medium rounded-lg text-primary-600 hover:bg-gray-100"
              @click="mobileMenuOpen = false"
            >
              Sign Up
            </RouterLink>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>
