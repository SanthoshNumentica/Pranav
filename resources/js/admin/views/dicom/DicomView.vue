<template>
  <div
    class="h-screen bg-slate-950 flex flex-col overflow-hidden text-slate-200 font-sans selection:bg-primary/30"
  >
    <DicomHeader
      :patient-name="patientName"
      :file-name="fileName"
      :current-image-index="currentImageIndex"
      :total-images="totalImages"
      :is-cine-active="isCineActive"
      v-model:cine-fps="cineFps"
      :viewport="viewport"
      @back="goBack"
      @prev="prevImage"
      @next="nextImage"
      @toggle-cine="toggleCine"
      @reset="resetImage"
      :documents="reportDocuments"
    />

    <div class="flex-grow flex relative overflow-hidden">
      <DicomSidebar
        :main-tools="mainTools"
        :action-tools="actionTools"
        :active-tool="activeTool"
        @set-active-tool="setActiveTool"
        @action="handleAction"
        @set-window="setWindowPreset"
      />

      <DicomViewport
        @element-ready="onElementReady"
        @back="goBack"
        :loading="loading"
        :error="error"
        :patient-name="patientName"
        :study-date="studyDate"
        :current-orientation="currentOrientation"
        :current-series="currentSeries"
        :viewport="viewport"
        :current-image-index="currentImageIndex"
      />
    </div>

    <!-- Series Footer -->
    <div
      v-if="seriesList.length > 1"
      class="bg-black border-t border-white/10 p-2 flex gap-2 overflow-x-auto pb-6 md:pb-2 z-40"
    >
      <button
        v-for="(series, idx) in seriesList"
        :key="idx"
        @click="selectSeries(idx)"
        class="relative group overflow-hidden rounded-lg border border-white/10 transition-all active:scale-95 flex flex-col items-center"
        :class="
          idx === currentSeriesIndex
            ? 'ring-2 ring-primary bg-black'
            : 'bg-slate-900 hover:bg-slate-800'
        "
      >
        <div class="w-16 h-16 bg-black relative">
          <DicomThumbnail
            v-if="series.imageIds.length > 0"
            :imageId="series.imageIds[Math.floor(series.imageIds.length / 2)]"
          />
          <div
            v-else
            class="absolute inset-0 flex items-center justify-center text-slate-500"
          >
            <LayersIcon class="w-6 h-6" />
          </div>
        </div>
        <div
          class="w-full px-1 py-1 bg-black/50 text-[10px] text-center text-slate-300 truncate max-w-[4rem]"
        >
          {{ series.name }}
        </div>
        <div
          class="absolute top-0 right-0 bg-primary text-white text-[9px] px-1 rounded-bl"
        >
          {{ series.imageIds.length }}
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import { formatDate } from "../../utils/format";
import * as lucide from "lucide-vue-next";

// Composable
import { useDicomViewer } from "../../composables/useDicomViewer";

// Sub-components
import DicomHeader from "../../components/dicom/DicomHeader.vue";
import DicomSidebar from "../../components/dicom/DicomSidebar.vue";
import DicomViewport from "../../components/dicom/DicomViewport.vue";
import DicomThumbnail from "../../components/dicom/DicomThumbnail.vue";

const {
  Layers: LayersIcon,
  Contrast: ContrastIcon,
  MousePointer2: MousePointer2Icon,
  ZoomIn: ZoomInIcon,
  Ruler: RulerIcon,
  Target: TargetIcon,
  Circle: CircleIcon,
  Square: SquareIcon,
  RefreshCw: RefreshCwIcon,
  FlipHorizontal: FlipHorizontalIcon,
  FlipVertical: FlipVerticalIcon,
  RotateCcw: RotateCcwIcon,
  Contrast,
  Target,
} = lucide;

const route = useRoute();
const router = useRouter();

