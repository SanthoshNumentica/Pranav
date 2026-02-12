<template>
  <router-link
    v-if="item.url"
    :to="item.url"
    :class="cn(
      'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group relative',
      isActive ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-500 hover:bg-slate-100/80 hover:text-slate-900',
      isSubItem ? 'py-2 px-3 text-xs' : 'text-sm',
      isCollapsed ? 'justify-center px-0 mx-auto w-11 h-11' : ''
    )"
    :title="isCollapsed ? item.title : ''"
  >
    <component 
      v-if="item.icon" 
      :is="item.icon" 
      :class="cn(
        'h-5 w-5 shrink-0 transition-all duration-300',
        isActive ? 'text-primary' : 'text-slate-400 group-hover:text-slate-600 group-hover:scale-110'
      )" 
    />
    <span v-if="!isCollapsed" :class="cn('tracking-wide truncate transition-all', isActive ? 'opacity-100' : 'opacity-80 group-hover:opacity-100')">
      {{ item.title }}
    </span>
    
    <!-- Active Indicator Dot (Collapsed) -->
    <div 
      v-if="isActive && isCollapsed" 
      class="absolute -right-0.5 top-1/2 -translate-y-1/2 w-1 h-4 rounded-full bg-primary"
    />

    <!-- Active Indicator Pill (Expanded) -->
    <div 
      v-if="isActive && !isSubItem && !isCollapsed" 
      class="absolute right-3 w-1.5 h-1.5 rounded-full bg-primary/40"
    />
  </router-link>

  <div
    v-else
    :class="cn(
      'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group cursor-default text-slate-400',
      isSubItem ? 'py-2 px-3 text-xs' : 'text-sm',
      isCollapsed ? 'justify-center px-0 mx-auto w-11 h-11' : ''
    )"
  >
    <component 
      v-if="item.icon" 
      :is="item.icon" 
      class="h-5 w-5 shrink-0" 
    />
    <span v-if="!isCollapsed" class="font-medium tracking-wide truncate opacity-60">{{ item.title }}</span>
  </div>
</template>

<script setup>
function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}

defineProps({
  item: {
    type: Object,
    required: true
  },
  isCollapsed: {
    type: Boolean,
    default: false
  },
  isActive: {
    type: Boolean,
    default: false
  },
  isSubItem: {
    type: Boolean,
    default: false
  }
});
</script>
