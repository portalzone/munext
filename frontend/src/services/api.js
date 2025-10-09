import axios from "axios";
import router from "../router";

const api = axios.create({
  baseURL:
    import.meta.env.VITE_API_URL ||
    "https://munext-production.up.railway.app/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});
console.log("API base URL:", import.meta.env.VITE_API_URL);

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response?.status === 401) {
      // Unauthorized - clear token and redirect to login
      localStorage.removeItem("token");
      router.push({ name: "login" });
    }

    return Promise.reject(error);
  }
);

export default api;
