<template>
  <header
    class="fixed top-0 left-0 right-0 z-30 flex flex-col pointer-events-none"
  >
    <!-- Top Bar -->
    <div
      class="h-14 md:h-16 px-4 md:px-6 bg-slate-900/90 backdrop-blur-xl border-b border-white/5 flex items-center justify-between shadow-2xl pointer-events-auto"
    >
      <div
        class="flex items-center gap-4 md:gap-6 w-full md:w-auto overflow-hidden"
      >
        <button
          @click="$emit('back')"
          class="h-9 w-9 md:h-10 md:w-10 rounded-xl bg-slate-800/50 hover:bg-slate-700 text-slate-400 hover:text-white transition-all active:scale-95 flex items-center justify-center border border-white/5 flex-shrink-0"
        >
          <ArrowLeftIcon class="h-5 w-5" />
        </button>
        <div class="h-6 w-px bg-white/10 flex-shrink-0"></div>
        <div class="flex items-center gap-3 md:gap-4 overflow-hidden">
          <div
            class="h-9 w-9 md:h-10 md:w-10 rounded-xl bg-primary/20 flex items-center justify-center border border-primary/30 ring-2 md:ring-4 ring-primary/5 flex-shrink-0"
          >
            <ActivityIcon class="h-4 w-4 md:h-5 md:w-5 text-primary" />
          </div>
          <div class="flex flex-col overflow-hidden">
            <h3
              class="text-xs md:text-sm font-bold text-white tracking-tight leading-tight truncate"
            >
              {{ patientName }}
            </h3>
            <p
              class="text-[9px] md:text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em] mt-0.5 truncate"
            >
              {{ fileName || "Awaiting Data..." }}
            </p>
          </div>
        </div>
      </div>

      <!-- Desktop Center: Stack Info & Cine Controls -->
      <div
        class="absolute left-1/2 -translate-x-1/2 hidden md:flex items-center gap-6"
      >
        <div
          class="flex items-center bg-slate-950/50 rounded-2xl px-4 py-1.5 border border-white/5 shadow-inner"
        >
          <button
            @click="$emit('prev')"
            class="p-1.5 text-slate-500 hover:text-white transition-colors"
          >
            <ChevronLeftIcon class="h-4 w-4" />
          </button>
          <div
            class="px-4 text-[11px] font-black text-slate-400 uppercase tracking-widest tabular-nums"
          >
            Img: <span class="text-primary">{{ currentImageIndex + 1 }}</span> /
            {{ totalImages }}
          </div>
          <button
            @click="$emit('next')"
            class="p-1.5 text-slate-500 hover:text-white transition-colors"
          >
            <ChevronRightIcon class="h-4 w-4" />
          </button>
        </div>

        <div
          class="flex items-center gap-3 bg-slate-950/50 rounded-2xl px-3 py-1.5 border border-white/5"
        >
          <button
            @click="$emit('toggle-cine')"
            :class="[
              'p-2 rounded-lg transition-all',
              isCineActive
                ? 'bg-primary text-white shadow-lg shadow-primary/30'
                : 'text-slate-500 hover:text-white hover:bg-slate-800',
            ]"
          >
            <PlayIcon v-if="!isCineActive" class="h-4 w-4 fill-current" />
            <PauseIcon v-else class="h-4 w-4 fill-current" />
          </button>
          <div class="flex flex-col w-16">
            <div
              class="flex justify-between text-[7px] font-black text-slate-500 uppercase tracking-tighter mb-0.5"
            >
              <span>CINE</span>
              <span class="text-primary">{{ cineFps }}</span>
            </div>
            <input
              type="range"
              min="0.5"
              max="5"
              step="0.1"
              :value="cineFps"
              @input="$emit('update:cineFps', Number($event.target.value))"
              class="w-full h-1 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-primary"
            />
          </div>
        </div>
      </div>

      <!-- Right: Viewport Metrics & Actions -->
      <div class="flex items-center gap-2 md:gap-4 flex-shrink-0">
        <!-- Desktop Metrics -->
        <div
          class="hidden xl:flex items-center bg-slate-950/50 rounded-2xl px-4 py-2 border border-white/5 gap-6"
        >
          <div class="flex flex-col">
            <span
              class="text-[8px] font-black text-slate-600 uppercase tracking-[0.2em] leading-none mb-1"
              >Scaling</span
            >
            <span class="text-xs font-bold text-primary tabular-nums"
              >{{ Math.round(viewport.scale * 100) }}%</span
            >
          </div>
          <div class="w-px h-6 bg-white/5"></div>
          <div class="flex flex-col">
            <span
              class="text-[8px] font-black text-slate-600 uppercase tracking-[0.2em] leading-none mb-1"
              >WW / WC</span
            >
            <span class="text-xs font-bold text-emerald-500 tabular-nums"
              >{{ Math.round(viewport.voi.windowWidth) }} /
              {{ Math.round(viewport.voi.windowCenter) }}</span
            >
          </div>
        </div>

        <!-- Documents Button -->
        <div class="relative" v-if="documents && documents.length > 0">
          <!-- Mobile simplified button -->
          <button
            @click="showDocs = !showDocs"
            class="md:hidden h-9 w-9 rounded-xl bg-primary/20 text-primary border border-primary/20 flex items-center justify-center active:scale-95"
          >
            <DownloadIcon class="h-4 w-4" />
          </button>

          <!-- Desktop button -->
          <div class="hidden md:block">
            <template v-if="documents.length === 1">
              <a
                :href="documents[0].url"
                download
                target="_blank"
                class="h-10 px-4 rounded-xl bg-primary/20 hover:bg-primary/30 text-primary hover:text-white text-[10px] font-black uppercase tracking-widest border border-primary/20 transition-all active:scale-95 flex items-center gap-2"
              >
                <DownloadIcon class="h-3.5 w-3.5" />
                Download
              </a>
            </template>
            <template v-else>
              <button
                @click="showDocs = !showDocs"
                class="h-10 px-4 rounded-xl bg-primary/20 hover:bg-primary/30 text-primary hover:text-white text-[10px] font-black uppercase tracking-widest border border-primary/20 transition-all active:scale-95 flex items-center gap-2"
              >
                <DownloadIcon class="h-3.5 w-3.5" />
                Downloads ({{ documents.length }})
              </button>
            </template>
          </div>

          <div
            v-if="showDocs"
            class="absolute top-full right-0 mt-2 w-56 bg-slate-900 border border-white/10 rounded-xl shadow-xl overflow-hidden z-50 flex flex-col max-h-[60vh] overflow-y-auto"
          >
            <a
              v-for="(doc, idx) in documents"
              :key="idx"
              :href="doc.url"
              download
              target="_blank"
              class="px-4 py-3 text-xs text-slate-300 hover:bg-white/5 hover:text-white flex items-center gap-2 border-b border-white/5 last:border-0 transition-colors"
            >
              <FileTextIcon
                v-if="!doc.name.endsWith('.zip')"
                class="h-3 w-3 opacity-50 flex-shrink-0"
              />
              <DownloadIcon v-else class="h-3 w-3 opacity-50 flex-shrink-0" />
              <span class="truncate">{{ doc.name }}</span>
            </a>
          </div>
        </div>

        <button
          @click="$emit('reset')"
          class="hidden md:flex h-10 px-4 rounded-xl bg-slate-800/50 hover:bg-slate-700 text-slate-300 hover:text-white text-[10px] font-black uppercase tracking-widest border border-white/5 transition-all active:scale-95 items-center gap-2"
        >
          <RefreshCwIcon class="h-3.5 w-3.5" />
          Reset View
        </button>

        <button
          @click="$emit('reset')"
          class="md:hidden h-9 w-9 rounded-xl bg-slate-800/50 text-slate-300 border border-white/5 flex items-center justify-center active:scale-95"
        >
          <RefreshCwIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Mobile Nav Bar (Below Main Header) -->
    <div
      class="md:hidden bg-slate-900/80 backdrop-blur-md border-b border-white/5 p-2 flex items-center justify-between gap-2 pointer-events-auto"
    >
      <!-- Mobile Stack Nav -->
      <div
        class="flex items-center flex-1 bg-slate-950/50 rounded-xl px-2 py-1 border border-white/5 shadow-inner justify-between"
      >
        <button
          @click="$emit('prev')"
          class="p-2 text-slate-400 active:text-white active:scale-90"
        >
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
        <div
          class="text-[10px] font-black text-slate-500 uppercase tracking-widest"
        >
          <span class="text-primary">{{ currentImageIndex + 1 }}</span> /
          {{ totalImages }}
        </div>
        <button
          @click="$emit('next')"
          class="p-2 text-slate-400 active:text-white active:scale-90"
        >
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>

      <!-- Mobile Cine Toggle -->
      <button
        @click="$emit('toggle-cine')"
        :class="[
          'h-9 w-9 rounded-xl flex items-center justify-center border border-white/5',
          isCineActive
            ? 'bg-primary text-white shadow-lg shadow-primary/30'
            : 'bg-slate-800/50 text-slate-400',
        ]"
      >
        <PlayIcon v-if="!isCineActive" class="h-4 w-4 fill-current" />
        <PauseIcon v-else class="h-4 w-4 fill-current" />
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref } from "vue";
import {
  ArrowLeftIcon,
  ActivityIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  PlayIcon,
  PauseIcon,
  RefreshCwIcon,
  FileTextIcon,
  DownloadIcon,
} from "lucide-vue-next";

defineProps({
  patientName: String,
  fileName: String,
  currentImageIndex: Number,
  totalImages: Number,
  isCineActive: Boolean,
  cineFps: Number,
  viewport: Object,
  documents: {
    type: Array,
    default: () => [],
  },
});

defineEmits(["back", "prev", "next", "toggle-cine", "reset", "update:cineFps"]);

const showDocs = ref(false);
</script>
