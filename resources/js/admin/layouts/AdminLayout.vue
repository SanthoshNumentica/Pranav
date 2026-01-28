<template>
  <div class="min-h-screen flex overflow-hidden bg-slate-50 text-slate-900">
    <AppSidebar />
    <div class="flex-1 flex flex-col min-w-0">
      <AdminHeader />
      <main class="flex-1 overflow-auto custom-scrollbar">
        <div class="p-8">
          <router-view v-slot="{ Component }">
            <transition name="page" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import AppSidebar from '../components/AppSidebar.vue';
import AdminHeader from '../components/AdminHeader.vue';

function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}

onMounted(() => {
  // Ensure dark mode class is removed
  document.documentElement.classList.remove('dark');
  localStorage.removeItem('theme');
});
</script>

<style>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
