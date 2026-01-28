<template>
  <div
    class="h-screen bg-slate-950 flex flex-col overflow-hidden text-slate-200 font-sans selection:bg-primary/30"
  >
    <!-- Professional Toolbar -->
    <header
      class="h-16 px-6 bg-slate-900/90 backdrop-blur-xl border-b border-white/5 flex items-center justify-between z-30 shadow-2xl"
    >
      <div class="flex items-center gap-6">
        <button
          @click="goBack"
          class="h-10 w-10 rounded-xl bg-slate-800/50 hover:bg-slate-700 text-slate-400 hover:text-white transition-all active:scale-95 flex items-center justify-center border border-white/5 group"
        >
          <ArrowLeftIcon
            class="h-5 w-5 group-hover:-translate-x-0.5 transition-transform"
          />
        </button>
        <div class="h-8 w-px bg-white/10"></div>
        <div class="flex items-center gap-4">
          <div
            class="h-10 w-10 rounded-xl bg-primary/20 flex items-center justify-center border border-primary/30 ring-4 ring-primary/5"
          >
            <ActivityIcon class="h-5 w-5 text-primary" />
          </div>
          <div class="flex flex-col">
            <h3
              class="text-sm font-bold text-white tracking-tight leading-tight"
            >
              Diagnostic Engine v1.2
            </h3>
            <p
              class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em] mt-0.5 truncate max-w-[200px]"
            >
              {{ fileName || "Awaiting Data..." }}
            </p>
          </div>
        </div>
      </div>

      <!-- Center: Stack Info & Cine Controls -->
      <div class="absolute left-1/2 -translate-x-1/2 flex items-center gap-6">
        <div
          class="flex items-center bg-slate-950/50 rounded-2xl px-4 py-1.5 border border-white/5 shadow-inner"
        >
          <button
            @click="prevImage"
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
            @click="nextImage"
            class="p-1.5 text-slate-500 hover:text-white transition-colors"
          >
            <ChevronRightIcon class="h-4 w-4" />
          </button>
        </div>

        <div
          class="flex items-center gap-3 bg-slate-950/50 rounded-2xl px-3 py-1.5 border border-white/5"
        >
          <button
            @click="toggleCine"
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
          <div class="flex flex-col w-24">
            <div
              class="flex justify-between text-[8px] font-black text-slate-500 uppercase tracking-tighter mb-1"
            >
              <span>FPS control</span>
              <span class="text-primary">{{ cineFps }}</span>
            </div>
            <input
              type="range"
              min="1"
              max="60"
              v-model="cineFps"
              class="w-full h-1 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-primary"
            />
          </div>
        </div>
      </div>

      <!-- Right: Viewport Metrics -->
      <div class="flex items-center gap-4">
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

        <button
          @click="resetImage"
          class="h-10 px-4 rounded-xl bg-slate-800/50 hover:bg-slate-700 text-slate-300 hover:text-white text-[10px] font-black uppercase tracking-widest border border-white/5 transition-all active:scale-95 flex items-center gap-2"
        >
          <RefreshCwIcon class="h-3.5 w-3.5" />
          Reset View
        </button>
      </div>
    </header>

    <div class="flex-grow flex relative overflow-hidden">
      <!-- Left Floating Tool Palette -->
      <aside
        class="absolute left-6 top-1/2 -translate-y-1/2 z-30 flex flex-col gap-4"
      >
        <!-- Main Analysis Tools -->
        <div
          class="bg-slate-900/80 backdrop-blur-2xl border border-white/10 rounded-[24px] p-2 shadow-2xl flex flex-col gap-1 ring-1 ring-white/5"
        >
          <p
            class="text-[8px] font-black text-slate-600 uppercase tracking-[0.2em] text-center py-2 border-b border-white/5 mb-1"
          >
            Analysis
          </p>
          <ToolButton
            v-for="tool in mainTools"
            :key="tool.name"
            :icon="tool.icon"
            :label="tool.label"
            :active="activeTool === tool.name"
            @click="setActiveTool(tool.name)"
          />
        </div>

        <!-- Annotation Tools -->
        <div
          class="bg-slate-900/80 backdrop-blur-2xl border border-white/10 rounded-[24px] p-2 shadow-2xl flex flex-col gap-1 ring-1 ring-white/5"
        >
          <p
            class="text-[8px] font-black text-slate-600 uppercase tracking-[0.2em] text-center py-2 border-b border-white/5 mb-1"
          >
            Markup
          </p>
          <ToolButton
            v-for="tool in markupTools"
            :key="tool.name"
            :icon="tool.icon"
            :label="tool.label"
            :active="activeTool === tool.name"
            @click="setActiveTool(tool.name)"
          />
        </div>
      </aside>

      <!-- Right Floating Manipulation Palette -->
      <aside
        class="absolute right-6 top-1/2 -translate-y-1/2 z-30 flex flex-col gap-4"
      >
        <div
          class="bg-slate-900/80 backdrop-blur-2xl border border-white/10 rounded-[24px] p-2 shadow-2xl flex flex-col gap-1 ring-1 ring-white/5"
        >
          <p
            class="text-[8px] font-black text-slate-600 uppercase tracking-[0.2em] text-center py-2 border-b border-white/5 mb-1"
          >
            Image
          </p>
          <ActionButton
            icon="RotateCcwIcon"
            label="Rotate 90°"
            @click="rotateImage"
          />
          <ActionButton
            icon="FlipHorizontalIcon"
            label="Flip H"
            @click="flipHorizontal"
          />
          <ActionButton
            icon="FlipVerticalIcon"
            label="Flip V"
            @click="flipVertical"
          />
          <ActionButton
            icon="ContrastIcon"
            label="Invert"
            @click="invertImage"
          />
        </div>

        <div
          class="bg-slate-900/80 backdrop-blur-2xl border border-white/10 rounded-[24px] p-2 shadow-2xl flex flex-col gap-1 ring-1 ring-white/5"
        >
          <p
            class="text-[8px] font-black text-slate-600 uppercase tracking-[0.2em] text-center py-2 border-b border-white/5 mb-1"
          >
            Presets
          </p>
          <PresetButton label="Bone" @click="setWindowPreset('bone')" />
          <PresetButton label="Soft" @click="setWindowPreset('soft')" />
          <PresetButton label="Lung" @click="setWindowPreset('lung')" />
        </div>
      </aside>

      <!-- Main Canvas -->
      <main
        class="flex-grow bg-slate-950 flex items-center justify-center relative cursor-crosshair group"
      >
        <div
          ref="dicomElement"
          class="w-full h-full"
          @contextmenu.prevent
        ></div>

        <!-- Loading / No Data Overlay -->
        <Transition
          enter-active-class="transition duration-500 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-300 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="loading"
            class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/90 backdrop-blur-xl z-20"
          >
            <div class="relative mb-8">
              <div
                class="h-24 w-24 rounded-full border-[8px] border-primary/10 border-t-primary animate-spin"
              ></div>
              <ActivityIcon
                class="h-10 w-10 text-primary absolute inset-0 m-auto animate-pulse"
              />
            </div>
            <h2
              class="text-white text-xl font-black uppercase tracking-[0.4em] mb-2"
            >
              Diagnostic Scan
            </h2>
            <p
              class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.5em] animate-pulse"
            >
              Initializing Digital Image Reconstructor...
            </p>
          </div>

          <!-- Error Overlay -->
          <div
            v-else-if="error"
            class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/95 backdrop-blur-2xl z-20 px-10 text-center"
          >
            <div
              class="h-20 w-20 rounded-full bg-red-500/10 flex items-center justify-center border border-red-500/20 mb-6"
            >
              <AlertCircleIcon class="h-10 w-10 text-red-500" />
            </div>
            <h2
              class="text-white text-xl font-black uppercase tracking-[0.2em] mb-2"
            >
              Analysis Failed
            </h2>
            <p class="text-red-400 text-sm font-medium max-w-md">{{ error }}</p>
            <button
              @click="goBack"
              class="mt-8 px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold uppercase tracking-widest transition-all border border-white/5 active:scale-95"
            >
              Return to Case
            </button>
          </div>
        </Transition>

        <!-- Dynamic Overlay Data -->
        <div
          v-if="!loading"
          class="absolute inset-0 pointer-events-none p-10 flex flex-col justify-between z-10 transition-opacity duration-700 group-hover:opacity-100 opacity-40"
        >
          <div class="flex justify-between items-start">
            <div class="space-y-1">
              <p
                class="text-[10px] font-black text-primary uppercase tracking-[0.2em]"
              >
                Patient Records
              </p>
              <h4 class="text-white text-lg font-black tracking-tight">
                {{ patientName || "ANONYMOUS" }}
              </h4>
              <p class="text-[10px] text-slate-500 font-bold tabular-nums">
                ID: {{ patientId || "P-000000" }}
              </p>
            </div>
            <div class="text-right space-y-1">
              <p
                class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]"
              >
                Study Instance
              </p>
              <h4 class="text-white text-sm font-black tracking-widest">
                {{ studyDate || "--/--/----" }}
              </h4>
              <p
                class="text-[10px] text-slate-600 font-bold uppercase tracking-widest"
              >
                Digital Diagnostic Matrix
              </p>
            </div>
          </div>

          <div class="flex justify-between items-end">
            <div
              class="bg-black/40 backdrop-blur-md rounded-2xl p-4 border border-white/5 space-y-2"
            >
              <div class="flex items-center gap-2">
                <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                <p
                  class="text-[9px] font-black text-slate-300 uppercase tracking-widest"
                >
                  Active Tool:
                  <span class="text-primary">{{ activeTool }}</span>
                </p>
              </div>
              <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                <span
                  class="text-[8px] text-slate-500 font-black uppercase tracking-widest"
                  >Orientation:
                  <span class="text-slate-200">{{
                    currentOrientation
                  }}</span></span
                >
                <span
                  class="text-[8px] text-slate-500 font-black uppercase tracking-widest"
                  >Series:
                  <span class="text-slate-200">{{ currentSeries }}</span></span
                >
              </div>
            </div>

            <div class="text-right">
              <p
                class="text-[8px] font-black text-slate-600 uppercase tracking-[0.3em] italic mb-1"
              >
                Pranav Diagnostic Solutions
              </p>
              <p
                class="text-[7px] text-slate-700 font-black uppercase tracking-[0.4em]"
              >
                High Precision Biomedical Imaging Architecture
              </p>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import {
  ref,
  onMounted,
  onBeforeUnmount,
  reactive,
  watch,
  defineComponent,
  h,
} from "vue";
import { useRoute, useRouter } from "vue-router";
import * as lucide from "lucide-vue-next";
const {
  ArrowLeft: ArrowLeftIcon,
  Activity: ActivityIcon,
  RefreshCw: RefreshCwIcon,
  ZoomIn: ZoomInIcon,
  ZoomOut: ZoomOutIcon,
  AlertCircle: AlertCircleIcon,
  ChevronLeft: ChevronLeftIcon,
  ChevronRight: ChevronRightIcon,
  Play: PlayIcon,
  Pause: PauseIcon,
  Ruler: RulerIcon,
  Square: SquareIcon,
  Circle: CircleIcon,
  Triangle: TriangleIcon,
  Type: TypeIcon,
  PenTool: PenToolIcon,
  MousePointer2: MousePointer2Icon,
  Target: TargetIcon,
  RotateCcw: RotateCcwIcon,
  FlipHorizontal: FlipHorizontalIcon,
  FlipVertical: FlipVerticalIcon,
  Contrast: ContrastIcon,
  ArrowRight: ArrowIcon,
} = lucide;

