<template>
  <main
    ref="viewportContainer"
    class="flex-grow bg-slate-950 flex items-center justify-center relative cursor-crosshair group"
  >
    <div ref="dicomElement" class="w-full h-full" @contextmenu.prevent></div>

    <!-- Loading / No Data Overlay -->
    <Transition
      enter-active-class="transition duration-500 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-300 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="loading"
        class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/90 backdrop-blur-xl z-20"
      >
        <div class="relative mb-8">
          <div
            class="h-24 w-24 rounded-3xl border-2 border-primary/20 animate-[spin_3s_linear_infinite]"
          ></div>
          <div
            class="absolute inset-0 flex items-center justify-center animate-pulse"
          >
            <ActivityIcon class="h-10 w-10 text-primary" />
          </div>
        </div>
        <h2 class="text-xl font-bold text-white tracking-tight mb-2">
          Initializing Engine
        </h2>
        <p class="text-slate-500 font-medium animate-pulse text-sm">
          Processing DICOM Stream...
        </p>
      </div>

      <!-- Error Overlay -->
      <div
        v-else-if="error"
        class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/95 backdrop-blur-2xl z-20 px-10 text-center"
      >
        <div
          class="h-20 w-20 rounded-full bg-red-500/10 flex items-center justify-center border border-red-500/20 mb-6"
        >
          <AlertCircleIcon class="h-10 w-10 text-red-500" />
        </div>
        <h2 class="text-2xl font-bold text-white mb-3">System Interrupted</h2>
        <p class="text-slate-400 max-w-md mb-8 leading-relaxed">
          {{ error }}
        </p>
        <button
          @click="$emit('back')"
          class="h-12 px-8 rounded-2xl bg-white text-slate-950 font-bold hover:bg-slate-200 transition-colors shadow-xl shadow-white/5"
        >
          Return to Case
        </button>
      </div>
    </Transition>

    <!-- Dynamic Overlay Data -->
    <div
      v-if="!loading"
      class="absolute inset-0 pointer-events-none p-10 flex flex-col justify-between z-10 transition-opacity duration-700 group-hover:opacity-100 opacity-40"
    >
      <div class="flex justify-between items-start">
        <div class="flex flex-col gap-1">
          <span
            class="text-[10px] font-black text-primary uppercase tracking-[0.3em] bg-primary/10 self-start px-2 py-0.5 rounded shadow-sm border border-primary/20"
            >Patient</span
          >
          <h1
            class="text-2xl font-bold text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.5)]"
          >
            {{ patientName }}
          </h1>
          <div class="flex items-center gap-3 mt-1.5">
            <div class="flex flex-col">
              <span
                class="text-[8px] font-bold text-slate-500 uppercase tracking-widest leading-none mb-1"
                >Orientation</span
              >
              <span class="text-xs font-bold text-slate-300 drop-shadow-md">{{
                currentOrientation
              }}</span>
            </div>
            <div class="w-px h-6 bg-white/10 mx-1"></div>
            <div class="flex flex-col">
              <span
                class="text-[8px] font-bold text-slate-500 uppercase tracking-widest leading-none mb-1"
                >Protocol</span
              >
              <span class="text-xs font-bold text-slate-300 drop-shadow-md">{{
                currentSeries
              }}</span>
            </div>
          </div>
        </div>
        <div class="text-right flex flex-col items-end gap-1">
          <span
            class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]"
            >Imaging Date</span
          >
          <span class="text-lg font-bold text-white drop-shadow-md">{{
            studyDate
          }}</span>
        </div>
      </div>

      <div class="flex justify-between items-end">
        <div
          class="bg-slate-900/40 backdrop-blur-md rounded-xl p-4 border border-white/5"
        >
          <div class="grid grid-cols-2 gap-x-8 gap-y-2">
            <div class="flex flex-col">
              <span
                class="text-[8px] font-bold text-slate-500 uppercase tracking-widest mb-0.5"
                >Scale</span
              >
              <span class="text-xs font-mono text-primary"
                >{{ (viewport.scale * 100).toFixed(1) }}%</span
              >
            </div>
            <div class="flex flex-col">
              <span
                class="text-[8px] font-bold text-slate-500 uppercase tracking-widest mb-0.5"
                >WC</span
              >
              <span class="text-xs font-mono text-emerald-400">{{
                Math.round(viewport.voi.windowCenter)
              }}</span>
            </div>
            <div class="flex flex-col">
              <span
                class="text-[8px] font-bold text-slate-500 uppercase tracking-widest mb-0.5"
                >WW</span
              >
              <span class="text-xs font-mono text-emerald-400">{{
                Math.round(viewport.voi.windowWidth)
              }}</span>
            </div>
            <div class="flex flex-col">
              <span
                class="text-[8px] font-bold text-slate-500 uppercase tracking-widest mb-0.5"
                >Instance</span
              >
              <span class="text-xs font-mono text-slate-300">{{
                currentImageIndex + 1
              }}</span>
            </div>
          </div>
        </div>
        <div class="flex flex-col items-end gap-2">
          <div
            class="flex items-center gap-2 bg-slate-950/80 rounded-lg px-3 py-1.5 border border-white/10"
          >
            <div
              class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"
            ></div>
            <span
              class="text-[9px] font-black text-white uppercase tracking-widest"
              >Diagnostic Grade</span
            >
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { ActivityIcon, AlertCircleIcon } from "lucide-vue-next";

const props = defineProps({
  loading: Boolean,
  error: String,
  patientName: String,
  studyDate: String,
  currentOrientation: String,
  currentSeries: String,
  viewport: Object,
  currentImageIndex: Number,
});

const emit = defineEmits(["back", "element-ready"]);

const dicomElement = ref(null);

onMounted(() => {
  if (dicomElement.value) {
    emit("element-ready", dicomElement.value);
  }
});

// If the ref becomes available later, emit it then
watch(dicomElement, (newEl) => {
  if (newEl) {
    emit("element-ready", newEl);
  }
});
</script>

<style scoped>
:deep(canvas) {
  width: 100% !important;
  height: 100% !important;
  image-rendering: pixelated;
}
</style>
