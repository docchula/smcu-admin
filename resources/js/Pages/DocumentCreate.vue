<template>
    <app-layout>
        <Head title="สร้างเอกสารใหม่"/>
        <template #header>
            <Link :href="item.id ? route('documents.show', {document: item.id}) : route('documents.index')"
                  class="mb-4 flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                <p v-if="item.id">
                    {{ item.title }}
                </p>
                <p v-else>สารบรรณ</p>
            </Link>
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
            <jet-form-section>
                <template #title>โครงการ</template>
                <template #description>
                    หนังสือฉบับนี้เป็นการดำเนินงานของโครงการใด<br/>
                    <strong>ก่อนส่งหนังสือขออนุมัติโครงการต้องลงทะเบียน<a :href="route('projects.index')" target="_blank" class="text-green-500">โครงการ</a>
                        และกรอกเลขที่โครงการในเอกสารก่อน</strong>
                </template>
                <template #form>
                    <fieldset class="col-span-6">
                        <jet-label for="" value="ประเภทหนังสือ"/>
                        <ul class="mt-1.5 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg">
                            <li class="w-full border-b border-gray-200 rounded-t-lg">
                                <div class="flex items-center ps-3">
                                    <input v-model="form.tag" id="list-radio-license" type="radio" value="" name="tag" required
                                           class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500 focus:ring-2">
                                    <label for="list-radio-license" class="w-full py-3 ms-2 text-sm font-medium text-gray-900">หนังสือทั่วไป</label>
                                </div>
                            </li>
                            <li class="w-full border-b border-gray-200 rounded-t-lg">
                                <div class="flex items-center ps-3">
                                    <input v-model="form.tag" id="list-radio-approval" type="radio" value="approval" name="tag"
                                           class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500 focus:ring-2">
                                    <label for="list-radio-approval" class="w-full py-3 ms-2 text-sm font-medium text-gray-900">ขออนุมัติดำเนินโครงการ
                                        <i>(เปิดโครง)</i></label>
                                </div>
                            </li>
                            <li class="w-full border-b border-gray-200 rounded-t-lg">
                                <div class="flex items-center ps-3">
                                    <input v-model="form.tag" id="list-radio-summary" type="radio" value="summary" name="tag"
                                           class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500 focus:ring-2">
                                    <label for="list-radio-summary" class="w-full py-3 ms-2 text-sm font-medium text-gray-900">รายงานผลการดำเนินโครงการ
                                        <i>(ปิดโครง)</i></label>
                                </div>
                            </li>
                        </ul>
                        <jet-input-error :message="errors.tag" class="mt-2"/>
                    </fieldset>
                    <div v-if="selectedProject" class="col-span-6">
                        <a :href="route('projects.show', {project: selectedProject.id})" target="_blank">โครงการ<strong>{{
                                selectedProject.name
                            }}</strong></a>&ensp;
                        <template v-if="selectedProject.department_id !== 33">สังกัด{{ selectedProject.department.name }}</template>
                        <a class="cursor-pointer text-green-500" @click="projectKeyword = ''">
                            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-4 w-4 text-red-500 ml-2" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    </div>
                    <div v-else class="col-span-6">
                        <div class="mb-4" v-if="!form.is_non_project">
                            <div>
                                <jet-label for="project" value="ค้นหาโครงการ"/>
                                <jet-input id="project" type="text" class="mt-1 block w-full" v-model.trim="projectKeyword" ref="project"
                                           placeholder="ค้นหาด้วยชื่อหรือเลขที่โครงการ"/>
                                <jet-input-error v-if="keywordError" :message="keywordError" class="mt-2"/>
                                <p v-else-if="projectKeyword !== ''" class="mt-2 text-xs text-gray-500">
                                    ยังไม่ได้เลือก (<a class="cursor-pointer text-green-500" @click="projectKeyword = ''">ล้าง</a>)
                                </p>
                            </div>
                            <div v-if="projectSearchResult.length > 0" class="border-l border-r border-b border-gray-200">
                                <div class="border-t px-3 py-2 flex hover:bg-gray-50 cursor-pointer" v-for="item in projectSearchResult"
                                     :key="item.id" @click="selectProject(item)">
                                    <div class="flex-auto items-center">
                                        <span class="text-xs text-gray-500 px-0.5">{{ item.year }}-{{ item.number }}</span>
                                        {{ item.name }}
                                    </div>
                                    <div class="flex-initial items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="block h-5 w-5 text-gray-600" fill="none" viewBox="0 0 20 20"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!form.tag || form.is_non_project" class="flex items-start">
                            <div class="flex items-center h-5">
                                <Checkbox id="is_non_project" :checked="form.is_non_project"
                                          @update:checked="newValue => form.is_non_project = newValue"/>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_non_project" class="font-medium text-gray-700">ไม่ใช่การดำเนินโครงการ</label>
                            </div>
                        </div>
                    </div>
                    <jet-input-error :message="errors.project_id" class="col-span-6"/>
                </template>
            </jet-form-section>
            <template v-if="(form.tag !== 'approval' && form.tag !== 'summary') || selectedProject">
                <jet-section-border/>
                <jet-form-section @submitted="submit">
                    <template #title>ข้อมูลพื้นฐาน</template>
                    <template #description>
                        ชื่อผู้ใช้ที่สร้างเอกสารจะถูกบันทึกและแสดงผลในฐานช้อมูล
                    </template>
                    <template #form>
                        <div class="col-span-6">
                            <jet-label for="title" value="หัวเรื่อง"/>
                            <jet-input id="title" type="text" class="mt-1 block w-full" v-model.trim="form.title" ref="title" required/>
                            <jet-input-error v-if="errors.title" :message="errors.title" class="mt-2"/>
                            <jet-input-error v-else-if="form.title.startsWith('เอกสาร')" message='ไม่ต้องขึ้นต้นด้วยคำว่า "เอกสาร"' class="mt-2"/>
                            <p v-else class="mt-2 text-xs text-gray-500">ข้อความสั้น ๆ ที่ทำให้ผู้รับเข้าใจความประสงค์หรือเนื้อหาโดยสังเขป</p>
                        </div>
                        <div class="col-span-6">
                            <jet-label for="recipient" value="ผู้รับ"/>
                            <jet-input id="recipient" type="text" class="mt-1 block w-full" v-model.trim="form.recipient" ref="recipient" required/>
                            <jet-input-error v-if="errors.recipient" :message="errors.recipient" class="mt-2"/>
                            <p v-else class="mt-2 text-xs text-gray-500">
                                ควรใช้ชื่อตำแหน่ง (ถ้ามี) เช่น
                                <a class="cursor-pointer text-green-500"
                                   @click="form.recipient = 'รองคณบดีฝ่ายกิจการนิสิต'">รองคณบดีฝ่ายกิจการนิสิต</a>
                            </p>
                        </div>
                        <div class="col-span-6">
                            <label for="department" class="block text-sm font-medium text-gray-700">หน่วยงานที่รับผิดชอบ</label>
                            <select id="department" v-model="form.department_id" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <!-- hide if sequence >= 200 (deprecated values) -->
                                <template v-for="department in static_departments" v-bind:key="department.id">
                                    <option v-if="department.sequence < 200 || department.id === form.department_id" :value="department.id">
                                        {{ department.name }}
                                    </option>
                                </template>
                            </select>
                            <jet-input-error v-if="errors.department_id" :message="errors.department_id" class="mt-2"/>
                        </div>
                    </template>
                </jet-form-section>
                <jet-section-border/>
                <template v-if="!form.tag && !item.id">
                    <jet-form-section @submitted="submit">
                        <template #title>ออกหนังสือหลายฉบับ</template>
                        <template #description>กรณีต้องการออกหนังสือเรื่องเดียวกันไปยังผู้รับจำนวนมาก (เช่น หนังสือเชิญ)
                            ระบบจะออกเลขที่หนังสือให้หลายเลขติดต่อกัน
                        </template>
                        <template #form>
                            <div class="col-span-6">
                                <jet-label for="amount" value="จำนวนหนังสือ"/>
                                <jet-input id="amount" type="number" class="mt-1 block w-full" v-model.number="form.amount" ref="amount" required
                                           step="1"
                                           min="1" max="500" :disabled="item.id"/>
                                <jet-input-error v-if="errors.amount" :message="errors.amount" class="mt-2"/>
                                <p v-else-if="form.amount > 1" class="mt-2 text-xs text-gray-500">
                                    อาจกรอกผู้รับเป็นชื่อกลุ่มของผู้รับ เช่น "อาจารย์ทั้งหมดในคณะ"
                                </p>
                            </div>
                        </template>
                    </jet-form-section>
                    <jet-section-border/>
                </template>
                <template v-if="form.tag === 'summary' && selectedProject">
                    <div class="ml-1 pl-1 sm:ml-0 sm:pl-3 border-l-4 border-indigo-500">
                        <h6 class="mb-2 px-4 sm:px-0 text-sm text-indigo-500">ผลการดำเนินโครงการ</h6>
                        <jet-form-section>
                            <template #title>ประเมินความสำเร็จตามเป้าหมาย</template>
                            <template #description>
                                ตามที่ได้กำหนดเป้าหมายของโครงการไว้ หลังดำเนินโครงการเสร็จสิ้นแล้ว ได้ผลอย่างไร
                            </template>
                            <template #form>
                                <div class="col-span-6">
                                    <div v-if="hasFilledClosureForm"
                                         class="p-3 rounded bg-green-100 border border-green-600 text-green-600 mb-2 text-xl font-bold">
                                        มีข้อมูลรายงานผลโครงการเรียบร้อยแล้ว
                                        <Link :href="route('projects.closureForm', {project: selectedProject.id})">
                                            <jet-button type="button" class="ml-2 inline-block">
                                                แก้ไข
                                            </jet-button>
                                        </Link>
                                    </div>
                                    <div v-else class="p-3 rounded bg-blue-100 border border-blue-600 text-blue-600">
                                        <h4 class="mb-2 text-xl font-bold">
                                            กรุณาบันทึกข้อมูลรายงานผลโครงการ ก่อนดำเนินการต่อ
                                        </h4>
                                        <Link :href="route('projects.closureForm', {project: selectedProject.id})">
                                            <jet-button type="button">
                                                บันทึกข้อมูลรายงานผลโครงการ
                                            </jet-button>
                                        </Link>
                                    </div>
                                </div>
                                <table v-if="selectedProject.objectives.length > 0" class="col-span-6 divide-y divide-gray-200">
                                    <thead>
                                    <tr class="text-xs text-left text-gray-500 tracking-wider">
                                        <th scope="col" class="px-1 pb-2 font-medium">
                                            ตัวชี้วัด
                                        </th>
                                        <th scope="col" class="px-1 pb-2 font-medium">
                                            วิธีการประเมินผล
                                        </th>
                                        <th scope="col" class="px-1 pb-2 font-medium">
                                            ผลที่ได้
                                        </th>
                                        <th scope="col" class="px-1 pb-2 font-medium">
                                            คิดเป็น
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(member, index) in selectedProject.objectives">
                                        <td class="px-1 py-3">
                                            <span class="text-indigo-500">{{ index + 1 }}.</span> {{ member.goal }}
                                        </td>
                                        <td class="px-1 py-3 text-sm">
                                            {{ member.method }}
                                        </td>
                                        <td class="px-1 py-3">
                                            {{ member.result }}
                                        </td>
                                        <td class="px-1 py-3 whitespace-nowrap">
                                            {{ member.percentage }}<span class="ml-0.5 text-xs">%</span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </template>
                        </jet-form-section>
                        <jet-section-border/>
                        <jet-form-section>
                            <template #title>รายจ่าย</template>
                            <template #description>
                                จากที่ได้วางแผนของบประมาณไว้ ใช้จริงไปเท่าใด
                            </template>
                            <template #form>
                                <table v-if="selectedProject.expense.length > 0" class="col-span-6 divide-y divide-gray-200">
                                    <thead>
                                    <tr class="text-left text-xs text-gray-500 tracking-wider">
                                        <th scope="col" class="px-1 pb-2 font-medium">
                                            รายการ
                                        </th>
                                        <th scope="col" class="px-1 pb-2 font-medium">
                                            ประเภท
                                        </th>
                                        <th scope="col" class="px-1 pb-2 font-medium">
                                            แหล่ง
                                        </th>
                                        <th scope="col" class="px-1 pb-2 font-medium">
                                            วางแผนงบ (บ.)
                                        </th>
                                        <th scope="col" class="px-1 pb-2 font-medium">
                                            จ่ายจริงไป (บ.)
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(member, index) in selectedProject.expense">
                                        <td class="px-1 py-3">
                                            {{ member.name }}
                                        </td>
                                        <td class="px-1 py-3 text-sm">
                                            {{ member.type }}
                                        </td>
                                        <td class="px-1 py-3 text-sm">
                                            {{ member.source }}
                                        </td>
                                        <td class="px-1 py-3 text-right">
                                            {{ Number(member.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                                        </td>
                                        <td class="px-1 py-3 text-right">
                                        <span v-if="member.paid || member.paid === 0">
                                            {{ Number(member.paid).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                                        </span>
                                            <span v-else class="text-red-500">
                                            ไม่มีข้อมูล
                                        </span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="col-span-6">
                                    <p v-if="selectedProject.expense.length === 0" class="mb-2">ไม่ได้ของบประมาณ</p>
                                    <jet-input-error :message="errors.expense" class="col-span-6"/>
                                </div>
                            </template>
                        </jet-form-section>
                    </div>
                    <jet-section-border/>
                </template>
                <template v-if="form.tag !== 'summary' || hasFilledClosureForm">
                    <jet-form-section @submitted="submit">
                        <template #title>ไฟล์เอกสาร</template>
                        <template #description>กรุณาแนบไฟล์เอกสารทั้งฉบับ รวมเป็นไฟล์เดียว ในรูปแบบ pdf หรือ docx ขนาดไม่เกิน 15 MB</template>
                        <template #form>
                            <AttachmentBox class="col-span-6" v-model="form.attachment"
                                           accept="application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword"
                                           description="PDF or DOCX up to 15 MB"/>
                            <jet-input-error :message="errors.attachment" class="col-span-6"/>
                        </template>
                        <template #actions>
                            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                                Saved.
                            </jet-action-message>
                            <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="mr-3">
                                {{ form.progress.percentage }}%
                            </progress>

                            <jet-button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ item.id ? 'บันทึก' : 'บันทึก & ออกเลขเอกสาร' }}
                            </jet-button>
                        </template>
                    </jet-form-section>
                    <jet-section-border/>
                </template>
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
                        <jet-input-error :message="errors.approved_attachment" class="col-span-6"/>
                    </template>
                </jet-form-section>
            </template>
        </div>
    </app-layout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import JetActionMessage from '@/Jetstream/ActionMessage.vue'
import JetButton from '@/Jetstream/Button.vue'
import JetFormSection from '@/Jetstream/FormSection.vue'
import JetInput from '@/Jetstream/Input.vue'
import JetInputError from '@/Jetstream/InputError.vue'
import JetLabel from '@/Jetstream/Label.vue'
import JetSectionBorder from '@/Jetstream/SectionBorder.vue'
import Checkbox from "@/Jetstream/Checkbox.vue";
import AttachmentBox from "@/Components/AttachmentBox.vue";
import {computed, reactive, ref, watch} from 'vue';
import {Head, Link, router} from '@inertiajs/vue3'
import {debounce} from 'lodash/function';
import {isNumber} from 'lodash/lang';

// Props
const props = defineProps({
    item: Object,
    static_departments: Array,
    is_admin: Boolean,
    errors: Object,
});

// Data
const projectKeyword = ref(props.item.project ? (props.item.project.year + '-' + props.item.project.number) : "");
const projectSearchResult = ref([]);
const keywordError = ref('');
const selectedProject = ref(props.item.project);
const form = reactive({
    _method: props.item.id ? 'PUT' : 'POST',
    title: props.item.title ?? "",
    recipient: props.item.recipient ?? "",
    department_id: props.item.department_id ?? "",
    amount: props.item.amount ?? 1,
    project_id: props.item.project_id,
    attachment: null,
    approved_attachment: null,
    is_non_project: props.item.id ? !props.item.project_id : false,
    tag: props.item.tag ?? "",
});
const hasFilledClosureForm = computed(() =>
    selectedProject.value && !(
        selectedProject.value.objectives.filter(o => o.result && o.result.length > 0).length !== selectedProject.value.objectives.length
        || selectedProject.value.expense.filter(e => e.paid || (e.paid === 0)).length !== selectedProject.value.expense.length
    ));

// Methods
const submit = function () {
    if (!form.attachment && !props.item.id) {
        props.errors.attachment = "กรุณาอัปโหลดร่างเอกสาร";
    } else if (form.attachment && !(form.attachment.name.endsWith('.pdf') || form.attachment.name.endsWith('.docx'))) {
        props.errors.attachment = "ไม่รองรับประเภทไฟล์นี้";
    } else if (!selectedProject.value && !form.is_non_project) {
        props.errors.project_id = "กรุณาเลือกโครงการ";
    } else {
        form.project_id = selectedProject.value ? selectedProject.value.id : null;
        if (selectedProject.value) {
            form.objectives = selectedProject.value.objectives;
            form.expense = selectedProject.value.expense;
        }
        if (form.tag) {
            form.amount = 1;
            if (form.tag === 'summary') {
                if (form.objectives.filter(o => o.result && o.result.length > 0).length !== form.objectives.length) {
                    props.errors.objectives = "กรุณากรอกผลการประเมินทุกตัวชี้วัด";
                    return;
                } else {
                    props.errors.objectives = "";
                }
                if (form.expense.filter(e => e.paid || (e.paid === 0)).length !== form.expense.length) {
                    props.errors.expense = "กรุณากรอกยอดเงินที่จ่ายจริงให้ครบทุกรายการ หากไม่ได้ใช้จ่าย ให้ใส่ 0";
                    return;
                } else {
                    props.errors.expense = "";
                }
            }
        }
        router.post(props.item.id
                ? route('documents.update', {document: props.item.id})
                : route('documents.store'),
            form
        );
    }
};

const searchProject = debounce(function (keyword) {
    keywordError.value = 'กำลังค้นหา...';
    axios.get(route('projects.search', {
        keyword: keyword
    })).then((response) => {
        keywordError.value = '';
        projectSearchResult.value = response.data;
    }).catch((error) => {
        keywordError.value = 'Error! Could not reach the API. ' + error;
    })
}, 500)

const selectProject = function (item) {
    selectedProject.value = item;
    const isProjectNameLatin = /[\u0000-\u007F]/.test(selectedProject.value.name.charAt(0));
    if (!form.title) {
        if (form.tag) {
            if (form.tag === 'approval') {
                form.title = `ขออนุมัติดำเนินโครงการ${isProjectNameLatin ? ' ' : ''}${selectedProject.value.name}`;
            } else if (form.tag === 'summary') {
                form.title = `รายงานผลการดำเนินงานโครงการ${isProjectNameLatin ? ' ' : ''}${selectedProject.value.name}`;
            }
            form.recipient = 'รองคณบดีฝ่ายกิจการนิสิต';
        }
        form.department_id = selectedProject.value.department_id;
    }
}


// Watch
watch(projectKeyword, function (newValue) {
    keywordError.value = "กำลังพิมพ์...";
    if (newValue === "") {
        keywordError.value = "";
        projectSearchResult.value = [];
        selectedProject.value = null;
        return;
    } else if (newValue.length < 2 && !isNumber(newValue)) {
        keywordError.value = 'กรุณากรอกคำค้นหาอย่างน้อย 2 ตัวอักษร';
        return;
    }
    searchProject(newValue);
});
</script>
