<template>
  <div class="flex justify-end gap-1.5 transition-opacity duration-200">
    <button
      v-if="showView && permissions.canView"
      @click="$emit('view', item)"
      class="flex h-8 w-8 items-center justify-center rounded-xl text-blue-500 hover:bg-blue-500/10 transition-all active:scale-90"
      :title="viewTitle"
    >
      <Eye class="h-4 w-4" />
    </button>
    <button
      v-if="showEdit && permissions.canEdit"
      @click="$emit('edit', item)"
      class="flex h-8 w-8 items-center justify-center rounded-xl text-primary hover:bg-primary/10 transition-all active:scale-90"
      :title="editTitle"
    >
      <Edit class="h-4 w-4" />
    </button>
    <button
      v-if="showDelete && permissions.canDelete"
      @click="$emit('delete', item)"
      class="flex h-8 w-8 items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
      :title="deleteTitle"
    >
      <Trash2 class="h-4 w-4" />
    </button>
  </div>
</template>

<script setup>
import { Eye, Edit, Trash2 } from "lucide-vue-next";

defineProps({
  item: {
    type: Object,
    required: true,
  },
  permissions: {
    type: Object,
    required: true,
  },
  showView: {
    type: Boolean,
    default: true,
  },
  showEdit: {
    type: Boolean,
    default: true,
  },
  showDelete: {
    type: Boolean,
    default: true,
  },
  viewTitle: {
    type: String,
    default: "View Details",
  },
  editTitle: {
    type: String,
    default: "Edit Item",
  },
  deleteTitle: {
    type: String,
    default: "Delete Item",
  },
});

defineEmits(["view", "edit", "delete"]);
</script>
