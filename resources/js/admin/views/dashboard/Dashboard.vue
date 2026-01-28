<template>
  <div class="space-y-8">
    <!-- Page Header -->
    <div class="animate-in fade-in slide-in-from-top-4 duration-500">
      <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
      <p class="text-sm text-slate-500 mt-1">System overview and key performance metrics.</p>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-100">
      <div 
        v-for="stat in stats" 
        :key="stat.label" 
        class="p-6 rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group"
      >
        <div class="flex items-center justify-between mb-4">
          <div :class="cn('p-3 rounded-2xl transition-colors duration-300', 'bg-primary/5 text-primary group-hover:bg-primary group-hover:text-white')">
            <component :is="stat.icon" class="h-6 w-6" />
          </div>
          <span class="text-xs font-bold text-emerald-500 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
            +12.5%
          </span>
        </div>
        <div>
          <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-400 mb-1 leading-none">{{ stat.label }}</p>
          <p class="text-3xl font-bold text-slate-900 tracking-tight">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions or Placeholder for other dashboard cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-in fade-in duration-1000 delay-300">
      <div class="bg-white h-64 rounded-[2.5rem] border border-slate-200 border-dashed flex items-center justify-center text-slate-400">
        <div class="text-center">
          <ChartBarIcon class="h-10 w-10 mx-auto mb-2 opacity-20" />
          <p class="text-sm font-medium">Monthly Revenue Chart Placeholder</p>
        </div>
      </div>
      <div class="bg-white h-64 rounded-[2.5rem] border border-slate-200 border-dashed flex items-center justify-center text-slate-400">
        <div class="text-center">
          <UsersIcon class="h-10 w-10 mx-auto mb-2 opacity-20" />
          <p class="text-sm font-medium">Recent Activity Placeholder</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  ShoppingBag as ShoppingBagIcon, 
  TrendingUp as TrendingUpIcon, 
  Users as UsersIcon, 
  DollarSign as DollarSignIcon,
  BarChart3 as ChartBarIcon
} from 'lucide-vue-next';

const stats = [
  { label: 'Total Orders', value: '1,254', icon: ShoppingBagIcon },
  { label: 'Revenue', value: '$45,280', icon: DollarSignIcon },
  { label: 'Customers', value: '842', icon: UsersIcon },
  { label: 'Growth', value: '24.5%', icon: TrendingUpIcon },
];

function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}
</script>

<style scoped>
.animate-in {
  animation-fill-mode: forwards;
}
</style>
