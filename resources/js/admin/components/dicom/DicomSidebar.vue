<template>
  <div class="pointer-events-none absolute inset-0 z-30">
    <!-- Desktop: Bottom Horizontal Toolbar -->
    <aside
      class="hidden md:flex absolute bottom-8 left-1/2 -translate-x-1/2 flex-row items-center gap-2 pointer-events-auto z-40"
    >
      <div
        class="bg-slate-900/80 backdrop-blur-2xl border border-white/10 rounded-2xl p-1.5 shadow-2xl flex items-center gap-1.5 ring-1 ring-white/5"
      >
        <!-- Analysis Group -->
        <div class="flex items-center gap-1 px-1 border-r border-white/10">
          <button
            v-for="tool in mainTools"
            :key="tool.name"
            @click="$emit('set-active-tool', tool.name)"
            :title="tool.label"
            :class="[
              'h-10 w-10 rounded-xl flex items-center justify-center transition-all duration-300 group relative',
              activeTool === tool.name
                ? 'bg-primary text-white shadow-lg shadow-primary/40 scale-105 z-10'
                : 'text-slate-500 hover:text-slate-200 hover:bg-slate-800/80',
            ]"
          >
            <component :is="tool.icon" class="h-5 w-5" />
            <span
              v-if="activeTool !== tool.name"
              class="absolute bottom-full mb-3 px-2 py-1 bg-slate-800 text-white text-[9px] font-black uppercase tracking-widest rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50 border border-white/5 shadow-2xl"
            >
              {{ tool.label }}
            </span>
          </button>
        </div>

        <!-- Transform Group -->
        <div class="flex items-center gap-1 px-1">
          <button
            v-for="tool in actionTools"
            :key="tool.action"
            @click="$emit('action', tool.action)"
            :title="tool.label"
            class="h-10 w-10 rounded-xl flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-800/80 transition-all group relative"
          >
            <component :is="tool.icon" class="h-4.5 w-4.5" />
            <span
              class="absolute bottom-full mb-3 px-2 py-1 bg-slate-800 text-white text-[9px] font-black uppercase tracking-widest rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50 border border-white/5 shadow-2xl"
            >
              {{ tool.label }}
            </span>
          </button>

          <div class="h-6 w-px bg-white/10 mx-1"></div>

          <div class="flex items-center gap-1">
            <button
              @click="$emit('set-window', 'bone')"
              class="h-8 px-2 rounded-lg text-[8px] font-black uppercase tracking-tighter text-slate-500 hover:text-primary hover:bg-primary/5 border border-transparent hover:border-primary/20 transition-all"
            >
              Bone
            </button>
            <button
              @click="$emit('set-window', 'soft')"
              class="h-8 px-2 rounded-lg text-[8px] font-black uppercase tracking-tighter text-slate-500 hover:text-primary hover:bg-primary/5 border border-transparent hover:border-primary/20 transition-all"
            >
              Soft
            </button>
            <button
              @click="$emit('set-window', 'lung')"
              class="h-8 px-2 rounded-lg text-[8px] font-black uppercase tracking-tighter text-slate-500 hover:text-primary hover:bg-primary/5 border border-transparent hover:border-primary/20 transition-all"
            >
              Lung
            </button>
            <button
              @click="$emit('action', 'invert')"
              class="h-10 w-10 rounded-xl flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-800/80 transition-all group relative"
              title="Invert"
            >
              <ContrastIcon class="h-4.5 w-4.5" />
              <span
                class="absolute bottom-full mb-3 px-2 py-1 bg-slate-800 text-white text-[9px] font-black uppercase tracking-widest rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50 border border-white/5 shadow-2xl"
              >
                Invert
              </span>
            </button>
          </div>
        </div>
      </div>
    </aside>

    <!-- Mobile Bottom Toolbar (Horizontal Scroll) -->
    <div
      class="md:hidden fixed bottom-10 left-0 right-0 z-30 flex justify-center pointer-events-none px-4 pb-[env(safe-area-inset-bottom)]"
    >
      <div
        class="bg-slate-900/90 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl p-2 flex gap-2 overflow-x-auto max-w-full pointer-events-auto"
      >
        <!-- Main Tools -->
        <button
          v-for="tool in mainTools"
          :key="tool.name"
          @click="$emit('set-active-tool', tool.name)"
          :class="[
            'h-10 w-10 flex-shrink-0 rounded-xl flex items-center justify-center transition-all',
            activeTool === tool.name
              ? 'bg-primary text-white shadow-lg shadow-primary/30'
              : 'text-slate-400 hover:bg-slate-800',
          ]"
        >
          <component :is="tool.icon" class="h-5 w-5" />
        </button>

        <div class="w-px h-10 bg-white/10 flex-shrink-0 mx-1"></div>

        <!-- Transform Tools Condensed -->
        <button
          @click="$emit('action', 'rotate')"
          class="h-10 w-10 flex-shrink-0 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-800"
        >
          <component
            :is="actionTools.find((t) => t.action === 'rotate').icon"
            class="h-5 w-5"
          />
        </button>
        <button
          @click="$emit('set-window', 'soft')"
          class="h-10 px-3 flex-shrink-0 rounded-xl flex items-center justify-center text-[9px] font-black uppercase tracking-widest bg-slate-800/50 text-slate-400 border border-white/5"
        >
          Win
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Contrast as ContrastIcon } from "lucide-vue-next";

defineProps({
  mainTools: Array,
  actionTools: Array,
  activeTool: String,
});

defineEmits(["set-active-tool", "action", "set-window"]);
</script>
