<template>
  <div class="space-y-8 pb-20 max-w-5xl mx-auto">
    <!-- Header Skeleton -->
    <div class="flex items-center justify-between">
      <div class="space-y-3">
        <div class="h-8 w-48 bg-slate-200 rounded-lg animate-pulse"></div>
        <div class="h-4 w-64 bg-slate-100 rounded-lg animate-pulse"></div>
      </div>
      <div class="h-10 w-24 bg-slate-200 rounded-2xl animate-pulse"></div>
    </div>

    <!-- Tabs Skeleton -->
    <div class="flex space-x-1 mb-6 overflow-x-auto">
      <div
        v-for="i in 4"
        :key="i"
        class="h-10 w-32 bg-slate-200 rounded-lg animate-pulse shrink-0"
      ></div>
    </div>

    <!-- Content Skeleton (Card) -->
    <div
      class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-8"
    >
      <!-- Section Header -->
      <div class="flex items-center gap-3 mb-6">
        <div class="h-10 w-10 rounded-2xl bg-slate-100 animate-pulse"></div>
        <div class="h-6 w-40 bg-slate-200 rounded-lg animate-pulse"></div>
      </div>

      <!-- Grid Layout -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <div class="h-4 w-24 bg-slate-100 rounded animate-pulse"></div>
          <div class="h-12 w-full bg-slate-50 rounded-xl animate-pulse"></div>
        </div>
        <div class="space-y-2">
          <div class="h-4 w-24 bg-slate-100 rounded animate-pulse"></div>
          <div class="h-12 w-full bg-slate-50 rounded-xl animate-pulse"></div>
        </div>
        <div class="space-y-2 md:col-span-2">
          <div class="h-4 w-32 bg-slate-100 rounded animate-pulse"></div>
          <div class="h-24 w-full bg-slate-50 rounded-xl animate-pulse"></div>
        </div>
      </div>
    </div>
  </div>
</template>
