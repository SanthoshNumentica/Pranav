<template>
    <div class="h-screen bg-slate-50 overflow-hidden flex flex-col font-sans text-slate-900">
        <!-- Professional Header -->
        <header class="bg-white border-b border-slate-200 px-8 py-6 flex items-center justify-between shadow-sm z-20">
            <div class="flex items-center gap-6">
                <button @click="goBack"
                    class="p-3 rounded-full hover:bg-slate-100 transition-colors text-slate-500 group"
                    title="Back to Dashboard">
                    <ArrowLeftIcon class="h-6 w-6 group-hover:-translate-x-1 transition-transform" />
                </button>
                <div class="h-8 w-[1px] bg-slate-200"></div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Live Check-in Monitor</h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5">Real-time Patient
                        Activities</p>
                </div>
            </div>

            <div class="flex items-center gap-12 text-right">
                <div class="space-y-0.5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Selected Branch</p>
                    <p class="text-lg font-black text-primary">{{ activeBranchName }}</p>
                </div>
                <div class="h-10 w-[1px] bg-slate-200"></div>
                <div class="space-y-0.5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ currentDate }}</p>
                    <p class="text-2xl font-black tabular-nums text-slate-900 leading-none">{{ currentTime }}</p>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 p-12 relative flex flex-col min-h-0 bg-[#f8fafc]">
            <div class="flex-1 relative overflow-hidden">
                <TransitionGroup name="slide-fade" mode="out-in">
                    <div v-if="currentSlideItems.length > 0" :key="currentSlidePage"
                        class="grid grid-cols-2 grid-rows-2 gap-10 h-full">
                        <div v-for="item in currentSlideItems" :key="item.id"
                            class="bg-white rounded-[2.5rem] p-10 flex flex-col justify-center border border-slate-200 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.05)] hover:shadow-[0_40px_80px_-15px_rgba(0,0,0,0.1)] transition-all duration-700 relative overflow-hidden group">
                            <!-- Subtle card accent -->
                            <div class="absolute top-0 left-0 w-2 h-full bg-primary opacity-20"></div>

                            <div class="flex items-center justify-between mb-8">
                                <div
                                    class="bg-slate-50 px-5 py-2 rounded-2xl border border-slate-100 inline-block font-black text-slate-400 text-sm tracking-wider uppercase">
                                    CASE: {{ item.case_id }}
                                </div>
                                <div
                                    class="flex items-center gap-3 text-emerald-600 bg-emerald-50 px-5 py-2 rounded-2xl border border-emerald-100">
                                    <ClockIcon class="h-5 w-5" />
                                    <span class="text-xl font-black tabular-nums">{{ item.check_in }}</span>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 px-1">Patient
                                    Name</p>
                                <h2
                                    class="text-3xl font-black text-slate-900 tracking-tight leading-tight uppercase group-hover:text-primary transition-colors duration-500">
                                    {{ item.patient?.name }}
                                </h2>
                            </div>
                        </div>
                    </div>

                    <!-- No Data State -->
                    <div v-else-if="!loading" key="empty" class="h-full flex flex-col items-center justify-center">
                        <div
                            class="bg-white rounded-[4rem] p-16 border border-slate-200 shadow-2xl text-center max-w-2xl">
                            <div
                                class="h-24 w-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
                                <MonitorOffIcon class="h-12 w-12 text-slate-300" />
                            </div>
                            <h2 class="text-4xl font-black text-slate-900 tracking-tight mb-4">No Active Check-ins Today
                            </h2>
                            <p class="text-slate-400 font-bold uppercase tracking-widest text-sm px-12">Waiting for
                                patients to check-in. The screen will automatically update when activity begins.</p>
                        </div>
                    </div>
                </TransitionGroup>
            </div>
        </main>

        <!-- Progress Footer -->
        <footer class="bg-white border-t border-slate-200 px-12 py-6 flex items-center justify-between z-20">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                    </span>
                    <span class="text-xs font-black text-slate-800 uppercase tracking-widest">System Operational</span>
                </div>
                <div class="h-4 w-[1px] bg-slate-200"></div>
                <div class="flex items-center gap-2">
                    <HistoryIcon class="h-4 w-4 text-slate-400" />
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Current Page: {{
                        currentSlidePage + 1 }} of {{ totalPages || 1 }}</span>
                </div>
            </div>

            <div class="flex gap-4">
                <div v-for="i in totalPages" :key="i" class="h-1.5 rounded-full transition-all duration-700 ease-out"
                    :class="i - 1 === currentSlidePage ? 'w-24 bg-primary shadow-[0_0_15px_rgba(var(--primary-rgb),0.5)]' : 'w-4 bg-slate-200 hover:bg-slate-300 pointer-events-none'">
                </div>
            </div>

            <div class="flex items-center gap-3 text-slate-400">
                <ActivityIcon class="h-4 w-4" />
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Update in {{ nextRefreshSeconds
                    }}s</span>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import {
    ArrowLeft as ArrowLeftIcon,
    Clock as ClockIcon,
    MonitorOff as MonitorOffIcon,
    History as HistoryIcon,
    Activity as ActivityIcon
} from 'lucide-vue-next';
import { useBranchContext } from '../../composables/useBranchContext';

const router = useRouter();
const { selectedBranchId } = useBranchContext();

const currentTime = ref('');
const currentDate = ref('');
const reports = ref([]);
const currentSlidePage = ref(0);
const nextRefreshSeconds = ref(10);
const loading = ref(true);

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    currentDate.value = now.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

const goBack = () => {
    router.push('/');
};

const fetchActiveCheckIns = async () => {
    try {
        const params = {
            filter_type: 'day',
            filter_option: 'today',
            limit: 500 // Simplified fetching
        };
        if (selectedBranchId.value && selectedBranchId.value !== 'all') {
            params.branch_id = selectedBranchId.value;
        }
        const response = await axios.get('/api/v1/case-reports', { params });
        if (response.data.success) {
            // Strictly filter today's cases where check_out is NULL
            reports.value = response.data.data.data.filter(r => r.check_in && !r.check_out);
        }
    } catch (error) {
        console.error('Monitor fetch error:', error);
    } finally {
        loading.value = false;
    }
};

const checkIns = computed(() => reports.value);
const totalPages = computed(() => Math.ceil(checkIns.value.length / 4));

const currentSlideItems = computed(() => {
    const start = currentSlidePage.value * 4;
    return checkIns.value.slice(start, start + 4);
});

const activeBranchName = computed(() => {
    if (selectedBranchId.value === 'all') return 'All Branches';
    return reports.value.length > 0 ? (reports.value[0]?.branch?.name || 'Selected Branch') : 'Main Branch';
});

let mainTimer = null;
let slideTimer = null;

onMounted(() => {
    updateTime();
    fetchActiveCheckIns();
    mainTimer = setInterval(updateTime, 1000);

    slideTimer = setInterval(() => {
        if (nextRefreshSeconds.value > 1) {
            nextRefreshSeconds.value--;
        } else {
            nextRefreshSeconds.value = 10;

            // Loop slides
            if (currentSlidePage.value + 1 < totalPages.value) {
                currentSlidePage.value++;
            } else {
                currentSlidePage.value = 0;
            }

            fetchActiveCheckIns();
        }
    }, 1000);
});

onUnmounted(() => {
    clearInterval(mainTimer);
    clearInterval(slideTimer);
});

watch(selectedBranchId, () => {
    loading.value = true;
    currentSlidePage.value = 0;
    fetchActiveCheckIns();
});
</script>

<style scoped>
/* Smooth slide-fade transition */
.slide-fade-enter-active {
    transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-fade-leave-active {
    transition: all 0.6s cubic-bezier(0.7, 0, 0.84, 0);
}

.slide-fade-enter-from {
    opacity: 0;
    transform: translateX(40px) scale(0.98);
}

.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(-40px) scale(1.02);
}

/* Custom shadow for white cards */
.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
}
</style>
