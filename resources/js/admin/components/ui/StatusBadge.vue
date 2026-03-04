<template>
    <span :class="[
        'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border transition-colors duration-200 whitespace-nowrap',
        statusClass
    ]">
        {{ label || displayText }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true
    },
    label: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'invoice' // 'invoice' or 'case'
    }
});

const displayText = computed(() => {
    return props.status ? props.status.replace(/_/g, ' ') : '';
});

const statusClass = computed(() => {
    const s = props.status ? props.status.toLowerCase() : '';

    if (props.type === 'invoice') {
        switch (s) {
            case 'fully_paid':
            case 'paid':
                return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
            case 'due':
            case 'partial':
                return 'bg-amber-500/10 text-amber-500 border-amber-500/20';
            case 'unpaid':
            case 'pending':
                return 'bg-rose-500/10 text-rose-500 border-rose-500/20';
            case 'cancelled':
                return 'bg-slate-500/10 text-slate-500 border-slate-500/20';
            default:
                return 'bg-slate-500/10 text-slate-500 border-slate-500/20';
        }
    } else if (props.type === 'case') {
        switch (s) {
            case 'pending':
                return 'bg-amber-500/10 text-amber-500 border-amber-500/20';
            case 'available':
                return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
            case 'expired':
                return 'bg-rose-500/10 text-rose-500 border-rose-500/20';
            case 'deleted':
                return 'bg-slate-500/10 text-slate-500 border-slate-500/20';
            default:
                return 'bg-slate-500/10 text-slate-500 border-slate-500/20';
        }
    }

    return 'bg-slate-500/10 text-slate-500 border-slate-500/20';
});
</script>
