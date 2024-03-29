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
                <p class="mt-2 text-gray-500">
                    กรุณาร่างเอกสารให้เสร็จก่อน เหลือเฉพาะเลขที่เอกสาร<br/>
                    เอกสารอาจต้องผ่านการพิจารณาหลายขั้นตอน นิสิตจึงต้องดำเนินการส่งเอกสารล่วงหน้าพอสมควร
                </p>
            </template>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <jet-form-section @submitted="submit">
                <template #title>ข้อมูลพื้นฐาน</template>
                <template #description>
                    ชื่อผู้ใช้ที่สร้างเอกสารจะถูกบันทึกและแสดงผลในฐานช้อมูล
                    <p class="mt-2 text-blue-600">ยกเลิกตัวเลือกหน่วยงาน <i>โครงการค่ายอยากเป็นหมอ โครงการ MDCU Counseling Unit โครงการ CU Open
                        House</i> เนื่องจากไม่มีสภาพหน่วยงาน ให้เลือก "คณะกรรมการบริหาร" แทน</p>
                </template>
                <template #form>
                    <div class="col-span-6">
                        <jet-label for="title" value="หัวเรื่อง"/>
                        <jet-input id="title" type="text" class="mt-1 block w-full" v-model.trim="form.title" ref="title" required/>
                        <jet-input-error v-if="form.errors.title" :message="form.errors.title" class="mt-2"/>
                        <jet-input-error v-else-if="form.title.startsWith('เอกสาร')" message='ไม่ต้องขึ้นต้นด้วยคำว่า "เอกสาร"' class="mt-2"/>
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
                            <!-- hide if sequence >= 200 (deprecated values) -->
                            <option v-for="department in static_departments"
                                    v-show="department.sequence < 200 || department.id === form.department_id"
                                    v-bind:key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                        <jet-input-error v-if="form.errors.department_id" :message="form.errors.department_id" class="mt-2"/>
                    </div>
                    <fieldset class="col-span-6">
                        <div class="flex gap-x-8">
                            <div class="flex items-center">
                                <input id="tag_none" v-model="form.tag" name="tag" value="" type="radio" required
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="tag_none" class="ml-3 block text-sm font-medium text-gray-700">
                                    หนังสือทั่วไป
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="tag_approval" v-model="form.tag" name="tag" value="approval" type="radio"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="tag_approval" class="ml-3 block text-sm font-medium text-gray-700">
                                    ขออนุมัติดำเนินโครงการ <i>(เปิดโครง)</i>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="tag_summary" v-model="form.tag" name="tag" value="summary" type="radio"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="tag_summary" class="ml-3 block text-sm font-medium text-gray-700">
                                    รายงานผลการดำเนินโครงการ <i>(ปิดโครง)</i>
                                </label>
                            </div>
                        </div>
                        <jet-input-error :message="form.errors.tag" class="mt-2"/>
                    </fieldset>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <template v-if="!form.tag && !item.id">
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
            </template>
            <jet-form-section>
                <template #title>โครงการ</template>
                <template #description>
                    หนังสือฉบับนี้เป็นการดำเนินงานของโครงการใด<br/>
                    <strong>ก่อนส่งหนังสือขออนุมัติโครงการต้องลงทะเบียน<a :href="route('projects.index')" target="_blank" class="text-green-500">โครงการ</a>
                        และกรอกเลขที่โครงการในเอกสารก่อน</strong>
                </template>
                <template #form>
                    <div v-if="selectedProject" class="col-span-6">
                        โครงการ<strong>{{ selectedProject.name }}</strong> <template v-if="selectedProject.department_id !== 33">สังกัด{{ selectedProject.department.name }}</template>
                        <a class="cursor-pointer text-green-500" @click="projectKeyword = ''">
                            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-4 w-4 text-red-500 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    </div>
                    <div v-else class="col-span-6">
                        <div class="mb-4" v-if="!form.is_non_project">
                            <div>
                                <jet-label for="project" value="ค้นหาด้วยชื่อหรือเลขที่โครงการ"/>
                                <jet-input id="project" type="text" class="mt-1 block w-full" v-model.trim="projectKeyword" ref="project"/>
                                <jet-input-error v-if="keywordError" :message="keywordError" class="mt-2"/>
                                <p v-else-if="projectKeyword !== ''" class="mt-2 text-xs text-gray-500">
                                    ยังไม่ได้เลือก (<a class="cursor-pointer text-green-500" @click="projectKeyword = ''">ล้าง</a>)
                                </p>
                            </div>
                            <div v-if="projectSearchResult.length > 0" class="border-l border-r border-b border-gray-200">
                                <div class="border-t px-3 py-2 flex hover:bg-gray-50 cursor-pointer" v-for="item in projectSearchResult" :key="item.id" @click="selectProject(item)">
                                    <div class="flex-auto items-center">
                                        <span class="text-xs text-gray-500 px-0.5">{{ item.year }}-{{ item.number }}</span>
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
                        <div v-if="!form.tag || form.is_non_project" class="flex items-start">
                            <div class="flex items-center h-5">
                                <Checkbox id="is_non_project" :checked="form.is_non_project" @update:checked="newValue => form.is_non_project = newValue"/>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_non_project" class="font-medium text-gray-700">ไม่ใช่การดำเนินโครงการ</label>
                            </div>
                        </div>
                    </div>
                    <jet-input-error :message="form.errors.project_id" class="col-span-6"/>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submitted="submit">
                <template #title>ไฟล์เอกสาร</template>
                <template #description>กรุณาแนบไฟล์เอกสารทั้งฉบับ รวมเป็นไฟล์เดียว ในรูปแบบ pdf หรือ docx ขนาดไม่เกิน 15 MB</template>
                <template #form>
                    <AttachmentBox class="col-span-6" v-model="form.attachment"
                                   accept="application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword"
                                   description="PDF or DOCX up to 15 MB"/>
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
            <jet-section-border/>
            <jet-form-section v-if="item.id && is_admin">
                <template #title>เอกสารที่ได้รับอนุมัติแล้ว</template>
                <template #description>
                    <p class="text-amber-500">(สำหรับผู้ดูแลระบบ)</p>
                    อัปโหลดไฟล์เอกสารที่ลงนามอนุมัติแล้ว
                </template>
                <template #form>
                    <AttachmentBox class="col-span-6" v-model="form.approved_attachment"
                                   accept="application/pdf"
                                   description="PDF up to 15 MB"/>
                    <jet-input-error :message="form.errors.approved_attachment" class="col-span-6"/>
                </template>
            </jet-form-section>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import JetActionMessage from '@/Jetstream/ActionMessage.vue'