// Cornerstone imports
import * as cornerstone from "cornerstone-core";
import * as cornerstoneMath from "cornerstone-math";
import * as cornerstoneTools from "cornerstone-tools";
import * as dicomParser from "dicom-parser";
import * as cornerstoneWADOImageLoader from "cornerstone-wado-image-loader";
import Hammer from "hammerjs";

// Components
const ToolButton = defineComponent({
  props: ["icon", "label", "active"],
  setup(props) {
    return () =>
      h(
        "button",
        {
          title: props.label,
          class: [
            "h-12 w-12 rounded-xl flex items-center justify-center transition-all duration-300 group relative",
            props.active
              ? "bg-primary text-white shadow-lg shadow-primary/40 scale-105 z-10"
              : "text-slate-500 hover:text-slate-200 hover:bg-slate-800/80",
          ],
        },
        [
          h(lucide[props.icon], { class: "h-5 w-5" }),
          !props.active &&
            h(
              "span",
              {
                class:
                  "absolute left-full ml-3 px-2 py-1 bg-slate-800 text-white text-[9px] font-black uppercase tracking-widest rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50 border border-white/5 shadow-2xl",
              },
              props.label,
            ),
        ],
      );
  },
});

const ActionButton = defineComponent({
  props: ["icon", "label"],
  setup(props) {
    return () =>
      h(
        "button",
        {
          title: props.label,
          class:
            "h-11 w-11 rounded-xl flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-800/80 transition-all group relative",
        },
        [
          h(lucide[props.icon], { class: "h-4.5 w-4.5" }),
          h(
            "span",
            {
              class:
                "absolute right-full mr-3 px-2 py-1 bg-slate-800 text-white text-[9px] font-black uppercase tracking-widest rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50 border border-white/5",
            },
            props.label,
          ),
        ],
      );
  },
});

