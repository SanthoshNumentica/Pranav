<template>
    <div class="flex flex-wrap items-center gap-3">
        <!-- Period Type -->
        <div class="w-full sm:w-40 lg:w-44">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">Period</span>
            <Select v-model="localFilters.filter_type" @update:modelValue="onPeriodTypeChange">
                <SelectTrigger class="h-9 bg-slate-50/50">
                    <SelectValue placeholder="Period" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="day">Day</SelectItem>
                    <SelectItem value="week">Week</SelectItem>
                    <SelectItem value="month">Month</SelectItem>
                    <SelectItem value="year">Year</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Period Option -->
        <div class="w-full sm:w-40 lg:w-44">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">Option</span>
            <Select v-model="localFilters.filter_option" @update:modelValue="emitChange">
                <SelectTrigger class="h-9 bg-slate-50/50">
                    <SelectValue placeholder="Option" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="opt in currentSubOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Custom Date/Month/Year Pickers -->
        <template v-if="localFilters.filter_option === 'custom'">
            <div v-if="localFilters.filter_type === 'day'" class="w-full sm:w-40">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">Date</span>
                <input v-model="localFilters.from_date" type="date" :max="todayDate" @change="emitChange"
                    class="h-9 w-full px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
            </div>

            <div v-if="localFilters.filter_type === 'week'" class="flex gap-2 w-full sm:w-auto">
                <div class="w-32">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">Start</span>
                    <input v-model="localFilters.from_date" type="date" :max="todayDate" @change="emitChange"
                        class="h-9 w-full px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>
                <div class="w-32">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">End</span>
                    <input v-model="localFilters.to_date" type="date" :max="todayDate" :min="localFilters.from_date"
                        @change="emitChange"
                        class="h-9 w-full px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>
            </div>

            <div v-if="localFilters.filter_type === 'month'" class="flex gap-2 w-full sm:w-auto">
                <div class="w-32">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">Start</span>
                    <input v-model="localFilters.from_date" type="month" :max="todayMonth" @change="emitChange"
                        class="h-9 w-full px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>
                <div class="w-32">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">End</span>
                    <input v-model="localFilters.to_date" type="month" :max="todayMonth"
                        :min="localFilters.from_date" @change="emitChange"
                        class="h-9 w-full px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
                </div>
            </div>

            <div v-if="localFilters.filter_type === 'year'" class="flex gap-2 w-full sm:w-auto">
                <div class="w-28">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">Start</span>
                    <Select v-model="localFilters.from_date" @update:modelValue="emitChange">
                        <SelectTrigger class="h-9 bg-slate-50">
                            <SelectValue placeholder="Year" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="year in yearOptions" :key="year" :value="year">
                                {{ year }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="w-28">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1 block">End</span>
                    <Select v-model="localFilters.to_date" @update:modelValue="emitChange">
                        <SelectTrigger class="h-9 bg-slate-50">
                            <SelectValue placeholder="Year" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="year in yearOptions.filter(y => y >= localFilters.from_date)"
                                :key="year" :value="year">
                                {{ year }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </template>

        <!-- Active Range Display (Minimal) -->
        <div v-if="activeRangeLabel"
            class="flex items-center gap-1.5 text-[10px] font-bold text-primary bg-primary/5 px-2.5 py-1.5 rounded-lg h-9 self-end mb-[1px]">
            <CalendarIcon class="h-3 w-3" />
            {{ activeRangeLabel }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from "vue";
import { Calendar as CalendarIcon } from "lucide-vue-next";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "../ui/select";

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    }
});

const emit = defineEmits(["update:modelValue", "change"]);

const localFilters = reactive({ ...props.modelValue });

const subOptions = {
    day: [
        { label: "Today", value: "today" },
        { label: "Yesterday", value: "yesterday" },
        { label: "Day Before Yesterday", value: "before_yesterday" },
        { label: "Custom", value: "custom" },
    ],
    week: [
        { label: "This Week", value: "this_week" },
        { label: "Last Week", value: "last_week" },
        { label: "Last 2 Weeks", value: "last_2_weeks" },
        { label: "Custom", value: "custom" },
    ],
    month: [
        { label: "This Month", value: "this_month" },
        { label: "Last Month", value: "last_month" },
        { label: "Last 3 Months", value: "last_3_months" },
        { label: "Custom", value: "custom" },
    ],
    year: [
        { label: "This Year", value: "this_year" },
        { label: "Last Year", value: "last_year" },
        { label: "Last 3 Years", value: "last_3_years" },
        { label: "Custom", value: "custom" },
    ],
};

const currentSubOptions = computed(() => subOptions[localFilters.filter_type] || []);

const todayDate = new Date().toISOString().split("T")[0];
const todayMonth = new Date().toISOString().slice(0, 7);
const yearOptions = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = currentYear; i >= currentYear - 10; i--) {
        years.push(i.toString());
    }
    return years;
});

const onPeriodTypeChange = () => {
    localFilters.filter_option = subOptions[localFilters.filter_type][0].value;
    if (localFilters.filter_type === 'year') {
        localFilters.from_date = new Date().getFullYear().toString();
        localFilters.to_date = new Date().getFullYear().toString();
    } else if (localFilters.filter_type === 'month') {
        localFilters.from_date = new Date().toISOString().slice(0, 7);
        localFilters.to_date = new Date().toISOString().slice(0, 7);
    } else {
        localFilters.from_date = new Date().toISOString().split("T")[0];
        localFilters.to_date = new Date().toISOString().split("T")[0];
    }
    emitChange();
};

const activeRangeLabel = computed(() => {
    const type = localFilters.filter_type;
    const option = localFilters.filter_option;
    const start = localFilters.from_date;
    const end = localFilters.to_date;

    if (option !== 'custom') {
        const periodStr = type.charAt(0).toUpperCase() + type.slice(1);
        const optLabel = subOptions[type]?.find(o => o.value === option)?.label || option;
        return `${periodStr}: ${optLabel}`;
    }

    const opt = { day: 'numeric', month: 'short', year: 'numeric' };
    if (type === 'day') {
        return `Date: ${new Date(start).toLocaleDateString('en-GB', opt)}`;
    } else if (type === 'week') {
        return `Range: ${new Date(start).toLocaleDateString('en-GB', opt)} — ${new Date(end).toLocaleDateString('en-GB', opt)}`;
    } else if (type === 'month') {
        // For type="month" input, "start" might be "YYYY-MM"
        const startObj = new Date(start + "-01");
        const endObj = new Date(end + "-01");
        const startLabel = startObj.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' });
        const endLabel = endObj.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' });
        return `Months: ${startLabel} — ${endLabel}`;
    } else if (type === 'year') {
        return `Years: ${start} — ${end}`;
    }
    return "";
});

const emitChange = () => {
    emit("update:modelValue", { ...localFilters });
    emit("change", { ...localFilters });
};

watch(() => props.modelValue, (newVal) => {
    Object.assign(localFilters, newVal);
}, { deep: true });

onMounted(() => {
    // Ensure the label is generated initially if provided
    emitChange();
});
</script>
