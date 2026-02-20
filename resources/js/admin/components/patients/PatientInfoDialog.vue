<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="() => {}">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
        />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div
          class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 flex flex-col h-[90vh] sm:h-[80vh]"
            >
              <!-- Header/Banner - Fixed -->
              <div
                class="relative bg-primary px-6 py-8 sm:px-10 text-white overflow-hidden shrink-0"
              >
                <div
                  class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"
                ></div>
                <div
                  class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"
                ></div>

                <div class="relative flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <div
                      class="h-12 w-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30"
                    >
                      <UserIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                      <h3 class="text-xl font-bold tracking-tight">
                        Patient Details
                      </h3>
                      <p class="text-sm font-medium mt-1 opacity-90">
                        {{ patient?.name }} ({{ patient?.patient_id }})
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <button
                      @click="$emit('edit', patient)"
                      class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition-all active:scale-95"
                    >
                      <EditIcon class="h-4 w-4" />
                      Edit Patient
                    </button>
                    <button
                      @click.stop="close"
                      class="p-2 rounded-xl hover:bg-white/10 transition-colors"
                    >
                      <XIcon class="h-5 w-5 text-white" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Content Body -->
              <div
                class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar space-y-8"
              >
                <!-- Status Badge -->
                <div class="flex justify-end">
                  <span
                    :class="
                      cn(
                        'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider',
                        patient?.status === 'active'
                          ? 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                          : 'bg-slate-50 text-slate-600 border border-slate-100',
                      )
                    "
                  >
                    {{ patient?.status }}
                  </span>
                </div>

                <!-- Fields Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                  <div
                    v-for="(field, label) in detailedInfo"
                    :key="label"
                    class="space-y-1"
                  >
                    <span
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block ml-1"
                      >{{ label }}</span
                    >
                    <p
                      class="text-sm font-semibold text-slate-700 bg-slate-50/50 p-3 rounded-xl border border-slate-100/50"
                    >
                      {{ field || "N/A" }}
                    </p>
                  </div>
                </div>

                <!-- Case History Preview -->
                <div
                  v-if="patient?.case_reports?.length > 0"
                  class="space-y-4 pt-4 border-t border-slate-100"
                >
                  <h4
                    class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
                  >
                    <div class="h-1 w-1 rounded-full bg-primary"></div>
                    Recent Case Reports
                  </h4>
                  <div
                    class="divide-y divide-slate-50 bg-slate-50/50 rounded-2xl border border-slate-100 overflow-hidden"
                  >
                    <div
                      v-for="report in patient.case_reports.slice(0, 5)"
                      :key="report.id"
                      class="p-4 flex items-center justify-between hover:bg-white transition-colors"
                    >
                      <div>
                        <p class="text-sm font-bold text-slate-900">
                          {{ report.case_id }}
                        </p>
                        <p class="text-[10px] text-slate-500">
                          {{ formatDate(report.created_at) }}
                        </p>
                      </div>
                      <span class="text-xs font-bold text-primary">{{
                        report.status
                      }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer Actions -->
              <div
                class="p-6 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0"
              >
                <button
                  @click.stop="close"
                  type="button"
                  class="px-8 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition-all active:scale-95 shadow-lg shadow-slate-900/10"
                >
                  Close
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed } from "vue";
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  X as XIcon,
  User as UserIcon,
  Edit as EditIcon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";

const props = defineProps({
  isOpen: Boolean,
  patient: Object,
});

const emit = defineEmits(["close", "edit"]);

const close = () => {
  if (!props.isOpen) return;
  emit("close");
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const calculateAge = (dob) => {
  if (!dob) return "N/A";
  const today = new Date();
  const birthDate = new Date(dob);
  let age = today.getFullYear() - birthDate.getFullYear();
  const m = today.getMonth() - birthDate.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }
  return age;
};

const detailedInfo = computed(() => {
  if (!props.patient) return {};
  return {
    "Patient Name": props.patient.name,
    "Patient ID": props.patient.patient_id || "N/A",
    "MRN ID": props.patient.mrn_id || "N/A",
    Age: calculateAge(props.patient.dob),
    DOB: formatDate(props.patient.dob),
    Gender: props.patient.gender?.gender_name,
    "Blood Group": props.patient.blood_group?.name,
    "Father / Guardian": props.patient.father_name,
    "Mobile No": props.patient.mobile_no,
    "WhatsApp No": props.patient.whatsapp_no,
    "Email ID": props.patient.email_id,
    Place: props.patient.place,
    Remarks: props.patient.remarks,
    "Created By": props.patient.added_by_user?.name || "N/A",
    "Modified By": props.patient.modified_by_user?.name || "N/A",
  };
});
</script>