const PresetButton = defineComponent({
  props: ["label"],
  setup(props) {
    return () =>
      h(
        "button",
        {
          class:
            "h-10 px-2 rounded-lg text-[9px] font-black uppercase tracking-tighter text-slate-500 hover:text-primary hover:bg-primary/5 border border-transparent hover:border-primary/20 transition-all",
        },
        props.label,
      );
  },
});

// Logic
const route = useRoute();
const router = useRouter();

const dicomElement = ref(null);
const loading = ref(false);
const error = ref(null);
const fileName = ref("");
const activeTool = ref("Wwwc");
const viewport = reactive({
  scale: 1,
  voi: { windowWidth: 0, windowCenter: 0 },
});

// Stack Info
const currentImageIndex = ref(0);
const totalImages = ref(1);
const imageIds = ref([]);

// Cine Info
const isCineActive = ref(false);
const cineFps = ref(24);

// Overlay Data
const patientName = ref("Pranav Diagnostic");
const patientId = ref("CASE-38429");
const studyDate = ref("2024-01-29");
const currentOrientation = ref("Axial");
const currentSeries = ref("Series 01");

const mainTools = [
  { name: "Wwwc", icon: "ContrastIcon", label: "Windowing" },
  { name: "Pan", icon: "MousePointer2Icon", label: "Pan" },
  { name: "Zoom", icon: "ZoomInIcon", label: "Zoom" },
  { name: "Length", icon: "RulerIcon", label: "Ruler" },
  { name: "Angle", icon: "TriangleIcon", label: "Angle" },
  { name: "RectRoi", icon: "SquareIcon", label: "Rectangle ROI" },
  { name: "EllipticalRoi", icon: "CircleIcon", label: "Ellipse ROI" },
  { name: "FreehandRoi", icon: "PenToolIcon", label: "Freehand ROI" },
  { name: "Probe", icon: "TargetIcon", label: "HU Probe" },
];

