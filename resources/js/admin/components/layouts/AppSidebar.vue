<template>
  <aside :class="cn(
    'fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 ease-in-out border-r bg-slate-50/50 backdrop-blur-xl',
    isCollapsed ? 'w-20' : 'w-72',
    'border-slate-200 shadow-[1px_0_10px_rgba(0,0,0,0.02)]',
  )
    ">
    <!-- Brand / Logo Area -->
    <div class="h-20 flex items-center px-6 gap-3 shrink-0 border-b border-slate-100">
      <div
        class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center shadow-lg shadow-primary/20 shrink-0 transform transition-transform hover:scale-105 duration-300 cursor-pointer">
        <BrandIcon class="h-6 w-6 text-white" />
      </div>
      <div v-if="!isCollapsed" class="flex flex-col overflow-hidden animate-in fade-in slide-in-from-left-4">
        <span class="font-bold text-base text-slate-900 tracking-tight leading-none uppercase">Pranav</span>
        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Diagnostic Management</span>
      </div>
    </div>

    <!-- Navigation Area -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-6 px-4 space-y-8">
      <!-- Section: General -->
      <div class="space-y-1">
        <p v-if="!isCollapsed"
          class="px-3 mb-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] leading-none">
          Management
        </p>
        <div class="space-y-1">
          <SidebarNavItem v-for="item in navMain" :key="item.title" :item="item" :isCollapsed="isCollapsed"
            :isActive="isActive(item.url)" />
        </div>
      </div>

      <!-- Section: Master Data -->
      <div class="space-y-1">
        <p v-if="!isCollapsed"
          class="px-3 mb-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] leading-none">
          Configuration
        </p>
        <div class="space-y-1">
          <div @click="toggleMasters" :class="cn(
            'flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all duration-200 group',
            mastersOpen && !isCollapsed
              ? 'bg-primary/5 text-primary font-semibold'
              : 'text-slate-500 hover:bg-slate-100/80 hover:text-slate-900',
          )
            ">
            <MasterDataIcon class="h-5 w-5 shrink-0 transition-colors" :class="mastersOpen && !isCollapsed
              ? 'text-primary'
              : 'text-slate-400 group-hover:text-slate-600'
              " />
            <div v-if="!isCollapsed" class="flex flex-1 items-center justify-between">
              <span class="text-sm tracking-wide">Master Data</span>
              <ChevronDownIcon :class="cn(
                'h-4 w-4 transition-transform duration-300 opacity-50',
                mastersOpen ? 'rotate-180' : '',
              )
                " />
            </div>
          </div>

          <!-- Master Data Children -->
          <div v-if="mastersOpen && !isCollapsed"
            class="ml-4 pl-3 border-l-2 border-slate-100 space-y-1 animate-in fade-in slide-in-from-top-2">
            <SidebarNavItem v-for="item in navMaster" :key="item.title" :item="item" :isActive="isActive(item.url)"
              isSubItem />
          </div>
        </div>

        <SidebarNavItem v-for="item in navReports" :key="item.title" :item="item" :isCollapsed="isCollapsed"
          :isActive="isActive(item.url)" />
      </div>

      <!-- Section: System -->
      <div class="space-y-1">
        <p v-if="!isCollapsed"
          class="px-3 mb-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] leading-none">
          System
        </p>
        <div class="space-y-1">
          <SidebarNavItem v-for="item in navSystem" :key="item.title" :item="item" :isCollapsed="isCollapsed"
            :isActive="isActive(item.url)" />
        </div>
      </div>
    </div>

    <!-- Footer / User Area -->
    <div class="p-4 border-t border-slate-100 bg-white/50 backdrop-blur-md">
      <div class="flex gap-2">
        <button @click="isLogoutModalOpen = true" :class="cn(
          'flex-1 flex items-center justify-center h-11 rounded-xl transition-all duration-200 border group',
          isCollapsed
            ? 'bg-white border-slate-200 text-slate-400 hover:text-rose-500 hover:bg-rose-50 hover:border-rose-500/30'
            : 'bg-white border-slate-200 text-slate-500 hover:bg-rose-50 hover:border-rose-500/30 hover:text-rose-600',
        )
          " title="Sign Out">
          <LogOutIcon :class="cn(
            'h-5 w-5 transition-transform group-hover:scale-110',
            !isCollapsed && 'mr-2',
          )
            " />
          <span v-if="!isCollapsed" class="text-sm font-bold tracking-tight">Sign Out</span>
        </button>

        <button @click="isCollapsed = !isCollapsed"
          class="w-11 h-11 flex items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-90">
          <ChevronRightIcon v-if="isCollapsed" class="h-5 w-5" />
          <ChevronLeftIcon v-else class="h-5 w-5" />
        </button>
      </div>
    </div>
  </aside>

  <!-- Content Spacer -->
  <div :class="cn('transition-all duration-300 shrink-0', isCollapsed ? 'w-20' : 'w-72')
    " aria-hidden="true" />

  <!-- Logout Confirmation Modal -->
  <ConfirmationModal :isOpen="isLogoutModalOpen" title="Sign Out"
    description="Are you sure you want to sign out of the Scan Center Admin Panel? Your session will be ended."
    confirmLabel="Sign Out" variant="danger" :loading="logoutLoading" @close="isLogoutModalOpen = false"
    @confirm="handleLogout" />
</template>

