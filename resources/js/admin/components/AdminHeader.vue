<template>
  <header class="sticky top-0 z-40 h-16 w-full border-b backdrop-blur-md transition-all duration-300 bg-white/70 border-slate-200 shadow-sm">
    <div class="flex h-full items-center justify-between px-6">
      <!-- Left Section: Search -->
      <div class="flex flex-1 items-center max-w-md">
        <div class="relative w-full group">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
            <SearchIcon class="h-4 w-4 transition-colors duration-200 text-slate-400 group-focus-within:text-primary" />
          </div>
          <input
            type="text"
            placeholder="Search cases, patients..."
            class="block w-full rounded-xl border-0 py-2 pl-10 pr-4 text-sm transition-all duration-200 focus:ring-2 focus:ring-inset bg-slate-100/50 text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-primary/20 focus:shadow-sm"
          />
        </div>
      </div>

      <!-- Right Section: Actions -->
      <div class="flex items-center gap-4">
        <!-- Notifications -->
        <button
          class="relative flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-200 border group bg-white border-slate-200 text-slate-500 hover:text-primary hover:bg-slate-50 shadow-sm"
        >
          <BellIcon class="h-5 w-5 transition-transform group-hover:scale-110" />
          <!-- Pulse Badge -->
          <span class="absolute top-2.5 right-2.5 flex h-2 w-2">
            <span class="animate-pulse-ring absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
          </span>
        </button>

        <!-- User Dropdown -->
        <Menu as="div" class="relative">
          <MenuButton
            class="flex items-center gap-3 pl-1 pr-3 py-1 rounded-xl transition-all duration-200 border bg-white border-slate-200 hover:border-slate-300 text-slate-700 hover:shadow-sm shadow-sm"
          >
            <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-white font-bold text-xs shrink-0">
              {{ userInitials }}
            </div>
            <span class="hidden sm:block text-sm font-semibold truncate max-w-[100px]">{{ user?.name || 'Admin' }}</span>
            <ChevronDownIcon class="h-4 w-4 text-slate-400" />
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
              class="absolute right-0 mt-2 w-56 origin-top-right rounded-2xl p-1.5 shadow-xl border focus:outline-none bg-white border-slate-100 text-slate-700"
            >
              <div class="px-1 py-1">
                <MenuItem v-slot="{ active }">
                  <router-link
                    to="/profile"
                    :class="cn(
                      'group flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm transition-colors',
                      active ? 'bg-primary/10 text-primary font-medium' : ''
                    )"
                  >
                    <UserIcon class="h-4 w-4" />
                    Profile
                  </router-link>
                </MenuItem>
              </div>
              <div class="px-1 py-1 border-t border-slate-100 mt-1 pt-1">
                <MenuItem v-slot="{ active }">
                  <button
                    @click="isConfirmOpen = true"
                    :class="cn(
                      'group flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm transition-colors text-rose-500',
                      active ? 'bg-rose-500/10' : ''
                    )"
                  >
                    <LogOutIcon class="h-4 w-4" />
                    Logout
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </transition>
        </Menu>
      </div>
    </div>
  </header>

  <!-- Logout Confirmation Modal -->
  <ConfirmationModal
    :isOpen="isConfirmOpen"
    title="Confirm Logout"
    description="You are about to log out from the Scan Center platform. Do you wish to proceed?"
    confirmLabel="Logout"
    variant="danger"
    :loading="isLoading"
    @close="isConfirmOpen = false"
    @confirm="handleLogout"
  />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { 
  Search as SearchIcon, 
  Bell as BellIcon, 
  ChevronDown as ChevronDownIcon,
  User as UserIcon,
  LogOut as LogOutIcon
} from 'lucide-vue-next';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import ConfirmationModal from './ConfirmationModal.vue';

const router = useRouter();
const isConfirmOpen = ref(false);
const isLoading = ref(false);
const user = ref(null);

const userInitials = computed(() => {
  if (!user.value?.name) return 'AD';
  return user.value.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
});

const fetchUser = async () => {
  try {
    const response = await axios.get('/api/v1/me');
    if (response.data.success) {
      user.value = response.data.data.user;
    }
  } catch (e) {
    console.error('Header: Failed to fetch user info', e);
  }
};

onMounted(fetchUser);

// Utility for classes
function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}

const handleLogout = async () => {
  console.log('Header: Initiating logout');
  isLoading.value = true;
  try {
    await axios.post('/api/v1/logout');
    console.log('Header: Logout API success');
  } catch (e) {
    console.error('Header: Logout API failed', e);
  } finally {
    console.log('Header: Clearing session and redirecting');
    isLoading.value = false;
    isConfirmOpen.value = false;
    localStorage.removeItem('auth_token');
    delete axios.defaults.headers.common['Authorization'];
    router.push('/login');
  }
};
</script>

<style scoped>
input:focus {
  outline: none;
}
</style>