const markupTools = [
  { name: "ArrowAnnotate", icon: "ArrowRight", label: "Arrow" },
  { name: "TextMarker", icon: "TypeIcon", label: "Text" },
];

let initialized = false;
const initCornerstone = () => {
  cornerstoneWADOImageLoader.external.cornerstone = cornerstone;
  cornerstoneWADOImageLoader.external.dicomParser = dicomParser;

  // Explicitly register the loader
  cornerstone.registerImageLoader(
    "wadouri",
    cornerstoneWADOImageLoader.wadouri.loadImage,
  );

  // Configure WADO Image Loader
  cornerstoneWADOImageLoader.webWorkerManager.initialize({
    maxWebWorkers: Math.max(navigator.hardwareConcurrency - 1, 1),
    startWebWorkersOnDemand: true,
    taskConfiguration: {
      decodeTask: {
        initializeCodecsOnStartup: true,
        usePDFJS: false,
        strict: false,
      },
    },
  });

  cornerstoneTools.external.cornerstone = cornerstone;
  cornerstoneTools.external.cornerstoneMath = cornerstoneMath;
  cornerstoneTools.external.Hammer = Hammer;

  try {
    cornerstoneTools.init({
      showSVGCursors: true,
      globalToolSyncEnabled: true,
    });
  } catch (err) {
    console.warn("Cornerstone Tools already initialized:", err.message);
  }

  initialized = true;
};

