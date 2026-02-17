<template>
  <header
    class="fixed top-0 left-0 right-0 z-30 flex flex-col pointer-events-none"
  >
    <!-- Top Bar -->
    <div
      class="h-12 md:h-14 px-4 md:px-6 bg-slate-900/90 backdrop-blur-xl border-b border-white/5 flex items-center justify-between shadow-2xl pointer-events-auto"
    >
      <div
        class="flex items-center gap-3 md:gap-6 flex-1 min-w-0 overflow-hidden"
      >
        <button
          @click="$emit('back')"
          class="h-8 w-8 md:h-9 md:w-9 rounded-lg bg-slate-800/50 hover:bg-slate-700 text-slate-400 hover:text-white transition-all active:scale-95 flex items-center justify-center border border-white/5 flex-shrink-0"
        >
          <ArrowLeftIcon class="h-4.5 w-4.5" />
        </button>
        <div class="h-6 w-px bg-white/10 flex-shrink-0"></div>
        <div class="flex items-center gap-3 md:gap-4 min-w-0">
          <div
            class="h-8 w-8 md:h-9 md:w-9 rounded-lg bg-primary/20 flex items-center justify-center border border-primary/30 ring-2 md:ring-4 ring-primary/5 flex-shrink-0"
          >
            <ActivityIcon class="h-3.5 w-3.5 md:h-4.5 md:w-4.5 text-primary" />
          </div>
          <div
            class="min-w-0 flex-1 truncate pr-4 border-r border-white/10 hidden sm:block"
          >
            <h3
              class="text-xs md:text-sm font-bold text-white tracking-tight leading-none truncate"
            >
              {{ patientName }}
            </h3>
          </div>

          <!-- Desktop: Img Navigation moved to Left (Hidden on Mobile) -->
          <div
            class="hidden md:flex items-center bg-slate-950/30 rounded-xl py-1 md:py-1.5 px-3 border border-white/5 shadow-inner flex-shrink-0"
          >
            <button
              @click="$emit('prev')"
              class="p-1 px-2 text-slate-500 hover:text-white transition-colors border-r border-white/5 active:scale-90"
            >
              <ChevronLeftIcon class="h-3.5 w-3.5" />
            </button>
            <div
              class="px-3 text-[9px] font-black text-slate-400 uppercase tracking-widest tabular-nums flex items-center gap-1.5"
            >
              <span class="hidden lg:inline">Img:</span>
              <span class="text-primary text-xs">{{
                currentImageIndex + 1
              }}</span>
              <span class="text-slate-600">/</span>
              <span class="text-slate-300">{{ totalImages }}</span>
            </div>
            <button
              @click="$emit('next')"
              class="p-1 px-2 text-slate-500 hover:text-white transition-colors border-l border-white/5 active:scale-90"
            >
              <ChevronRightIcon class="h-3.5 w-3.5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Right: Viewport Metrics & Actions -->
      <div class="flex items-center gap-2 md:gap-4 flex-shrink-0">
        <!-- Desktop: Cine Controls moved to Right -->
        <div
          class="hidden md:flex items-center gap-4 bg-slate-950/30 rounded-xl py-1.5 px-3 border border-white/5"
        >
          <button
            @click="$emit('toggle-cine')"
            :class="[
              'p-1 rounded-lg transition-all',
              isCineActive
                ? 'bg-primary text-white shadow-lg shadow-primary/40'
                : 'text-slate-400 hover:text-white hover:bg-slate-800',
            ]"
          >
            <PlayIcon v-if="!isCineActive" class="h-3.5 w-3.5 fill-current" />
            <PauseIcon v-else class="h-3.5 w-3.5 fill-current" />
          </button>
          <div class="flex items-center gap-3">
            <div class="flex flex-col">
              <span
                class="text-[6px] font-black text-slate-600 uppercase tracking-widest leading-none mb-0.5"
                >Cine</span
              >
              <span
                class="text-[10px] font-black text-primary tabular-nums leading-none"
                >{{ cineFps }}</span
              >
            </div>
            <div class="w-12 xl:w-16">
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

        <div class="hidden md:block h-6 w-px bg-white/10"></div>
        <!-- Desktop Metrics -->
        <div
          class="hidden xl:flex items-center bg-slate-950/50 rounded-2xl py-2 px-4 border border-white/5 gap-6"
        >
          <div class="flex flex-col">
            <span
              class="text-[7px] font-black text-slate-600 uppercase tracking-widest leading-none mb-1"
              >Scaling</span
            >
            <span class="text-xs font-bold text-primary tabular-nums"
              >{{ Math.round(viewport.scale * 100) }}%</span
            >
          </div>
          <div class="w-px h-6 bg-white/10"></div>
          <div class="flex flex-col">
            <span
              class="text-[7px] font-black text-slate-600 uppercase tracking-widest leading-none mb-1"
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
          <div v-if="documents.length === 1" class="md:hidden flex items-center gap-2">
            <a
              :href="documents[0].url"
              target="_blank"
              class="h-9 w-9 rounded-xl bg-slate-800/50 text-slate-300 border border-white/5 flex items-center justify-center active:scale-95"
            >
              <EyeIcon class="h-4 w-4 text-primary" />
            </a>
            <a
              :href="documents[0].url"
              download
              target="_blank"
              class="h-9 w-9 rounded-xl bg-primary/20 text-primary border border-primary/20 flex items-center justify-center active:scale-95"
            >
              <DownloadIcon class="h-4 w-4" />
            </a>
          </div>
          <button
            v-else
            @click="showDocs = !showDocs"
            class="md:hidden h-9 w-9 rounded-xl bg-primary/20 text-primary border border-primary/20 flex items-center justify-center active:scale-95"
          >
            <DownloadIcon class="h-4 w-4" />
          </button>

          <!-- Desktop button -->
          <div class="hidden md:block">
            <template v-if="documents.length === 1">
              <div class="flex items-center gap-2">
                <a
                  :href="documents[0].url"
                  target="_blank"
                  class="h-10 px-4 rounded-xl bg-slate-800/50 hover:bg-slate-700 text-slate-300 hover:text-white text-[10px] font-black uppercase tracking-widest border border-white/5 transition-all active:scale-95 flex items-center gap-2"
                >
                  <EyeIcon class="h-3.5 w-3.5 text-primary" />
                  View
                </a>
                <a
                  :href="documents[0].url"
                  download
                  target="_blank"
                  class="h-10 px-4 rounded-xl bg-primary/20 hover:bg-primary/30 text-primary hover:text-white text-[10px] font-black uppercase tracking-widest border border-primary/20 transition-all active:scale-95 flex items-center gap-2"
                >
                  <DownloadIcon class="h-3.5 w-3.5" />
                  Document
                </a>
              </div>
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
            class="absolute top-full right-0 mt-2 w-72 bg-slate-900 border border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50 flex flex-col max-h-[60vh] overflow-y-auto"
          >
            <div
              v-for="(doc, idx) in documents"
              :key="idx"
              class="px-4 py-3 hover:bg-white/5 flex items-center justify-between border-b border-white/5 last:border-0 transition-colors"
            >
              <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <FileTextIcon
                  v-if="!doc.name.endsWith('.zip')"
                  class="h-4 w-4 text-slate-500 flex-shrink-0"
                />
                <DownloadIcon
                  v-else
                  class="h-4 w-4 text-slate-500 flex-shrink-0"
                />
                <span class="truncate text-xs font-medium text-slate-300">{{
                  doc.name
                }}</span>
              </div>
              <div class="flex items-center gap-1.5 ml-4">
                <a
                  :href="doc.url"
                  target="_blank"
                  class="p-2 rounded-lg hover:bg-primary/10 text-primary transition-all active:scale-90"
                  title="View"
                >
                  <EyeIcon class="h-4 w-4" />
                </a>
                <a
                  :href="doc.url"
                  download
                  target="_blank"
                  class="p-2 rounded-lg hover:bg-white/5 text-slate-400 hover:text-white transition-all active:scale-90"
                  title="Download"
                >
                  <DownloadIcon class="h-4 w-4" />
                </a>
              </div>
            </div>
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
      class="md:hidden bg-slate-900/80 backdrop-blur-md border-b border-white/5 p-1.5 flex flex-col gap-1.5 pointer-events-none"
    >
      <div class="flex items-center justify-between gap-2">
        <!-- Mobile Stack Nav -->
        <div
          class="flex items-center flex-1 bg-slate-950/50 rounded-xl px-2 py-1 border border-white/5 shadow-inner justify-between pointer-events-auto"
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
            'h-9 w-9 rounded-xl flex items-center justify-center border border-white/5 pointer-events-auto',
            isCineActive
              ? 'bg-primary text-white shadow-lg shadow-primary/40'
              : 'bg-slate-800/50 text-slate-400',
          ]"
        >
          <PlayIcon v-if="!isCineActive" class="h-4 w-4 fill-current" />
          <PauseIcon v-else class="h-4 w-4 fill-current" />
        </button>
      </div>

      <!-- Mobile Metrics Display -->
      <div
        class="flex items-center justify-between px-3 py-1.5 bg-slate-950/30 rounded-lg border border-white/5 pointer-events-auto"
      >
        <div class="flex items-center gap-4">
          <div class="flex flex-col">
            <span
              class="text-[6px] font-black text-slate-600 uppercase tracking-widest leading-none mb-0.5"
              >Scaling</span
            >
            <span class="text-[9px] font-bold text-primary tabular-nums"
              >{{ Math.round(viewport.scale * 100) }}%</span
            >
          </div>
          <div class="w-px h-4 bg-white/10"></div>
          <div class="flex flex-col">
            <span
              class="text-[6px] font-black text-slate-600 uppercase tracking-widest leading-none mb-0.5"
              >WW / WC</span
            >
            <span class="text-[9px] font-bold text-emerald-500 tabular-nums"
              >{{ Math.round(viewport.voi.windowWidth) }} /
              {{ Math.round(viewport.voi.windowCenter) }}</span
            >
          </div>
        </div>
        <div class="text-[8px] font-medium text-slate-500 italic pr-1">
          Adjust tools via bottom bar
        </div>
      </div>
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
  EyeIcon,
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