import JetButton from '@/Jetstream/Button.vue'
import JetFormSection from '@/Jetstream/FormSection.vue'
import JetInput from '@/Jetstream/Input.vue'
import JetInputError from '@/Jetstream/InputError.vue'
import JetLabel from '@/Jetstream/Label.vue'
import JetSectionBorder from '@/Jetstream/SectionBorder.vue'
import _, {isNumber} from "lodash";
import Checkbox from "../Jetstream/Checkbox.vue";
import AttachmentBox from "@/Components/AttachmentBox.vue";

export default {
    components: {
        AttachmentBox,
        AppLayout,
        Checkbox,
        JetActionMessage,
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
                approved_attachment: null,
                is_non_project: this.item.id ? !this.item.project_id : false,
                tag: this.item.tag ?? "",
            }),
        }
    },

    methods: {
        submit() {
            if (!this.form.attachment && !this.item.id) {
                this.form.errors.attachment = "กรุณาอัปโหลดร่างเอกสาร";
            } else if (this.form.attachment && !(this.form.attachment.name.endsWith('.pdf') || this.form.attachment.name.endsWith('.docx'))) {
                this.form.errors.attachment = "ไม่รองรับประเภทไฟล์นี้";
            } else if (!this.selectedProject && !this.form.is_non_project) {
                this.form.errors.project_id = "กรุณาเลือกโครงการ";
            } else {
                if (this.form.tag) {
                    this.form.amount = 1;
                }
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
        projectKeyword: function (newValue) {
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
        is_admin: Boolean,
    }
};
</script>
