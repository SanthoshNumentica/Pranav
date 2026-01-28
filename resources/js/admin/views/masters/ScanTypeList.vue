<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Scan Types</h1>
        <p class="text-sm text-slate-500 mt-1">Manage available scan types.</p>
      </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-hidden">
      <div v-if="loading" class="p-20 text-center text-slate-400">Loading...</div>
      <table v-else class="w-full">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/50">
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">ID</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Name</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Created At</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="type in scanTypes" :key="type.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50">
            <td class="px-6 py-4 text-sm font-medium text-slate-900">#{{ type.id }}</td>
            <td class="px-6 py-4 text-sm text-slate-600">{{ type.name }}</td>
            <td class="px-6 py-4 text-sm text-slate-400">{{ new Date(type.created_at).toLocaleDateString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const scanTypes = ref([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const response = await axios.get('/api/v1/masters/scan-types');
    if (response.data.success) {
      scanTypes.value = response.data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
});
</script>
