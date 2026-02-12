<template>
  <div
    class="min-h-screen flex items-center justify-center p-4 bg-slate-50 text-slate-900 overflow-hidden relative"
  >
    <!-- Abstract Background Ornaments -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div
        class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full blur-[120px] bg-primary/5 opacity-40"
      />
      <div
        class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] rounded-full blur-[120px] bg-primary/5 opacity-40"
      />
    </div>

    <div
      class="w-full max-w-[440px] relative z-10 animate-in fade-in slide-in-from-bottom-8 duration-700"
    >
      <!-- Login Card -->
      <div
        class="rounded-[32px] border p-8 md:p-10 shadow-soft-xl bg-white/80 backdrop-blur-xl border-slate-200/60 transition-all duration-500"
      >
        <div class="flex flex-col items-center mb-10">
          <div
            class="w-16 h-16 rounded-2xl bg-primary flex items-center justify-center shadow-lg shadow-primary/20 mb-6 group transition-transform hover:scale-105 duration-300"
          >
            <ActivityIcon class="h-8 w-8 text-white" />
          </div>
          <h1
            class="text-3xl font-bold tracking-tight text-slate-900 text-center mb-2"
          >
            Pranav Diagnostics Centre
          </h1>
          <p class="text-slate-500 text-center text-sm font-medium">
            Management Portal
          </p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <div
            v-if="errorMessage"
            class="bg-rose-500/10 border border-rose-500/20 text-rose-600 text-[11px] font-bold py-3 px-4 rounded-xl text-center animate-in fade-in slide-in-from-top-2 flex items-center justify-center gap-2"
          >
            <AlertCircleIcon class="h-3.5 w-3.5" />
            {{ errorMessage }}
          </div>

          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Email Address <span class="text-rose-500">*</span></label
            >
            <div class="relative group">
              <MailIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
              />
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="name@example.com"
                class="w-full rounded-2xl py-3.5 pl-11 pr-4 text-sm transition-all duration-200 border outline-none font-medium bg-slate-50/50 border-slate-200 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5"
              />
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center ml-1">
              <label
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400"
                >Password <span class="text-rose-500">*</span></label
              >
            </div>
            <div class="relative group">
              <LockIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
              />
              <input
                v-model="form.password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full rounded-2xl py-3.5 pl-11 pr-4 text-sm transition-all duration-200 border outline-none font-medium bg-slate-50/50 border-slate-200 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5"
              />
            </div>
          </div>

          <div class="pt-4 text-slate-900">
            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-primary hover:bg-primary-700 text-white h-14 rounded-2xl font-bold text-base shadow-lg shadow-primary/20 active:scale-[0.98] transition-all disabled:opacity-50 disabled:pointer-events-none flex items-center justify-center gap-2"
            >
              <Loader2Icon v-if="loading" class="h-5 w-5 animate-spin" />
              <span>{{ loading ? "Authenticating..." : "Sign In" }}</span>
            </button>
          </div>
        </form>

        <!-- Footer -->
        <div class="mt-10 text-center">
          <p
            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"
          >
            &copy; 2026 Scan Center. All rights reserved.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import {
  Activity as ActivityIcon,
  Mail as MailIcon,
  Lock as LockIcon,
  Loader2 as Loader2Icon,
  AlertCircle as AlertCircleIcon,
} from "lucide-vue-next";

import { useAuth } from "../../composables/useAuth";

const { setUser } = useAuth();
const router = useRouter();
const loading = ref(false);
const errorMessage = ref(null);

const form = reactive({
  email: "",
  password: "",
});

onMounted(() => {
  document.documentElement.classList.remove("dark");
});

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = null;

  try {
    await axios.get("/sanctum/csrf-cookie");
    const response = await axios.post("/api/v1/login", form);

    if (response.data.success) {
      const { token, user, permissions } = response.data.data;

      localStorage.setItem("auth_token", token);
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

      // Update global auth state with user and permissions
      setUser(user, permissions);

      router.push("/case-reports");
    }
  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value = "Invalid credentials. Please try again.";
    } else {
      errorMessage.value =
        error.response?.data?.message ||
        "Login failed. Please check your connection.";
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.animate-in {
  animation: animate-in 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes animate-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