const {
  dicomElement,
  loading,
  error,
  fileName,
  activeTool,
  viewport,
  currentImageIndex,
  totalImages,
  isCineActive,
  cineFps,
  patientName,
  studyDate,
  currentOrientation,
  currentSeries,
  initCornerstone,
  loadImages,
  setActiveTool,
  rotateImage,
  flipHorizontal,
  flipVertical,
  invertImage,
  setWindowPreset,
  nextImage,
  prevImage,
  resetImage,
  toggleCine,
  seriesList,
  currentSeriesIndex,
  selectSeries,
} = useDicomViewer();

const mainTools = [
  { name: "StackScroll", icon: LayersIcon, label: "Scroll" },
  { name: "Wwwc", icon: Contrast, label: "Windowing" },
  { name: "Pan", icon: MousePointer2Icon, label: "Pan" },
  { name: "Zoom", icon: ZoomInIcon, label: "Zoom" },
  { name: "Length", icon: RulerIcon, label: "Length" },
  { name: "Probe", icon: Target, label: "Probe" },
  { name: "EllipticalRoi", icon: CircleIcon, label: "Ellipse" },
  { name: "RectangleRoi", icon: SquareIcon, label: "Rectangle" },
];

const actionTools = [
  { action: "rotate", icon: RefreshCwIcon, label: "Rotate" },
  { action: "flipH", icon: FlipHorizontalIcon, label: "Flip H" },
  { action: "flipV", icon: FlipVerticalIcon, label: "Flip V" },
  { action: "reset", icon: RotateCcwIcon, label: "Reset" },
];

const handleAction = (action) => {
  switch (action) {
    case "rotate":
      rotateImage();
      break;
    case "flipH":
      flipHorizontal();
      break;
    case "flipV":
      flipVertical();
      break;
    case "reset":
      resetImage();
      break;
    case "invert":
      invertImage();
      break;
  }
};

const goBack = () => router.back();

const onElementReady = (el) => {
  dicomElement.value = el;
  initCornerstone();

  // Re-trigger initial load if already have params
  if (route.query.token) {
    fetchByToken(route.query.token);
  } else if (route.query.paths) {
    loadImages(route.query.paths.split(","));
  } else if (route.query.path) {
    loadImages([route.query.path]);
  }
};

const reportDocuments = ref([]);

const fetchByToken = async (token) => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get(`/api/v1/public/case-reports/${token}`);
    if (response.data.success) {
      const data = response.data.data;
      const report = data.report;

      patientName.value = report.patient?.name || "ANONYMOUS";
      studyDate.value = formatDate(report.created_at);

      // Extract non-DICOM documents calling helper
      const docs = [];
      const extractDocs = (docList) => {
        if (!docList) return;
        docList.forEach((doc) => {
          const ext = doc.split(".").pop().toLowerCase();
          // Allow all files except individual dcm (unless we want to allow those too)
          if (ext !== "dcm") {
            let cleanPath = doc;
            if (cleanPath.includes("public/")) {
              cleanPath = cleanPath.split("public/").pop();
            }
            cleanPath = cleanPath.startsWith("/")
              ? cleanPath.substring(1)
              : cleanPath;

            docs.push({
              name: doc.split("/").pop(),
              url: `${window.location.origin}/${cleanPath}`,
            });
          }
        });
      };

      // 1. General Case Documents
      if (report.documents) {
        extractDocs(report.documents);
      }

      // 2. Item-specific Documents
      if (report.items) {
        report.items.forEach((item) => {
          if (item.documents) {
            extractDocs(item.documents);
          }
        });
      }
      reportDocuments.value = docs;

      if (data.dicom_paths && data.dicom_paths.length > 0) {
        await loadImages(data.dicom_paths);
      } else {
        error.value = "No DICOM files found for this case report.";
      }
    } else {
      loading.value = false;
      error.value =
        response.data.message || "Failed to fetch case report details.";
    }
  } catch (err) {
    loading.value = false;
    console.error("Failed to fetch public report:", err);
    if (err.response && err.response.status === 403) {
      error.value =
        err.response.data.message || "This case report has expired.";
    } else {
      error.value = "Failed to load report data.";
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  // Initialization happens in onElementReady
});
</script>
