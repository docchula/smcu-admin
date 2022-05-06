<template>
    <app-layout>
        <template #header>
            <inertia-link :href="item.id ? route('documents.show', {document: item.id}) : route('documents.index')" class="mb-4 block flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                <p v-if="item.id">
                    {{ item.title }}
                </p>
                <p v-else>สารบรรณ</p>
            </inertia-link>
            <h2 v-if="item.id" class="font-semibold text-xl text-gray-800 leading-tight">
                แก้ไขหนังสือเลขที่ {{ item.number }}/{{ item.year }}
            </h2>
            <template v-else>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">สร้างเอกสารใหม่</h2>
                <p class="mt-2 text-gray-500">กรุณาร่างเอกสารให้เสร็จก่อน เหลือเฉพาะเลขที่เอกสาร</p>
            </template>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <jet-form-section @submitted="submit">
                <template #title>ข้อมูลพื้นฐาน</template>
                <template #description>ชื่อผู้ใช้ที่สร้างเอกสารจะถูกบันทึกและแสดงผลในฐานช้อมูล</template>
                <template #form>
                    <div class="col-span-6">
                        <jet-label for="title" value="หัวเรื่อง"/>
                        <jet-input id="title" type="text" class="mt-1 block w-full" v-model.trim="form.title" ref="title" required/>
                        <jet-input-error v-if="form.errors.title" :message="form.errors.title" class="mt-2"/>
                        <p v-else class="mt-2 text-xs text-gray-500">ข้อความสั้น ๆ ที่ทำให้ผู้รับเข้าใจความประสงค์หรือเนื้อหาโดยสังเขป</p>
                    </div>
                    <div class="col-span-6">
                        <jet-label for="recipient" value="ผู้รับ"/>
                        <jet-input id="recipient" type="text" class="mt-1 block w-full" v-model.trim="form.recipient" ref="recipient" required/>
                        <jet-input-error v-if="form.errors.recipient" :message="form.errors.recipient" class="mt-2"/>
                        <p v-else class="mt-2 text-xs text-gray-500">
                            ควรใช้ชื่อตำแหน่ง (ถ้ามี) เช่น
                            <a class="cursor-pointer text-green-500" @click="form.recipient = 'รองคณบดีฝ่ายกิจการนิสิต'">รองคณบดีฝ่ายกิจการนิสิต</a>
                        </p>
                    </div>
                    <div class="col-span-6">
                        <label for="department" class="block text-sm font-medium text-gray-700">หน่วยงานที่รับผิดชอบ</label>
                        <select id="department" v-model="form.department_id" required
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option v-for="department in static_departments" v-bind:key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                        <jet-input-error v-if="form.errors.department_id" :message="form.errors.department_id" class="mt-2"/>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submitted="submit">
                <template #title>ออกหนังสือหลายฉบับ</template>
                <template #description>กรณีต้องการออกหนังสือเรื่องเดียวกันไปยังผู้รับจำนวนมาก (เช่น หนังสือเชิญ) ระบบจะออกเลขที่หนังสือให้หลายเลขติดต่อกัน</template>
                <template #form>
                    <div class="col-span-6">
                        <jet-label for="amount" value="จำนวนหนังสือ"/>
                        <jet-input id="amount" type="number" class="mt-1 block w-full" v-model.number="form.amount" ref="amount" required step="1" min="1" max="500" :disabled="item.id"/>
                        <jet-input-error v-if="form.errors.amount" :message="form.errors.amount" class="mt-2"/>
                        <p v-else-if="form.amount > 1" class="mt-2 text-xs text-gray-500">
                            อาจกรอกผู้รับเป็นชื่อกลุ่มของผู้รับ เช่น "อาจารย์ทั้งหมดในคณะ"
                        </p>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section>
                <template #title>โครงการ</template>
                <template #description>
                    หนังสือฉบับนี้เป็นการดำเนินงานของโครงการใด หากไม่มีให้เว้นว่าง<br/>
                    <strong>ก่อนส่งหนังสือขออนุมัติโครงการต้องลงทะเบียน<inertia-link :href="route('projects.index')" class="text-green-500">โครงการ</inertia-link> และกรอกเลขที่โครงการในเอกสารก่อน</strong>
                </template>
                <template #form>
                    <div v-if="selectedProject" class="col-span-6">
                        โครงการ<strong>{{ selectedProject.name }}</strong> สังกัด{{ selectedProject.department.name }}
                        <a class="cursor-pointer text-green-500" @click="projectKeyword = ''"><svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-4 w-4 text-red-500 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg></a>
                    </div>
                    <div v-else class="col-span-6">
                        <div>
                            <jet-label for="project" value="ชื่อหรือเลขที่โครงการ (หากไม่มีให้เว้นว่าง)"/>
                            <jet-input id="project" type="text" class="mt-1 block w-full" v-model.trim="projectKeyword" ref="project"/>
                            <jet-input-error v-if="keywordError" :message="keywordError" class="mt-2"/>
                            <p v-else-if="projectKeyword !== ''" class="mt-2 text-xs text-gray-500">
                                ยังไม่ได้เลือก (<a class="cursor-pointer text-green-500" @click="projectKeyword = ''">ล้าง</a>)
                            </p>
                        </div>
                        <div v-if="projectSearchResult.length > 0" class="border-l border-r border-b border-gray-200">
                            <div class="border-t px-3 py-2 flex hover:bg-gray-50 cursor-pointer" v-for="item in projectSearchResult" :key="item.id" @click="selectProject(item)">
                                <div class="flex-auto items-center">
                                    {{ item.name }}
                                </div>
                                <div class="flex-initial items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="block h-5 w-5 text-gray-600" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submitted="submit">
                <template #title>ไฟล์เอกสาร</template>
                <template #description>กรุณาแนบไฟล์เอกสารทั้งฉบับ รวมเป็นไฟล์เดียว ในรูปแบบ pdf หรือ docx ขนาดไม่เกิน 15 MB</template>
                <template #form>
                    <p v-if="form.attachment" class="col-span-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z"
                                  clip-rule="evenodd"/>
                        </svg>
                        {{ form.attachment.name }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 cursor-pointer ml-4 inline" viewBox="0 0 20 20" fill="currentColor" @click="form.attachment = null">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </p>
                    <div v-else v-cloak @drop.prevent="dropFile" @dragover.prevent class="col-span-6 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload"
                                       class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="file-upload" type="file" class="sr-only" accept="application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword" @input="form.attachment = $event.target.files[0]">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PDF or DOCX up to 15 MB
                            </p>
                        </div>
                    </div>
                    <jet-input-error :message="form.errors.attachment" class="col-span-6"/>
                </template>
                <template #actions>
                    <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                        Saved.
                    </jet-action-message>
                    <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="mr-3">
                        {{ form.progress.percentage }}%
                    </progress>

                    <jet-button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Save
                    </jet-button>
                </template>
            </jet-form-section>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetButton from '@/Jetstream/Button'
import JetFormSection from '@/Jetstream/FormSection'
import JetInput from '@/Jetstream/Input'
import JetInputError from '@/Jetstream/InputError'
import JetLabel from '@/Jetstream/Label'
import JetSectionBorder from '@/Jetstream/SectionBorder'
import {isNumber} from "lodash";
import _ from "lodash";

export default {
    components: {
        AppLayout, JetActionMessage,
        JetButton,
        JetFormSection,
        JetInput,
        JetInputError,
        JetLabel,
        JetSectionBorder,
    },

    data() {
        return {
            projectKeyword: this.item.project ? (this.item.project.year + '-' + this.item.project.number) : "",
            projectSearchResult: [],
            keywordError: "",
            selectedProject: this.item.project,
            form: this.$inertia.form({
                _method: this.item.id ? 'PUT' : 'POST',
                title: this.item.title ?? "",
                recipient: this.item.recipient ?? "",
                department_id: this.item.department_id ?? "",
                amount: this.item.amount ?? 1,
                project_id: this.item.project_id,
                attachment: null,
            }),
        }
    },

    methods: {
        dropFile(e) {
            // from https://www.raymondcamden.com/2019/08/08/drag-and-drop-file-upload-in-vuejs
            let droppedFiles = e.dataTransfer.files;
            if (!droppedFiles) return;
            // this tip, convert FileList to array, credit: https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
            ([...droppedFiles]).slice(0, 1).forEach(f => {
                this.form.attachment = f;
            });
        },
        submit() {
            if (!this.form.attachment && !this.item.id) {
                this.form.errors.attachment = "กรุณาอัปโหลดร่างเอกสาร";
            } else if (this.form.attachment && !(this.form.attachment.name.endsWith('.pdf') || this.form.attachment.name.endsWith('.docx'))) {
                this.form.errors.attachment = "ไม่รองรับประเภทไฟล์นี้";
            } else {
                this.form.project_id = this.selectedProject ? this.selectedProject.id : null;
                this.form.post(this.item.id
                    ? this.route('documents.update', {document: this.item.id})
                    : this.route('documents.store')
                )
            }
        },
        searchProject: _.debounce(function (keyword) {
            this.keywordError = 'กำลังค้นหา...';
            axios.get(this.route('projects.search', {
                keyword: keyword
            })).then((response) => {
                this.keywordError = '';
                this.projectSearchResult = response.data;
            }).catch((error) => {
                this.keywordError = 'Error! Could not reach the API. ' + error;
            })
        }, 500),
        selectProject(item) {
            this.selectedProject = item;
        }
    },
    watch: {
        projectKeyword: function (newValue, oldValue) {
            this.keywordError = "กำลังพิมพ์...";
            if (newValue === "") {
                this.keywordError = "";
                this.projectSearchResult = [];
                this.selectedProject = null;
                return;
            } else if (newValue.length < 2 && !isNumber(newValue)) {
                this.keywordError = 'กรุณากรอกคำค้นหาอย่างน้อย 2 ตัวอักษร';
                return;
            }
            this.searchProject(newValue);
        },
    },

    props: {
        item: Object,
        static_departments: Array,
    }
};
</script>
