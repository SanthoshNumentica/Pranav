<template>
    <TransitionRoot as="template" :show="isOpen">
        <Dialog as="div" class="relative z-50" @close="$emit('close')">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">
                            <div class="bg-white px-6 pt-8 pb-6 sm:p-8 sm:pb-6">
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <DialogTitle as="h3" class="text-xl font-bold text-slate-900">
                                            Set Check-out Time
                                        </DialogTitle>
                                        <p class="text-sm text-slate-500 mt-1">
                                            Case ID: <span class="font-semibold text-primary">#{{ report?.case_id
                                                }}</span>
                                        </p>
                                    </div>
                                    <button @click="$emit('close')"
                                        class="p-2 rounded-full hover:bg-slate-50 text-slate-400 transition-colors">
                                        <XIcon class="h-5 w-5" />
                                    </button>
                                </div>

                                <div class="space-y-6">
                                    <!-- Time Selection -->
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest px-1">
                                            Check-out Time
                                        </label>
                                        <div class="relative group">
                                            <ClockIcon
                                                class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-primary transition-colors" />
                                            <input v-model="form.check_out" type="time"
                                                class="w-full pl-12 pr-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-medium focus:ring-2 focus:ring-primary/20 transition-all" />
                                        </div>
                                        <p v-if="report?.rct_hour" class="text-[10px] text-slate-400 font-medium px-1">
                                            Check-in time was: {{ report.rct_hour }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50/50 px-6 py-4 sm:px-8 flex flex-row-reverse gap-3">
                                <button type="button" @click="handleSave" :disabled="loading"
                                    class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-primary/20 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                    <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
                                    {{ loading ? 'Saving...' : 'Save Time' }}
                                </button>
                                <button type="button" @click="$emit('close')"
                                    class="flex-1 inline-flex justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-700 border border-slate-200 hover:bg-slate-50 transition-all">
                                    Cancel
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
import { ref, watch } from 'vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import {
    X as XIcon,
    Clock as ClockIcon,
    Loader2 as Loader2Icon
} from 'lucide-vue-next';

const props = defineProps({
    isOpen: Boolean,
    report: Object,
    loading: Boolean
});

const emit = defineEmits(['close', 'confirm']);

const form = ref({
    check_out: ''
});

watch(() => props.isOpen, (newVal) => {
    if (newVal && props.report) {
        // Default to current time if no check_out exists
        if (props.report.check_out) {
            form.value.check_out = props.report.check_out;
        } else {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            form.value.check_out = `${hours}:${minutes}`;
        }
    }
});

const handleSave = () => {
    emit('confirm', form.value.check_out);
};
</script>