const setupTools = () => {
  if (!dicomElement.value) return;

  const tools = [
    { tool: cornerstoneTools.PanTool, name: "Pan" },
    { tool: cornerstoneTools.ZoomTool, name: "Zoom" },
    { tool: cornerstoneTools.WwwcTool, name: "Wwwc" },
    { tool: cornerstoneTools.LengthTool, name: "Length" },
    { tool: cornerstoneTools.AngleTool, name: "Angle" },
    { tool: cornerstoneTools.RectRoiTool, name: "RectRoi" },
    { tool: cornerstoneTools.EllipticalRoiTool, name: "EllipticalRoi" },
    { tool: cornerstoneTools.FreehandRoiTool, name: "FreehandRoi" },
    { tool: cornerstoneTools.ProbeTool, name: "Probe" },
    { tool: cornerstoneTools.ArrowAnnotateTool, name: "ArrowAnnotate" },
    { tool: cornerstoneTools.TextMarkerTool, name: "TextMarker" },
    {
      tool: cornerstoneTools.StackScrollMouseWheelTool,
      name: "StackScrollMouseWheel",
    },
  ];

  tools.forEach((t) => {
    try {
      cornerstoneTools.addTool(t.tool);
    } catch (e) {
      // Tool might already be added
    }
  });

  // Set default states
  cornerstoneTools.setToolActive("Wwwc", { mouseButtonMask: 1 });
  cornerstoneTools.setToolActive("Pan", { mouseButtonMask: 2 });
  cornerstoneTools.setToolActive("StackScrollMouseWheel", {});
};

const setActiveTool = (toolName) => {
  const previousTool = activeTool.value;
  activeTool.value = toolName;

  // Tools to keep active for other buttons
  const reservedButtons = [2, 4]; // Right click, middle click

  // Deactivate all primary click tools except the new one
  const allPrimaryTools = [...mainTools, ...markupTools].map((t) => t.name);
  allPrimaryTools.forEach((t) => {
    cornerstoneTools.setToolPassive(t);
  });

  // Set new active for left click
  cornerstoneTools.setToolActive(toolName, { mouseButtonMask: 1 });
};

const loadImage = async (path) => {
  if (!path || !dicomElement.value) return;
  loading.value = true;
  error.value = null;

  // Normalize path (convert backslashes to forward slashes)
  const normalizedPath = path.replace(/\\/g, "/");
  fileName.value = normalizedPath.split("/").pop();

  try {
    // Construct full URL to avoid path issues
    const baseUrl = window.location.origin;
    let cleanPath = normalizedPath.replace(/\\/g, "/");

    // Remove 'public/' if it exists at the start or mid-path (mis-configuration)
    if (cleanPath.includes("public/")) {
      cleanPath = cleanPath.split("public/").pop();
    }

    cleanPath = cleanPath.startsWith("/") ? cleanPath.substring(1) : cleanPath;
    const fullUrl = `${baseUrl}/${cleanPath}`;

    console.log("Diagnostic: Final URL:", fullUrl);

    // Step 1: Pre-fetch check and diagnostic parse
    try {
      const resp = await fetch(fullUrl);
      if (!resp.ok) {
        throw new Error(
          `Resource unavailable: ${resp.status} ${resp.statusText}`,
        );
      }

      const arrayBuffer = await resp.arrayBuffer();
      console.log(
        `Diagnostic: Data received (${(arrayBuffer.byteLength / 1024).toFixed(2)} KB)`,
      );

      try {
        const dataSet = dicomParser.parseDicom(new Uint8Array(arrayBuffer));
        const modality = dataSet.string("x00080060");
        const transferSyntax = dataSet.string("x00020010");
        const rows = dataSet.uint16("x00280010");
        const cols = dataSet.uint16("x00280011");

        console.log(`Diagnostic: DICOM Header Parsed:
          - Modality: ${modality || "Unknown"}
          - Transfer Syntax: ${transferSyntax || "Unknown"}
          - Resolution: ${cols}x${rows}`);

        if (!rows || !cols) {
          console.warn(
            "Diagnostic: No pixel dimensions found. This might not be a viewable image (e.g., Structured Report or KOS).",
          );
        }
      } catch (parseErr) {
        console.error(
          "Diagnostic: DICOM Parser failed - file might be invalid or missing preamble:",
          parseErr,
        );
      }
    } catch (fetchErr) {
      console.error("Diagnostic: Fetch failed:", fetchErr);
      throw fetchErr;
    }

    const imageId = `wadouri:${fullUrl}`;
    console.log("Cornerstone: Prepared Image ID:", imageId);

    imageIds.value = [imageId];
    totalImages.value = imageIds.value.length;

    cornerstone.enable(dicomElement.value);

    const stack = {
      currentImageIdIndex: 0,
      imageIds: imageIds.value,
    };

    cornerstoneTools.addStackStateManager(dicomElement.value, ["stack"]);
    cornerstoneTools.addToolState(dicomElement.value, "stack", stack);

    // Step 2: Load with timeout
    const loadPromise = cornerstone.loadAndCacheImage(imageId);
    const timeoutPromise = new Promise((_, reject) =>
      setTimeout(
        () =>
          reject(
            new Error(
              "Cornerstone load timeout (60s). This usually happens with large files or compressed (JPEG2000) DICOMs that require heavy decoding.",
            ),
          ),
        60000,
      ),
    );

    const image = await Promise.race([loadPromise, timeoutPromise]);
    cornerstone.displayImage(dicomElement.value, image);

    setupTools();
    updateViewportState();

    dicomElement.value.addEventListener(
      "cornerstoneimagerendered",
      updateViewportState,
    );
    dicomElement.value.addEventListener("cornerstonestackscroll", (e) => {
      currentImageIndex.value = e.detail.newImageIdIndex;
    });
  } catch (err) {
    console.error("Cornerstone Error:", err);
    error.value = `Load Error: ${err.message || "Data interpreted as corrupt"}`;
  } finally {
    loading.value = false;
  }
};

