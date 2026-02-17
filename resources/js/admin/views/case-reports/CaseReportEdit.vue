<template>
  <div class="max-w-5xl mx-auto space-y-8 pb-20">
    <!-- Page Header -->
    <div
      class="flex items-center justify-between animate-in fade-in slide-in-from-top-4 duration-500"
    >
      <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">
          Edit Case Report
        </h1>
        <p class="text-slate-500 mt-2 font-medium">
          Update diagnostic case details and manage DICOM files.
        </p>
      </div>
      <button
        @click="$router.push('/case-reports')"
        class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-all active:scale-95"
      >
        Cancel
      </button>
    </div>

    <!-- Main Form Grid -->
    <form
      v-if="!fetching"
      @submit.prevent="handleSubmit"
      class="grid grid-cols-1 lg:grid-cols-3 gap-8"
    >
      <!-- Left Column: Case Information -->
      <div
        class="lg:col-span-1 space-y-8 animate-in fade-in slide-in-from-left-4 duration-700 delay-100"
      >
        <div
          class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-6"
        >
          <div class="flex items-center gap-3 text-slate-900 mb-2">
            <div
              class="h-10 w-10 rounded-2xl bg-primary/10 flex items-center justify-center"
            >
              <UserIcon class="h-5 w-5 text-primary" />
            </div>
            <h3 class="font-bold text-lg">Case Info</h3>
          </div>

          <div class="space-y-1">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Case ID</label
            >
            <p class="text-sm font-black text-slate-700 ml-1">
              {{ form.case_id }}
            </p>
          </div>

          <!-- Patient Selection -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Patient <span class="text-rose-500">*</span></label
            >
            <div class="relative">
              <Select v-model="form.patient_fk_id" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select Patient" />
                </SelectTrigger>
                <SelectContent>
                  <div class="px-2 py-2 sticky top-0 bg-white z-10 border-b border-slate-100">
                    <div class="relative">
                      <SearchIcon class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3 w-3 text-slate-400" />
                      <input
                        v-model="patientSearch"
                        type="text"
                        placeholder="Search patient..."
                        class="w-full pl-8 pr-3 py-1.5 text-[11px] font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/5 focus:border-primary transition-all"
                        @keydown.stop
                      />
                    </div>
                  </div>
                  <SelectItem
                    v-for="patient in filteredPatients"
                    :key="patient.id"
                    :value="patient.id.toString()"
                  >
                    {{ patient.name }} ({{ patient.patient_id }})
                  </SelectItem>
                  <div v-if="filteredPatients.length === 0" class="p-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    No patients found
                  </div>
                </SelectContent>
              </Select>
              <ChevronDownIcon
                class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none"
              />
            </div>
          </div>

          <!-- Patient WhatsApp Number & Toggle -->
          <div class="flex items-end gap-3 px-1">
            <div class="flex-1 space-y-1.5">
              <label
                class="text-[9px] font-bold text-slate-400 uppercase tracking-wider ml-1"
                >WhatsApp No</label
              >
              <input
                v-model="form.whatsapp_no_patient"
                type="text"
                placeholder="Patient mobile..."
                class="w-full h-9 px-3 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-primary/10 focus:border-primary outline-none transition-all placeholder:text-slate-300"
              />
            </div>
            <label
              class="flex items-center gap-2 h-9 px-3 rounded-xl border border-slate-100 bg-slate-50/50 cursor-pointer hover:bg-white hover:border-primary/20 transition-all group shrink-0"
            >
              <div class="relative flex items-center justify-center">
                <input
                  v-model="form.send_whatsapp_patient"
                  type="checkbox"
                  class="peer h-4 w-4 rounded border-2 border-slate-200 text-primary focus:ring-primary/10 transition-all cursor-pointer appearance-none checked:bg-primary checked:border-primary"
                />
                <CheckIcon
                  class="absolute h-2.5 w-2.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"
                />
              </div>
              <span
                class="text-[10px] font-bold text-slate-500 group-hover:text-primary transition-colors"
                >WhatsApp</span
              >
            </label>
          </div>

          <!-- Doctor Selection -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Referring Doctor <span class="text-rose-500">*</span></label
            >
            <div class="relative">
              <Select v-model="form.doc_ref_fk_id" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select Doctor" />
                </SelectTrigger>
                <SelectContent>
                  <div class="px-2 py-2 sticky top-0 bg-white z-10 border-b border-slate-100">
                    <div class="relative">
                      <SearchIcon class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3 w-3 text-slate-400" />
                      <input
                        v-model="doctorSearch"
                        type="text"
                        placeholder="Search doctor..."
                        class="w-full pl-8 pr-3 py-1.5 text-[11px] font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/5 focus:border-primary transition-all"
                        @keydown.stop
                      />
                    </div>
                  </div>
                  <SelectItem
                    v-for="doctor in filteredDoctors"
                    :key="doctor.id"
                    :value="doctor.id.toString()"
                  >
                    {{ doctor.name }}
                  </SelectItem>
                  <div v-if="filteredDoctors.length === 0" class="p-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    No doctors found
                  </div>
                </SelectContent>
              </Select>
              <ChevronDownIcon
                class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none"
              />
            </div>
          </div>

          <!-- Doctor WhatsApp Number & Toggle -->
          <div class="flex items-end gap-3 px-1">
            <div class="flex-1 space-y-1.5">
              <label
                class="text-[9px] font-bold text-slate-400 uppercase tracking-wider ml-1"
                >WhatsApp No</label
              >
              <input
                v-model="form.whatsapp_no_doctor"
                type="text"
                placeholder="Doctor mobile..."
                class="w-full h-9 px-3 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-primary/10 focus:border-primary outline-none transition-all placeholder:text-slate-300"
              />
            </div>
            <label
              class="flex items-center gap-2 h-9 px-3 rounded-xl border border-slate-100 bg-slate-50/50 cursor-pointer hover:bg-white hover:border-primary/20 transition-all group shrink-0"
            >
              <div class="relative flex items-center justify-center">
                <input
                  v-model="form.send_whatsapp_doctor"
                  type="checkbox"
                  class="peer h-4 w-4 rounded border-2 border-slate-200 text-primary focus:ring-primary/10 transition-all cursor-pointer appearance-none checked:bg-primary checked:border-primary"
                />
                <CheckIcon
                  class="absolute h-2.5 w-2.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"
                />
              </div>
              <span
                class="text-[10px] font-bold text-slate-500 group-hover:text-primary transition-colors"
                >WhatsApp</span
              >
            </label>
          </div>

          <!-- General Documents -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Case Documents (PDF, WORD, EXCEL)
              <span class="text-rose-500">*</span></label
            >
            <div class="flex items-center gap-3">
              <label
                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-primary/30 transition-all cursor-pointer group relative overflow-hidden"
              >
                <input
                  type="file"
                  multiple
                  accept="application/pdf,.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,.xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                  class="hidden"
                  @change="handleGeneralFiles"
                  :disabled="processingGeneral"
                />
                <template v-if="!processingGeneral">
                  <PaperclipIcon
                    class="h-4 w-4 text-slate-400 group-hover:text-primary transition-colors"
                  />
                  <span
                    class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors"
                    >Attach Files</span
                  >
                </template>
                <template v-else>
                  <Loader2Icon class="h-4 w-4 text-primary animate-spin" />
                  <span class="text-xs font-bold text-primary"
                    >Processing...</span
                  >
                </template>
              </label>
            </div>

            <div
              v-if="form.documents.length > 0"
              class="flex flex-wrap gap-2 mt-2"
            >
              <div
                v-for="(doc, dIdx) in form.documents"
                :key="dIdx"
                class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 border border-slate-200 rounded-xl text-[10px] font-bold text-slate-600 animate-in zoom-in-95 duration-200"
              >
                <div
                  class="h-4 w-4 rounded-md bg-white border border-slate-200 flex items-center justify-center"
                >
                  <CheckIcon class="h-2.5 w-2.5 text-emerald-500" />
                </div>
                <span class="truncate max-w-[80px]">{{
                  getFileName(doc.path)
                }}</span>
                <button
                  type="button"
                  @click="removeGeneralDoc(dIdx)"
                  class="hover:text-rose-500 transition-colors"
                >
                  <XIcon class="h-3 w-3" />
                </button>
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Description (Optional)</label
            >
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full rounded-2xl py-3.5 px-4 text-sm border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none resize-none"
              placeholder="Case history or notes..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Right Column: Scan Items -->
      <div
        class="lg:col-span-2 space-y-8 animate-in fade-in slide-in-from-right-4 duration-700 delay-200"
      >
        <div
          class="bg-white rounded-[40px] border border-slate-200 p-8 md:p-10 shadow-soft-xl"
        >
          <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
              <div
                class="h-10 w-10 rounded-2xl bg-primary/10 flex items-center justify-center"
              >
                <ActivityIcon class="h-5 w-5 text-primary" />
              </div>
              <h3 class="font-bold text-lg text-slate-900">
                Scan Items & Documents
              </h3>
            </div>
            <button
              type="button"
              @click="addItem"
              class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-all active:scale-95 shadow-lg shadow-slate-900/10"
            >
              <PlusIcon class="h-4 w-4" />
              Add Scan
            </button>
          </div>

          <div class="space-y-6">
            <div
              v-for="(item, index) in form.items"
              :key="index"
              class="group relative bg-slate-50/50 rounded-[32px] border border-slate-100 p-6 transition-all hover:border-primary/20 hover:bg-white hover:shadow-lg hover:shadow-primary/5 focus-within:ring-2 focus-within:ring-primary/10"
            >
              <!-- Index Badge -->
              <div
                class="absolute -left-3 top-6 h-8 w-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold shadow-lg"
              >
                {{ index + 1 }}
              </div>

              <!-- Item Controls -->
              <button
                v-if="form.items.length > 1"
                type="button"
                @click="removeItem(index)"
                class="absolute -right-2 -top-2 h-8 w-8 rounded-full bg-white border border-slate-100 text-slate-400 hover:text-rose-500 hover:border-rose-100 hover:bg-rose-50 transition-all shadow-sm flex items-center justify-center"
              >
                <XIcon class="h-4 w-4" />
              </button>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Scan Selection -->
                <div class="space-y-4">
                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Scan Type <span class="text-rose-500">*</span></label
                    >
                    <div class="relative group/select">
                      <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-hover/select:text-primary transition-colors"
                      >
                        <ActivityIcon class="h-4 w-4" />
                      </div>
                      <Select v-model="item.scan_type_id" required>
                        <SelectTrigger class="pl-10">
                          <SelectValue placeholder="Select Type" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="type in scanTypes"
                            :key="type.id"
                            :value="type.id.toString()"
                          >
                            {{ type.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                      <ChevronDownIcon
                        class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none group-hover/select:text-primary transition-colors"
                      />
                    </div>
                  </div>

                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Specific Scan <span class="text-rose-500">*</span></label
                    >
                    <div class="relative group/select">
                      <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-hover/select:text-primary transition-colors"
                      >
                        <ActivityIcon class="h-4 w-4" />
                      </div>
                      <Select
                        v-model="item.scan_id"
                        required
                        :disabled="!item.scan_type_id"
                      >
                        <SelectTrigger class="pl-10">
                          <SelectValue placeholder="Select Scan" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="scan in getScans(item.scan_type_id)"
                            :key="scan.id"
                            :value="scan.id.toString()"
                          >
                            {{ scan.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                      <ChevronDownIcon
                        class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none group-hover/select:text-primary transition-colors"
                      />
                    </div>
                  </div>
                </div>

                <!-- File Collection -->
                <div class="space-y-4">
                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >DICOM Study Folder</label
                    >
                    <div
                      class="relative h-[112px] rounded-2xl border-2 border-dashed border-slate-200 bg-white flex flex-col items-center justify-center p-4 transition-all hover:border-primary/50 group/upload overflow-hidden"
                      @dragover.prevent
                      @drop.prevent="handleDrop($event, index)"
                    >
                      <input
                        type="file"
                        webkitdirectory
                        directory
                        multiple
                        class="absolute inset-0 opacity-0 cursor-pointer"
                        @change="handleFiles($event, index)"
                      />
                      <div
                        class="flex flex-col items-center gap-2 pointer-events-none"
                      >
                        <div
                          class="p-2 rounded-xl bg-slate-50 group-hover/upload:bg-primary/10 transition-colors"
                        >
                          <component
                            :is="item.processing ? Loader2Icon : UploadIcon"
                            :class="[
                              'h-5 w-5',
                              item.processing
                                ? 'text-primary animate-spin'
                                : 'text-slate-400 group-hover/upload:text-primary',
                            ]"
                          />
                        </div>
                        <span
                          class="text-[11px] font-bold text-slate-500 group-hover/upload:text-primary"
                        >
                          {{
                            item.processing
                              ? "Analyzing files..."
                              : "Drop multiple folders or Click to select"
                          }}
                        </span>
                      </div>
                    </div>

                    <!-- Multi-Folder Display -->
                    <div class="mt-2 space-y-2">
                      <div
                        v-for="folder in getUniqueFolders(item.documents)"
                        :key="folder.name"
                        class="flex items-center justify-between gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg group/folder"
                      >
                        <div class="flex items-center gap-2">
                          <FolderIcon class="h-4 w-4 text-primary" />
                          <span class="text-xs font-bold text-slate-700">{{
                            folder.name
                          }}</span>
                          <span class="text-[10px] text-slate-400 font-medium"
                            >({{ folder.count }} files)</span
                          >
                        </div>
                        <button
                          type="button"
                          @click="removeFolder(index, folder.name)"
                          class="p-1 rounded-md hover:bg-rose-50 text-slate-400 hover:text-rose-500 transition-colors"
                          title="Remove Folder"
                        >
                          <XIcon class="h-4 w-4" />
                        </button>
                      </div>
                    </div>

                    <!-- Root Files Preview (if any) -->
                    <div
                      v-if="getUniqueFolders(item.documents, true).length > 0"
                      class="mt-3 flex flex-wrap gap-2"
                    >
                      <div
                        v-for="(doc, dIdx) in getUniqueFolders(
                          item.documents,
                          true,
                        )"
                        :key="dIdx"
                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-100 rounded-lg text-[10px] font-bold text-emerald-600 animate-in zoom-in-95 duration-200"
                      >
                        <CheckCircleIcon class="h-3 w-3" />
                        <span class="truncate max-w-[100px]">{{
                          getFileName(doc.path)
                        }}</span>
                        <button
                          type="button"
                          @click="removeDoc(index, dIdx)"
                          class="hover:text-rose-500"
                        >
                          <XIcon class="h-3 w-3" />
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Remarks Field -->
                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Remarks</label
                    >
                    <textarea
                      v-model="item.remarks"
                      rows="2"
                      class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all placeholder:text-slate-300 resize-none font-medium text-slate-600"
                      placeholder="Special instructions or notes for this scan..."
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div
            v-if="form.items.length === 0"
            class="py-12 border-2 border-dashed border-slate-100 rounded-3xl flex flex-col items-center justify-center gap-3"
          >
            <div
              class="h-12 w-12 rounded-full bg-slate-50 flex items-center justify-center"
            >
              <AlertCircleIcon class="h-6 w-6 text-slate-300" />
            </div>
            <p class="text-sm font-medium text-slate-400 italic">
              No scan items added yet.
            </p>
          </div>

          <!-- Final Actions -->
          <div
            class="mt-12 flex items-center justify-end gap-4 border-t border-slate-100 pt-8"
          >
            <div
              v-if="error"
              class="text-xs text-rose-500 font-bold px-4 flex items-center gap-2 animate-in fade-in"
            >
              <AlertCircleIcon class="h-3.5 w-3.5" />
              {{ error }}
            </div>
            <button
              type="submit"
              :disabled="loading"
              class="px-10 py-4 bg-primary text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary/20 hover:opacity-90 active:scale-[0.98] transition-all disabled:opacity-50 flex items-center gap-3"
            >
              <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
              {{ loading ? "Updating..." : "Save Changes" }}
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- Loading State: Skeleton Loader -->
    <div
      v-else
      class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-in fade-in duration-500"
    >
      <!-- Left Column Skeleton: Case Info -->
      <div class="lg:col-span-1 space-y-8">
        <div
          class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-6"
        >
          <div class="flex items-center gap-3 mb-2">
            <Skeleton class="h-10 w-10 rounded-2xl" />
            <Skeleton class="h-6 w-32" />
          </div>
          <div class="space-y-4">
            <div class="space-y-2">
              <Skeleton class="h-3 w-16 ml-1" />
              <Skeleton class="h-10 w-full rounded-lg" />
            </div>
            <div class="space-y-2">
              <Skeleton class="h-3 w-16 ml-1" />
              <Skeleton class="h-10 w-full rounded-lg" />
            </div>
            <div class="space-y-2">
              <Skeleton class="h-3 w-32 ml-1" />
              <Skeleton class="h-12 w-full rounded-2xl" />
            </div>
            <div class="space-y-2">
              <Skeleton class="h-3 w-32 ml-1" />
              <Skeleton class="h-24 w-full rounded-2xl" />
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column Skeleton: Scan Items -->
      <div class="lg:col-span-2 space-y-8">
        <div
          class="bg-white rounded-[40px] border border-slate-200 p-8 md:p-10 shadow-soft-xl"
        >
          <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
              <Skeleton class="h-10 w-10 rounded-2xl" />
              <Skeleton class="h-6 w-48" />
            </div>
            <Skeleton class="h-10 w-32 rounded-xl" />
          </div>
          <div class="space-y-6">
            <div
              class="bg-slate-50/50 rounded-[32px] border border-slate-100 p-8 space-y-6"
            >
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div class="space-y-2">
                    <Skeleton class="h-3 w-20 ml-1" />
                    <Skeleton class="h-10 w-full rounded-lg" />
                  </div>
                  <div class="space-y-2">
                    <Skeleton class="h-3 w-24 ml-1" />
                    <Skeleton class="h-10 w-full rounded-lg" />
                  </div>
                </div>
                <div class="space-y-4">
                  <Skeleton class="h-3 w-32 ml-1" />
                  <Skeleton class="h-28 w-full rounded-2xl" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- WhatsApp Recipient Selection Modal -->
    <WhatsAppRecipientModal
      :is-open="isWhatsappModalOpen"
      :report="reportForWhatsapp"
      :loading="sendingWhatsapp"
      :initial-recipients="initialRecipients"
      @close="handleModalClose"
      @confirm="handleSendWhatsApp"
    />

    <!-- DICOM Upload Progress & Confirmation Modal -->
    <DicomUploadModal
      :is-open="uploadModal.isOpen"
      :state="uploadModal.state"
      :file-count="uploadModal.fileCount"
      :progress="uploadModal.progress"
      :current-file-index="uploadModal.currentFileIndex"
      :total-files="uploadModal.totalFiles"
      :current-file-name="uploadModal.currentFileName"
      :title="uploadModal.title"
      :description="uploadModal.description"
      :variant="uploadModal.variant"
      @confirm="startBatchedUpload"
      @close="uploadModal.isOpen = false"
    />
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useCaseReportForm } from "../../composables/useCaseReportForm";
import Skeleton from "../../components/ui/skeleton/Skeleton.vue";
import WhatsAppRecipientModal from "../../components/notifications/WhatsAppRecipientModal.vue";
import DicomUploadModal from "../../components/dicom/DicomUploadModal.vue";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";
import {
  User as UserIcon,
  Activity as ActivityIcon,
  Plus as PlusIcon,
  X as XIcon,
  Upload as UploadIcon,
  Loader2 as Loader2Icon,
  AlertCircle as AlertCircleIcon,
  ChevronDown as ChevronDownIcon,
  Paperclip as PaperclipIcon,
  Check as CheckIcon,
  CheckCircle as CheckCircleIcon,
  Folder as FolderIcon,
  Search as SearchIcon,
} from "lucide-vue-next";

// Initialize useCaseReportForm with isEdit = true
const {
  loading,
  fetching,
  error,
  form,
  patients,
  doctors,
  scanTypes,
  processingGeneral,
  uploadModal,
  isWhatsappModalOpen,
  reportForWhatsapp,
  sendingWhatsapp,
  initialRecipients,
  fetchMasters,
  fetchCaseReport,
  getScans,
  getFileName,
  addItem,
  removeItem,
  getUniqueFolders,
  handleGeneralFiles,
  removeGeneralDoc,
  handleDrop,
  handleFiles,
  startBatchedUpload,
  removeFolder,
  removeDoc,
  handleSubmit,
  handleSendWhatsApp,
  handleModalClose,
} = useCaseReportForm(true);

const patientSearch = ref("");
const filteredPatients = computed(() => {
  if (!patientSearch.value) return patients.value;
  const query = patientSearch.value.toLowerCase();
  return patients.value.filter(
    (p) =>
      p.name.toLowerCase().includes(query) ||
      p.patient_id?.toLowerCase().includes(query),
  );
});

const doctorSearch = ref("");
const filteredDoctors = computed(() => {
  if (!doctorSearch.value) return doctors.value;
  const query = doctorSearch.value.toLowerCase();
  return doctors.value.filter((d) => d.name.toLowerCase().includes(query));
});

const route = useRoute();

onMounted(async () => {
  // 1. Fetch masters first
  await fetchMasters();
  // 2. Then fetch the case report
  await fetchCaseReport(route.params.id);
});
</script>
