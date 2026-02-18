<template>
  <div class="h-full flex flex-col space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Create Patient
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Enter patient details to create a new record.
        </p>
      </div>
    </div>

    <!-- Form Container -->
    <div
      class="flex-1 bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-hidden flex flex-col"
    >
      <PatientForm
        :loading="loading"
        @submit="handleCreate"
        @cancel="handleCancel"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import PatientForm from "../../components/patients/PatientForm.vue";

const router = useRouter();
const { addToast } = useToast();
const loading = ref(false);

const handleCancel = () => {
  router.push({ name: "Patients" });
};

const handleCreate = async (formData) => {
  loading.value = true;
  try {
    await axios.post("/api/v1/patients", formData);
    addToast({
      title: "Success",
      description: "Patient created successfully.",
      variant: "success",
    });
    router.push({ name: "Patients" });
  } catch (err) {
    console.error("Failed to create patient", err);
    addToast({
      title: "Error",
      description:
        err.response?.data?.message ||
        "Failed to create patient. Please check your data.",
      variant: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
