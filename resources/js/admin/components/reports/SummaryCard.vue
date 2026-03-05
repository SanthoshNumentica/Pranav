<template>
    <div @click="clickable && $emit('click')" :class="[
        'p-4 rounded-2xl border-2 transition-all duration-300 group relative overflow-hidden bg-white',
        orientation === 'vertical' ? 'flex flex-col items-center text-center gap-1 justify-center' : 'flex items-center gap-4',
        clickable ? 'cursor-pointer hover:border-slate-200 hover:shadow-sm hover:-translate-y-0.5' : '',
        active && clickable ? activeBorderClass + ' shadow-md -translate-y-0.5' : '',
        active && !clickable ? activeBorderClass : '',
        !active && !clickable ? 'border-slate-100' : '',
        active && !activeBorderClass.includes('border-') ? 'border-primary' : ''
    ]">
        <!-- Icon Section -->
        <div v-if="icon" :class="[
            'rounded-xl transition-transform group-hover:scale-110',
            orientation === 'vertical' ? 'p-2 mb-1' : 'p-3.5',
            active ? activeIconBgClass : (iconBgClass || 'bg-slate-50')
        ]">
            <component :is="icon"
                :class="[orientation === 'vertical' ? 'h-4 w-4' : 'h-6 w-6', active ? activeIconColorClass : (iconColorClass || 'text-slate-400')]" />
        </div>

        <!-- Content Section -->
        <div :class="[orientation === 'vertical' ? 'w-full' : 'flex-1 min-w-0']">
            <p :class="[
                'font-bold uppercase tracking-widest leading-none mb-1.5',
                orientation === 'vertical' ? 'text-[9px] opacity-60 truncate px-1' : 'text-[10px]',
                active ? activeLabelColorClass : 'text-slate-400'
            ]">
                {{ label }}
            </p>
            <p :class="[
                'font-bold tracking-tight truncate',
                orientation === 'vertical' ? 'text-lg font-black' : 'text-xl',
                active ? activeValueColorClass : 'text-slate-900'
            ]">
                {{ value }}
            </p>
            <p v-if="subtitle" :class="[
                'text-[10px] font-semibold mt-0.5 truncate',
                active ? activeLabelColorClass : 'text-slate-400'
            ]">
                {{ subtitle }}
            </p>
        </div>

        <!-- Active Indicator -->
        <div v-if="active" class="absolute top-1.5 right-1.5 text-primary">
            <component :is="activeIcon || CheckCircle2" :class="['h-3.5 w-3.5', activeLabelColorClass]" />
        </div>
    </div>
</template>

<script setup>
import { CheckCircle2 } from "lucide-vue-next";

defineProps({
    label: String,
    value: [String, Number],
    subtitle: [String, Number],
    icon: [Object, Function],
    iconBgClass: String,
    iconColorClass: String,
    clickable: {
        type: Boolean,
        default: true
    },
    active: {
        type: Boolean,
        default: false
    },
    activeBorderClass: {
        type: String,
        default: 'border-primary text-primary'
    },
    activeIconBgClass: {
        type: String,
        default: 'bg-primary/5'
    },
    activeIconColorClass: {
        type: String,
        default: 'text-primary'
    },
    activeLabelColorClass: {
        type: String,
        default: 'text-primary'
    },
    activeValueColorClass: {
        type: String,
        default: 'text-slate-900'
    },
    activeIcon: [Object, Function],
    orientation: {
        type: String,
        default: 'horizontal' // horizontal | vertical
    }
});

defineEmits(['click']);
</script>
