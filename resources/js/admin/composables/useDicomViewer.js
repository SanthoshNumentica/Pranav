import { ref, reactive, watch, onBeforeUnmount } from "vue";
import * as cornerstone from "cornerstone-core";
import * as cornerstoneMath from "cornerstone-math";
import * as cornerstoneTools from "cornerstone-tools";
import * as dicomParser from "dicom-parser";
import * as cornerstoneWADOImageLoader from "cornerstone-wado-image-loader";
import Hammer from "hammerjs";
import { formatDate } from "../utils/format";
import axios from "axios";
import JSZip from "jszip";

export function useDicomViewer() {
    const dicomElement = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const fileName = ref("");
    const activeTool = ref("StackScroll");
    const viewport = reactive({
        scale: 1,
        voi: { windowWidth: 0, windowCenter: 0 },
    });

    // Stack Info
    const currentImageIndex = ref(0);
    const totalImages = ref(1);
    const imageIds = ref([]);
    const seriesList = ref([]);
    const currentSeriesIndex = ref(0);
    const objectUrls = ref([]); // Keep track to revoke later if needed

    // Cine Info
    const isCineActive = ref(false);
    const cineFps = ref(1);

    // Overlay Data
    const patientName = ref("Loading...");
    const studyDate = ref("");
    const currentOrientation = ref("Axial");
    const currentSeries = ref("Series 01");
    const loadingCount = ref("");

    let initialized = false;
    const initCornerstone = () => {
        if (initialized) return;

        cornerstoneWADOImageLoader.external.cornerstone = cornerstone;
        cornerstoneWADOImageLoader.external.dicomParser = dicomParser;

        cornerstone.registerImageLoader(
            "wadouri",
            cornerstoneWADOImageLoader.wadouri.loadImage,
        );

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
            {
                tool: cornerstoneTools.StackScrollTool,
                name: "StackScroll",
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
        cornerstoneTools.setToolActive("StackScroll", { mouseButtonMask: 1 });
        cornerstoneTools.setToolActive("Pan", { mouseButtonMask: 2 });
        cornerstoneTools.setToolActive("StackScrollMouseWheel", {});
    };

    const setActiveTool = (toolName) => {
        activeTool.value = toolName;
        const tools = ["StackScroll", "Wwwc", "Pan", "Zoom", "Length", "Probe", "EllipticalRoi", "RectangleRoi"];
        tools.forEach((t) => {
            cornerstoneTools.setToolPassive(t);
        });
        cornerstoneTools.setToolActive(toolName, { mouseButtonMask: 1 });
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

    const loadImages = async (paths) => {
        if (!paths || !paths.length || !dicomElement.value) return;
        if (!paths || !paths.length || !dicomElement.value) return;
        loading.value = true;
        loadingCount.value = "";
        error.value = null;

        try {
            const baseUrl = window.location.origin;
            const newSeriesList = [];
            let generalSeries = { name: "General", imageIds: [] };

            for (const p of paths) {
                if (p.toLowerCase().endsWith('.zip')) {
                    try {
                        const seriesName = p.split('/').pop().replace('.zip', '');
                        const seriesImageIds = [];

                        // 1. Download Zip
                        let zipUrl = p.startsWith('http') ? p : `${baseUrl}/${p.startsWith('/') ? p.substring(1) : p}`;
                        if (p.includes("public/")) {
                            const clean = p.split("public/").pop();
                            zipUrl = `${baseUrl}/${clean.startsWith('/') ? clean.substring(1) : clean}`;
                        }

                        const response = await axios.get(zipUrl, { responseType: 'blob' });
                        const zipData = await JSZip.loadAsync(response.data);

                        // 2. Extract Files
                        const files = [];
                        zipData.forEach((relativePath, zipEntry) => {
                            if (!zipEntry.dir) {
                                files.push(zipEntry);
                            }
                        });

                        files.sort((a, b) => a.name.localeCompare(b.name, undefined, { numeric: true }));

                        for (let i = 0; i < files.length; i++) {
                            const zipEntry = files[i];
                            loadingCount.value = `${i + 1} / ${files.length}`;
                            const blob = await zipEntry.async("blob");
                            const objectUrl = URL.createObjectURL(blob);
                            objectUrls.value.push(objectUrl);
                            seriesImageIds.push(`wadouri:${objectUrl}`);
                        }

                        if (seriesImageIds.length > 0) {
                            newSeriesList.push({
                                name: seriesName,
                                imageIds: seriesImageIds
                            });
                        }

                    } catch (zipErr) {
                        console.error(`Failed to unzip ${p}`, zipErr);
                        error.value = `Failed to process zip: ${p.split('/').pop()}`;
                    }

                } else {
                    const normalizedPath = p.replace(/\\/g, "/");
                    let cleanPath = normalizedPath;
                    if (cleanPath.includes("public/")) {
                        cleanPath = cleanPath.split("public/").pop();
                    }
                    cleanPath = cleanPath.startsWith("/")
                        ? cleanPath.substring(1)
                        : cleanPath;
                    generalSeries.imageIds.push(`wadouri:${baseUrl}/${cleanPath}`);
                }
            }

            if (generalSeries.imageIds.length > 0) {
                newSeriesList.push(generalSeries);
            }

            seriesList.value = newSeriesList;

            if (seriesList.value.length === 0) {
                throw new Error("No displayable images found.");
            }

            // Load the first series by default
            selectSeries(0);

        } catch (err) {
            console.error("Cornerstone Error:", err);
            error.value = `Load Error: ${err.message || "Data interpreted as corrupt"}`;
            loading.value = false;
        }
    };

    const onNewImage = (e) => {
        const stackData = cornerstoneTools.getToolState(
            dicomElement.value,
            "stack",
        );
        if (stackData && stackData.data && stackData.data.length > 0) {
            const stack = stackData.data[0];
            const oldIdx = currentImageIndex.value;
            const newIdx = stack.currentImageIdIndex;

            // Handle Cine continuous play across series
            if (isCineActive.value && seriesList.value.length > 1) {
                // If we hit the end of the current series and it's looping back to 0
                if (oldIdx === totalImages.value - 1 && newIdx === 0) {
                    const nextIndex = (currentSeriesIndex.value + 1) % seriesList.value.length;

                    // Stop current clip to prevent multiple triggers during load
                    cornerstoneTools.stopClip(dicomElement.value);

                    // Switch to the next series
                    selectSeries(nextIndex);
                    return;
                }
            }

            currentImageIndex.value = newIdx;
            const imageId = stack.imageIds[newIdx];
            if (imageId) {
                fileName.value =
                    imageId.split("/").pop()?.split("?")[0] ||
                    "Image " + (newIdx + 1);
            }
        }
    };

    const selectSeries = (index) => {
        if (index < 0 || index >= seriesList.value.length) return;

        currentSeriesIndex.value = index;
        const series = seriesList.value[index];
        imageIds.value = series.imageIds;
        totalImages.value = imageIds.value.length;
        currentSeries.value = series.name;
        currentImageIndex.value = 0;

        if (!dicomElement.value) return;

        cornerstone.enable(dicomElement.value);

        const stack = {
            currentImageIdIndex: 0,
            imageIds: imageIds.value,
        };

        cornerstoneTools.clearToolState(dicomElement.value, "stack");
        cornerstoneTools.addStackStateManager(dicomElement.value, ["stack"]);
        cornerstoneTools.addToolState(dicomElement.value, "stack", stack);

        const firstImageId = imageIds.value[0];

        loading.value = true;
        cornerstone.loadAndCacheImage(firstImageId).then((image) => {
            cornerstone.displayImage(dicomElement.value, image);
            setupTools();
            updateViewportState();

            // Resume cine if it was active
            if (isCineActive.value) {
                cornerstoneTools.playClip(dicomElement.value, cineFps.value);
            }

            // Remove previous listeners to avoid duplicates
            dicomElement.value.removeEventListener("cornerstoneimagerendered", updateViewportState);
            dicomElement.value.addEventListener("cornerstoneimagerendered", updateViewportState);

            dicomElement.value.removeEventListener("cornerstonenewimage", onNewImage);
            dicomElement.value.addEventListener("cornerstonenewimage", onNewImage);

            loading.value = false;
        }).catch(err => {
            console.error(err);
            loading.value = false;
            error.value = "Failed to load series image.";
        });
    };

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

    const scrollToIndex = (index) => {
        const imageId = imageIds.value[index];
        fileName.value =
            imageId.split("/").pop()?.split("?")[0] || "Image " + (index + 1);

        // Update the stack tool state to keep cornerstoneTools sync'd
        const stackData = cornerstoneTools.getToolState(
            dicomElement.value,
            "stack",
        );
        if (stackData && stackData.data && stackData.data.length > 0) {
            stackData.data[0].currentImageIdIndex = index;
        }

        cornerstone.loadAndCacheImage(imageId).then((image) => {
            cornerstone.displayImage(dicomElement.value, image);
        });
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

    const resetImage = () => {
        cornerstone.reset(dicomElement.value);
        setActiveTool("Wwwc");
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

    onBeforeUnmount(() => {
        if (dicomElement.value) {
            cornerstone.disable(dicomElement.value);
            dicomElement.value.removeEventListener("cornerstonenewimage", onNewImage);
        }
        objectUrls.value.forEach(url => URL.revokeObjectURL(url));
        objectUrls.value = [];
    });

    return {
        dicomElement,
        loading,
        error,
        fileName,
        activeTool,
        viewport,
        currentImageIndex,
        totalImages,
        imageIds,
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
        // Series
        seriesList,
        currentSeriesIndex,
        selectSeries,

        updateViewportState,
        loadingCount,
    };
}
