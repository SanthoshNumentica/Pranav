<template>
  <div ref="container" class="w-full h-full bg-black overflow-hidden relative">
    <div
      v-if="loading"
      class="absolute inset-0 flex items-center justify-center"
    >
      <div
        class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"
      ></div>
    </div>
    <div
      v-if="error"
      class="absolute inset-0 flex items-center justify-center text-[8px] text-red-500 text-center p-1"
    >
      !
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import * as cornerstone from "cornerstone-core";

const props = defineProps({
  imageId: {
    type: String,
    required: true,
  },
});

const container = ref(null);
const loading = ref(true);
const error = ref(null);
let enabled = false;

const renderThumbnail = async () => {
  if (!props.imageId || !container.value) return;

  try {
    loading.value = true;
    error.value = null;

    if (!enabled) {
      cornerstone.enable(container.value);
      enabled = true;
    }

    const image = await cornerstone.loadAndCacheImage(props.imageId);

    if (container.value) {
      // Check again in case unmounted
      cornerstone.displayImage(container.value, image);

      // Fit to window
      const viewport = cornerstone.getViewport(container.value);
      if (viewport) {
        // Reset viewport to default to ensure it fits
        cornerstone.reset(container.value);
      }
    }
  } catch (err) {
    console.error("Thumbnail load error:", err);
    error.value = "Err";
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  renderThumbnail();
});

watch(
  () => props.imageId,
  () => {
    renderThumbnail();
  },
);

onBeforeUnmount(() => {
  if (container.value && enabled) {
    cornerstone.disable(container.value);
    enabled = false;
  }
});
</script>
