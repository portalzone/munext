import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "home",
      component: () => import("../views/Home.vue"),
    },
    {
      path: "/login",
      name: "login",
      component: () => import("../views/auth/Login.vue"),
      meta: { guest: true },
    },
    {
      path: "/register",
      name: "register",
      component: () => import("../views/auth/Register.vue"),
      meta: { guest: true },
    },
    {
      path: "/verify",
      name: "verify",
      component: () => import("../views/auth/Verify.vue"),
      meta: { requiresAuth: true },
    },
    {
      path: "/jobs",
      name: "jobs",
      component: () => import("../views/jobs/JobList.vue"),
    },
    {
      path: "/jobs/:id",
      name: "job-detail",
      component: () => import("../views/jobs/JobDetail.vue"),
    },
    // Student Routes
    {
      path: "/student/dashboard",
      name: "student-dashboard",
      component: () => import("../views/student/Dashboard.vue"),
      meta: { requiresAuth: true, roles: ["student", "alumni"] },
    },
    {
      path: "/student/profile",
      name: "student-profile",
      component: () => import("../views/student/Profile.vue"),
      meta: { requiresAuth: true, roles: ["student", "alumni"] },
    },
    {
      path: "/student/applications",
      name: "student-applications",
      component: () => import("../views/student/Applications.vue"),
      meta: { requiresAuth: true, roles: ["student", "alumni"] },
    },
    {
      path: "/student/saved-jobs",
      name: "saved-jobs",
      component: () => import("../views/student/SavedJobs.vue"),
      meta: { requiresAuth: true, roles: ["student", "alumni"] },
    },
    // Admin Routes
    {
      path: "/admin/dashboard",
      name: "admin-dashboard",
      component: () => import("../views/admin/Dashboard.vue"),
      meta: { requiresAuth: true, roles: ["admin"] },
    },
    {
      path: "/admin/users",
      name: "admin-users",
      component: () => import("../views/admin/Users.vue"),
      meta: { requiresAuth: true, roles: ["admin"] },
    },
    {
      path: "/admin/jobs",
      name: "admin-jobs",
      component: () => import("../views/admin/Jobs.vue"),
      meta: { requiresAuth: true, roles: ["admin"] },
    },
    {
      path: "/admin/analytics",
      name: "admin-analytics",
      component: () => import("../views/admin/Analytics.vue"),
      meta: { requiresAuth: true, roles: ["admin"] },
    },
    // Employer Routes
    {
      path: "/employer/dashboard",
      name: "employer-dashboard",
      component: () => import("../views/employer/Dashboard.vue"),
      meta: { requiresAuth: true, roles: ["employer"] },
    },
    {
      path: "/employer/profile",
      name: "employer-profile",
      component: () => import("../views/employer/Profile.vue"),
      meta: { requiresAuth: true, roles: ["employer"] },
    },
    {
      path: "/employer/jobs",
      name: "employer-jobs",
      component: () => import("../views/employer/MyJobs.vue"),
      meta: { requiresAuth: true, roles: ["employer"] },
    },
    {
      path: "/employer/jobs/create",
      name: "create-job",
      component: () => import("../views/employer/CreateJob.vue"),
      meta: { requiresAuth: true, roles: ["employer"] },
    },
    {
      path: "/employer/jobs/:id/edit",
      name: "edit-job",
      component: () => import("../views/employer/EditJob.vue"),
      meta: { requiresAuth: true, roles: ["employer"] },
    },
    {
      path: "/employer/jobs/:id/applications",
      name: "job-applications",
      component: () => import("../views/employer/JobApplications.vue"),
      meta: { requiresAuth: true, roles: ["employer"] },
    },
    // 404
    {
      path: "/:pathMatch(.*)*",
      name: "not-found",
      component: () => import("../views/NotFound.vue"),
    },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  },
});

// Navigation guards
// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // If we have a token but no user, try to fetch the user first
  if (authStore.token && !authStore.user && !authStore.loading) {
    try {
      await authStore.fetchUser();
    } catch (error) {
      console.error("Failed to fetch user:", error);
    }
  }

  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      next({ name: "login", query: { redirect: to.fullPath } });
      return;
    }

    // Check role-based access
    if (to.meta.roles && !to.meta.roles.includes(authStore.user?.role)) {
      next({ name: "home" });
      return;
    }
  }

  // Redirect authenticated users away from guest pages
  if (to.meta.guest && authStore.isAuthenticated) {
    if (
      authStore.user?.role === "student" ||
      authStore.user?.role === "alumni"
    ) {
      next({ name: "student-dashboard" });
    } else if (authStore.user?.role === "employer") {
      next({ name: "employer-dashboard" });
    } else if (authStore.user?.role === "admin") {
      next({ name: "admin-dashboard" });
    } else {
      next({ name: "home" });
    }
    return;
  }

  next();
});

export default router;
