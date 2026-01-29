<template>
  <div
    class="h-screen flex items-center justify-center bg-slate-950 text-white"
  >
    <div class="text-center">
      <div
        class="h-12 w-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-4"
      ></div>
      <p class="text-slate-400 font-medium">
        Preparing your diagnostic view...
      </p>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

onMounted(() => {
  const token = route.params.token;
  if (token) {
    router.replace({
      name: "DicomView",
      query: { token },
    });
  } else {
    router.replace("/");
  }
});
</script>