<script setup>
import { ref, watch, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import {
  Activity as BrandIcon,
  LayoutDashboard as DashboardIcon,
  FolderTree as CaseIcon,
  Users as PatientsIcon,
  Stethoscope as DoctorsIcon,
  BarChart3 as ReportsIcon,
  Settings2 as MasterDataIcon,
  Settings as SettingsIcon,
  User as UserIcon,
  LogOut as LogOutIcon,
  ChevronDown as ChevronDownIcon,
  ChevronRight as ChevronRightIcon,
  ChevronLeft as ChevronLeftIcon,
  Scan as ScanIcon,
  VenusAndMars as GenderIcon,
  CaseSensitive as CaseSensitiveIcon,
  Shield as ShieldIcon,
  Building2 as BuildingsIcon,
  Building as BuildingIcon,
  Tag as TagIcon,
  CreditCard as CreditCardIcon,
  Wallet as WalletIcon,
  Monitor as MonitorIcon,
} from "lucide-vue-next";
import SidebarNavItem from "../ui/SidebarNavItem.vue";
import ConfirmationModal from "../ui/ConfirmationModal.vue";
import { usePermissions } from "../../composables/usePermissions";

const { hasPermission } = usePermissions();

const route = useRoute();
const router = useRouter();
const isCollapsed = ref(false);
const mastersOpen = ref(false);
const isLogoutModalOpen = ref(false);
const logoutLoading = ref(false);
const user = ref(null);

const fetchUser = async () => {
  try {
    const response = await axios.get("/api/v1/me");
    if (response.data.success) {
      user.value = response.data.data.user;
    }
  } catch (e) {
    console.error("Sidebar: Failed to fetch user info", e);
  }
};

onMounted(fetchUser);

const navMain = computed(() => {
  const items = [
    { title: "Dashboard", url: "/", icon: DashboardIcon },
    {
      title: "Case Reports",
      url: "/case-reports",
      icon: CaseIcon,
      module: "case-reports",
    },
    {
      title: "Patients",
      url: "/patients",
      icon: PatientsIcon,
      module: "patients",
    },
    { title: "Referers", url: "/referers", icon: UserIcon, module: "referers" },
    {
      title: "Invoices",
      url: "/invoices",
      icon: CreditCardIcon,
      module: "invoices",
    },
  ];
  return items.filter((item) => !item.module || hasPermission(item.module));
});

const navReports = computed(() => {
  const items = [
    {
      title: "Reports",
      url: "/reports",
      icon: ReportsIcon,
      module: "reports",
    },
  ];
  return items.filter((item) => !item.module || hasPermission(item.module));
});

const navMaster = computed(() => {
  const items = [
    {
      title: "Scan Types",
      url: "/masters/scan-types",
      icon: ScanIcon,
      module: "scan-types",
    },
    {
      title: "Genders",
      url: "/masters/genders",
      icon: GenderIcon,
      module: "genders",
    },
    {
      title: "Titles",
      url: "/masters/titles",
      icon: CaseSensitiveIcon,
      module: "titles",
    },
    {
      title: "Referer Types",
      url: "/masters/referer-types",
      icon: BuildingIcon,
      module: "referer-types",
    },
    {
      title: "Discounts",
      url: "/masters/discounts",
      icon: TagIcon,
      module: "discounts",
    },
    {
      title: "Payment Methods",
      url: "/masters/payment-methods",
      icon: WalletIcon,
      module: "payment-methods",
    },
  ];
  return items.filter((item) => !item.module || hasPermission(item.module));
});

const navSystem = computed(() => {
  const items = [
    { title: "Users", url: "/users", icon: UserIcon, module: "user" },
    { title: "Roles", url: "/roles", icon: ShieldIcon, module: "role" },
    {
      title: "Branches",
      url: "/branches",
      icon: BuildingsIcon,
      module: "branch",
    },
    { title: "Settings", url: "/profile", icon: SettingsIcon },
  ];
  return items.filter((item) => !item.module || hasPermission(item.module));
});

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const isActive = (path) => {
  if (path === "/") return route.path === "/";
  return route.path.startsWith(path);
};

const toggleMasters = () => {
  if (isCollapsed.value) isCollapsed.value = false;
  mastersOpen.value = !mastersOpen.value;
};

const handleLogout = async () => {
  console.log("Sidebar: Initiating logout");
  logoutLoading.value = true;
  try {
    await axios.post("/api/v1/logout");
    console.log("Sidebar: Logout API success");
  } catch (e) {
    console.error("Sidebar: Logout API failed", e);
  } finally {
    console.log("Sidebar: Clearing session and redirecting");
    logoutLoading.value = false;
    isLogoutModalOpen.value = false;
    localStorage.removeItem("auth_token");
    delete axios.defaults.headers.common["Authorization"];
    router.push("/login");
  }
};

// Auto-expand reports if current route is a report
watch(
  () => route.path,
  (path) => {
    if (path.startsWith("/masters")) mastersOpen.value = true;
  },
  { immediate: true },
);
</script>

<style>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

/* Animations */
.animate-in {
  animation-duration: 0.3s;
  animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  animation-fill-mode: forwards;
}

@keyframes fade-in {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes slide-in-left {
  from {
    transform: translateX(-10px);
    opacity: 0;
  }

  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slide-in-top {
  from {
    transform: translateY(-10px);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes zoom-in {
  from {
    transform: scale(0.95);
    opacity: 0;
  }

  to {
    transform: scale(1);
    opacity: 1;
  }
}

.fade-in {
  animation-name: fade-in;
}

.slide-in-from-left-4 {
  animation-name: slide-in-left;
}

.slide-in-from-top-2 {
  animation-name: slide-in-top;
}

.zoom-in-95 {
  animation-name: zoom-in;
}
</style>
