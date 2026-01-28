<template>
  <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Account Settings</h1>
      <p class="text-sm text-slate-500 mt-1">Manage your administrator profile and security preferences.</p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left: Profile Summary -->
      <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-200 text-center shadow-sm">
          <div class="w-24 h-24 rounded-[2rem] bg-primary flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4 shadow-lg shadow-primary/20">
            {{ userInitials }}
          </div>
          <h2 class="text-xl font-bold text-slate-900">{{ user.name }}</h2>
          <p class="text-xs font-bold text-primary uppercase tracking-widest mt-1">{{ user.role }}</p>
          <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col gap-2">
            <div class="flex items-center justify-between text-sm">
              <span class="text-slate-500">Status</span>
              <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600 text-[10px] font-bold uppercase tracking-wider">{{ user.status }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-slate-500">Joined</span>
              <span class="font-medium text-slate-700">{{ formattedDate }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Edit Form -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 ml-1">Full Name</label>
                <div class="relative group">
                  <UserIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors" />
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full rounded-2xl py-3 pl-11 pr-4 text-sm transition-all duration-200 border outline-none font-medium bg-slate-50 border-slate-200 text-slate-900 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5"
                  />
                </div>
              </div>
              <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 ml-1">Email Address</label>
                <div class="relative group">
                  <MailIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors" />
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full rounded-2xl py-3 pl-11 pr-4 text-sm transition-all duration-200 border outline-none font-medium bg-slate-50 border-slate-200 text-slate-900 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5"
                  />
                </div>
              </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
              <h3 class="text-sm font-bold text-slate-900 mb-4">Update Password</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 ml-1">New Password</label>
                  <div class="relative group">
                    <LockIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors" />
                    <input
                      v-model="form.password"
                      type="password"
                      placeholder="Leave blank to keep current"
                      class="w-full rounded-2xl py-3 pl-11 pr-4 text-sm transition-all duration-200 border outline-none font-medium bg-slate-50 border-slate-200 text-slate-900 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5"
                    />
                  </div>
                </div>
                <div class="space-y-2">
                  <label class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 ml-1">Confirm Password</label>
                  <div class="relative group">
                    <LockIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors" />
                    <input
                      v-model="form.password_confirmation"
                      type="password"
                      class="w-full rounded-2xl py-3 pl-11 pr-4 text-sm transition-all duration-200 border outline-none font-medium bg-slate-50 border-slate-200 text-slate-900 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div v-if="status.message" :class="cn('p-4 rounded-xl text-xs font-bold uppercase tracking-widest', status.success ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500')">
              {{ status.message }}
            </div>

            <div class="flex justify-end gap-3 pt-4">
              <button
                type="submit"
                :disabled="loading"
                class="px-8 bg-primary hover:opacity-90 text-white h-12 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 transition-all active:scale-95 disabled:opacity-50 flex items-center gap-2"
              >
                <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
                <span>Save Changes</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { 
  User as UserIcon, 
  Mail as MailIcon, 
  Lock as LockIcon,
  Loader2 as Loader2Icon
} from 'lucide-vue-next';
import axios from 'axios';

const user = ref({
  name: 'Administrator',
  email: 'admin@bennycards.com',
  role: 'Admin',
  status: 'active',
  created_at: new Date().toISOString()
});

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const loading = ref(false);
const status = ref({
  success: false,
  message: ''
});

const userInitials = computed(() => {
  return user.value.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
});

const formattedDate = computed(() => {
  if (!user.value.created_at) return 'N/A';
  
  const date = new Date(user.value.created_at);
  // Check if date is valid
  if (isNaN(date.getTime())) return 'N/A';
  
  // Check if it's the epoch (1970) which usually means null in some context
  if (date.getFullYear() <= 1970) return 'Recent';

  return date.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric'
  });
});

const cn = (...classes) => classes.filter(Boolean).join(' ');

const fetchUser = async () => {
  try {
    const response = await axios.get('/api/v1/me');
    if (response.data.success) {
      user.value = response.data.data.user;
      form.value.name = user.value.name;
      form.value.email = user.value.email;
    }
  } catch (e) {
    console.error('Failed to fetch user info', e);
  }
};

const handleSubmit = async () => {
  loading.value = true;
  status.value.message = '';
  
  try {
    const response = await axios.put('/api/v1/me', form.value);
    if (response.data.success) {
      status.value = {
        success: true,
        message: 'Your profile has been updated successfully'
      };
      user.value = response.data.data.user;
      form.value.password = '';
      form.value.password_confirmation = '';
    }
  } catch (e) {
    status.value = {
      success: false,
      message: e.response?.data?.message || 'Failed to update profile'
    };
  } finally {
    loading.value = false;
  }
};

onMounted(fetchUser);
</script>