const updateViewportState = () => {
  if (!dicomElement.value) return;
  const vp = cornerstone.getViewport(dicomElement.value);
  if (vp) {
    viewport.scale = vp.scale;
    viewport.voi.windowWidth = vp.voi.windowWidth;
    viewport.voi.windowCenter = vp.voi.windowCenter;
  }
};

// Actions
const rotateImage = () => {
  const vp = cornerstone.getViewport(dicomElement.value);
  vp.rotation = (vp.rotation + 90) % 360;
  cornerstone.setViewport(dicomElement.value, vp);
};

const flipHorizontal = () => {
  const vp = cornerstone.getViewport(dicomElement.value);
  vp.hflip = !vp.hflip;
  cornerstone.setViewport(dicomElement.value, vp);
};

const flipVertical = () => {
  const vp = cornerstone.getViewport(dicomElement.value);
  vp.vflip = !vp.vflip;
  cornerstone.setViewport(dicomElement.value, vp);
};

const invertImage = () => {
  const vp = cornerstone.getViewport(dicomElement.value);
  vp.invert = !vp.invert;
  cornerstone.setViewport(dicomElement.value, vp);
};

const setWindowPreset = (type) => {
  const vp = cornerstone.getViewport(dicomElement.value);
  switch (type) {
    case "bone":
      vp.voi.windowWidth = 2500;
      vp.voi.windowCenter = 480;
      break;
    case "soft":
      vp.voi.windowWidth = 400;
      vp.voi.windowCenter = 40;
      break;
    case "lung":
      vp.voi.windowWidth = 1500;
      vp.voi.windowCenter = -600;
      break;
  }
  cornerstone.setViewport(dicomElement.value, vp);
};

const resetImage = () => {
  cornerstone.reset(dicomElement.value);
  setActiveTool("Wwwc");
};

const nextImage = () => {
  if (currentImageIndex.value < totalImages.value - 1) {
    currentImageIndex.value++;
    scrollToIndex(currentImageIndex.value);
  }
};

const prevImage = () => {
  if (currentImageIndex.value > 0) {
    currentImageIndex.value--;
    scrollToIndex(currentImageIndex.value);
  }
};

const scrollToIndex = (index) => {
  cornerstone.loadAndCacheImage(imageIds.value[index]).then((image) => {
    cornerstone.displayImage(dicomElement.value, image);
  });
};

const toggleCine = () => {
  if (isCineActive.value) {
    cornerstoneTools.stopClip(dicomElement.value);
  } else {
    cornerstoneTools.playClip(dicomElement.value, cineFps.value);
  }
  isCineActive.value = !isCineActive.value;
};

watch(cineFps, (newFps) => {
  if (isCineActive.value) {
    cornerstoneTools.stopClip(dicomElement.value);
    cornerstoneTools.playClip(dicomElement.value, newFps);
  }
});

const goBack = () => router.back();

onMounted(() => {
  initCornerstone();
  if (route.query.path) loadImage(route.query.path);
});

onBeforeUnmount(() => {
  if (dicomElement.value) cornerstone.disable(dicomElement.value);
});
</script>

<style scoped>
:deep(canvas) {
  width: 100% !important;
  height: 100% !important;
  image-rendering: pixelated;
}

/* Custom Range Input Snippet */
input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 12px;
  width: 12px;
  border-radius: 50%;
  background: #0284c7;
  cursor: pointer;
  box-shadow: 0 0 10px rgba(2, 132, 199, 0.5);
}
</style>
