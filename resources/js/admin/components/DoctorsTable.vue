<template>
  <div class="overflow-x-auto custom-scrollbar">
    <table class="w-full border-separate border-spacing-0">
      <thead>
        <tr class="border-b border-slate-200 bg-slate-50/50">
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">S.No</th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Name</th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Doctor ID</th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Mobile No</th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Email ID</th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Gender</th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">DOB</th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Address</th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Updated On</th>
          <th class="px-3 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 italic">
        <tr 
          v-for="(doctor, index) in doctors" 
          :key="doctor.id"
          class="group hover:bg-primary/5 transition-colors duration-300"
        >
          <td class="px-3 py-4 text-sm text-slate-500">
            {{ index + 1 }}
          </td>
          <td class="px-3 py-4">
            <span class="text-sm font-semibold text-slate-900">{{ doctor.name }}</span>
          </td>
          <td class="px-3 py-4 text-sm text-primary font-medium">
            {{ doctor.doctor_id || 'N/A' }}
          </td>
          <td class="px-3 py-4 text-sm text-slate-600">
            {{ doctor.mobile_no || 'N/A' }}
          </td>
          <td class="px-3 py-4 text-sm text-slate-600 truncate max-w-[120px]">
            {{ doctor.email_id || 'N/A' }}
          </td>
          <td class="px-3 py-4 text-sm text-slate-600">
            {{ doctor.gender?.gender_name || 'N/A' }}
          </td>
          <td class="px-3 py-4 text-sm text-slate-600">
            {{ doctor.dob }}
          </td>
          <td class="px-3 py-4 text-sm text-slate-500 truncate max-w-[150px]">
            {{ doctor.address || 'N/A' }}
          </td>
          <td class="px-3 py-4 text-sm text-slate-500">
            {{ new Date(doctor.updated_at).toLocaleDateString() }}
          </td>
          <td class="px-3 py-4 text-right">
            <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <button
                @click="$emit('view-info', doctor)"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
                title="View Info"
              >
                <Eye class="h-4 w-4" />
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    
    <div v-if="doctors.length === 0" class="text-center py-20 animate-in fade-in duration-500">
        <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 mb-4">
            <StethoscopeIcon class="h-6 w-6 text-slate-400" />
        </div>
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 italic">
            No doctors found.
        </p>
    </div>
  </div>
</template>

<script setup>
import { Eye, Stethoscope as StethoscopeIcon } from 'lucide-vue-next';

defineProps({
  doctors: {
    type: Array,
    required: true
  }
});

defineEmits(['view-info']);
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
</style>
