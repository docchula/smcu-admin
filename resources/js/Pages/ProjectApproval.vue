<template>
    <AppLayout>
        <template #header>
            <Link :href="route('projects.approvalIndex')" class="mb-4 flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                <p>
                    โครงการทั้งหมด
                </p>
            </Link>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                อนุมัติรายงานผลโครงการที่ {{ item.year }}-{{ item.number }}
                <span
                    class="inline-block ml-2 px-2 py-1 text-xs font-semibold text-orange-500 bg-orange-100 rounded-full">สำหรับรอง/ผู้ช่วยคณบดี</span>
            </h2>
            <p class="mt-2 text-gray-500">
                โครงการที่จะบันทึกเป็นส่วนหนึ่งของ Activity Transcript ต้องผ่านการรับรองรายชื่อนิสิตผู้เกี่ยวข้อง
                โดยนิสิตผู้รับผิดชอบและผู้ปฏิบัติงานทุกคน<b>ภายใน 60 วัน นับจากสิ้นสุดกิจกรรม</b>
            </p>
        </template>
        <div class="max-w-7xl mx-auto pt-4 pb-10 sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="flex px-4 py-5 sm:px-6">
                    <h3 class="flex-auto text-lg leading-6 font-medium text-gray-900">
                        ข้อมูลโครงการ
                    </h3>
                    <div class="flex-auto text-right text-sm">
                        <a :href="route('projects.edit', {project: item.id})" target="_blank" class="ml-4 text-orange-400 hover:text-orange-600">
                            <PencilIcon class="inline-block h-4 w-4"/>
                        </a>
                        <a :href="route('projects.show', {project: item.id})" target="_blank" class="ml-4 text-orange-500 hover:text-orange-700">
                            รายละเอียดโครงการ
                        </a>
                        <a v-if="item.summary_document" class="ml-4 text-yellow-500 hover:text-yellow-700"
                           :href="route('documents.show', {document: item.summary_document.id})" target="_blank">
                            รายงานผล
                        </a>
                    </div>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="grid sm:grid-cols-6 gap-4">
                            <div class="col-span-4 space-y-2">
                                <Label value="โครงการ"/>
                                <Link :href="route('projects.show', {project: item.id})">
                                    {{ item.name }}
                                </Link>
                            </div>
                            <div class="col-span-2 space-y-2">
                                <Label value="สถานะ"/>
                                <ClosureStatusText class="font-bold" :closure_status="item.closure_status"/>
                            </div>
                            <div class="col-span-4 space-y-2">
                                <Label value="หน่วยงาน (สพจ.)"/>
                                {{ item.department.name }}
                            </div>
                            <div class="col-span-2 space-y-2">
                                <Label value="ระยะเวลากิจกรรม"/>
                                {{ item.duration ?? '?' }} ชั่วโมง
                            </div>
                            <div class="col-span-4 space-y-2">
                                <Label value="อาจารย์ที่ปรึกษา"/>
                                {{ item.advisor }}
                                <p class="text-xs text-gray-500">
                                    มีหน้าที่ประเมินหลักฐานการเข้าร่วมกิจกรรมของนิสิต (เฉพาะนิสิตหลักสูตรพ.บ. 2567)
                                </p>
                            </div>
                            <div class="col-span-2 space-y-2">
                                <Label value="จำนวนผู้เข้าร่วม"/>
                                {{ item.estimated_attendees ?? '?' }} คน
                                <InputError v-if="!item.estimated_attendees" message="กรุณาแก้ไข"/>
                                <p v-else class="text-xs text-gray-500 col-span-6">
                                    อาจเป็นนิสิตแพทย์หรือบุคคลอื่นก็ได้
                                </p>
                            </div>
                            <div class="col-span-3 space-y-2">
                                <Label value="วันที่จัดกิจกรรม"/>
                                {{ (item.period_start === item.period_end) ? item.period_start : (item.period_start + ' - ' + item.period_end) }}
                                <p class="text-xs text-gray-500">
                                    ใน Transcript อาจปรากฏเฉพาะเดือนและปี
                                </p>
                            </div>
                            <div class="col-span-3 space-y-2">
                                <Label value="วันที่ส่งรายงานผล"/>
                                {{ item.closure_submitted_at }}
                                <p class="text-xs text-gray-500">
                                    นิสิตผู้รับผิดชอบต้องส่งรายงานผลภายใน 30 วัน นับจากสิ้นสุดกิจกรรม
                                </p>
                            </div>
                            <div v-if="item.closure_status < 10 && item.closure_status >= 0 && item.closure_approved_message"
                                 class="col-span-6 space-y-2">
                                <label class="block font-medium text-sm text-blue-500">
                                    หมายเหตุการพิจารณาอนุมัติรายงานผล
                                </label>
                                {{ item.closure_approved_message }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <ProjectClosureStatus v-if="item.closure_status <= 5" :participants="item.participants"/>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        นิสิตผู้เกี่ยวข้อง
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <div class="py-4 sm:px-6">
                        <table class="w-full divide-y divide-gray-200">
                            <thead>
                            <tr>
                                <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    ชื่อ
                                </th>
                                <th scope="col"
                                    class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider hidden sm:block">
                                    เลขประจำตัว
                                </th>
                                <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    ตำแหน่ง
                                </th>
                                <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    รับรอง/ไม่รับรองรายชื่อ
                                </th>
                                <th v-if="showCheckbox" @click="toggleAll" :class="{ 'cursor-pointer': item.closure_status < 10 }"
                                    scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    อนุมัติ
                                </th>
                                <th v-if="item.closure_status < 10" scope="col"
                                    class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider"></th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <template v-for="(name, type) in PROJECT_PARTICIPANT_ROLES">
                                <tr class="text-sm bg-gray-300 text-gray-800">
                                    <td class="px-2 py-0.5">{{ name }}</td>
                                    <td class="px-2 py-0.5 hidden sm:block"></td>
                                    <td class="px-2 py-0.5"></td>
                                    <td colspan="2" class="px-2 py-0.5 text-right">
                                        <span v-if="participantsGrouped[type]">
                                            ตรวจสอบแล้ว
                                            {{ participantsGrouped[type].filter(e => e.verify_status).length }}
                                            จาก {{ participantsGrouped[type].length }} คน
                                        </span>
                                    </td>
                                    <td v-if="item.closure_status < 10">
                                        <PlusIcon class="inline-block ml-1 h-5 text-green-400 cursor-pointer"
                                                  @click="showStudentIdDialog = type"/>
                                    </td>
                                </tr>
                                <tr v-for="(e, i) in participantsGrouped[type]">
                                    <td class="px-2 py-1">
                                        <span class="text-gray-400">{{ i + 1 }}.</span>&ensp;
                                        {{ e.user.name }}
                                        <p class="text-xs text-gray-400 sm:hidden">{{ e.user.student_id }}</p>
                                    </td>
                                    <td class="px-2 py-1 hidden sm:block">{{ e.user.student_id }}</td>
                                    <td class="px-2 py-1" :class="{'text-gray-300': !e.title}">{{ e.title ?? '-' }}</td>
                                    <td class="px-2 py-1 text-center">
                                        <span v-if="e.verify_status === 1">รับรอง</span>
                                        <span v-else-if="e.verify_status === -1" class="text-red-500">ไม่รับรอง</span>
                                        <span v-else class="text-sm">ยังไม่ตรวจสอบ</span>
                                    </td>
                                    <td v-if="showCheckbox" class="px-2 py-1 text-center">
                                        <template v-if="item.closure_status >= 10 && !forceShowApproveBox">
                                            <span v-if="e.approve_status === 1" class="text-green-500">อนุมัติ</span>
                                            <span v-else class="text-red-500">ไม่อนุมัติ</span>
                                        </template>
                                        <Checkbox v-else :checked="selectedParticipants.includes(e.id)" @click="selectParticipant(e.id)"
                                                  class="text-blue-600 focus:border-blue-300 focus:ring-blue-200"/>
                                    </td>
                                    <td v-if="item.closure_status < 10" class="px-1 py-1 text-center text-gray-400">
                                        <PencilIcon @click="showParticipantEditDialog=e" class="cursor-pointer w-4"/>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                        <div class="mt-4 px-4 sm:px-0 text-gray-500">
                            <h6 class="font-semibold">เงื่อนไขจำนวนนิสิตผู้เกี่ยวข้อง เพื่อบันทึกใน Activity Transcript</h6>
                            <p v-if="!organizerCountCompliance || !staffCountCompliance"
                               class="mt-2 mb-1 text-orange-500 border-orange-500 border p-2 w-full rounded-md">
                                <span class="font-semibold">ไม่ตรงตามเงื่อนไข</span>
                                เมื่อยืนยันและส่งเอกสารแล้ว ให้ติดต่อชี้แจงกับผู้ช่วยคณบดี/รองคณบดีที่ได้รับมอบหมาย
                            </p>
                            <ul class="mt-1 space-y-1 text-sm text-gray-500 list-inside list-disc">
                                <li class="font-bold"
                                    :class="{'text-green-600': organizerCountCompliance, 'text-red-500': !organizerCountCompliance}">
                                    ผู้รับผิดชอบ พึงมีจำนวนไม่เกินร้อยละ 20 ของจำนวนผู้ปฏิบัติงาน ยกเว้นโครงการที่ไม่มีนิสิตเป็นผู้ปฏิบัติงาน
                                </li>
                                <li class="font-bold" :class="{'text-green-600': staffCountCompliance, 'text-red-500': !staffCountCompliance}">
                                    ผู้ปฏิบัติงาน พึงมีจำนวนไม่เกิน 2 ใน 3 ของผู้มีส่วนร่วมในกิจกรรมทั้งหมด ทั้งผู้รับผิดชอบ ผู้ปฏิบัติงาน
                                    และผู้เข้าร่วม ทั้งนิสิตและบุคคลภายนอก
                                </li>
                                <li>ผู้เข้าร่วม ต้องลงชื่อเข้าร่วมกิจกรรมทุกวัน ทุกครึ่งวัน หรือตามความเหมาะสมต่อลักษณะกิจกรรม
                                    โดยมีหลักฐานว่าเข้าร่วมกิจกรรมไม่น้อยกว่า 2 ใน 3 ของระยะเวลากิจกรรมทั้งหมด
                                    ให้ผู้รับผิดชอบโครงการเก็บรักษาหลักฐานดังกล่าวไว้
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="hasRejectedVerify" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        นิสิตที่ไม่รับรองรายชื่อ
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-4 sm:px-6">
                        <ol class="my-2 ml-4 list-outside list-decimal">
                            <template v-for="participant in item.participants">
                                <li v-if="participant.verify_status === -1">
                                    {{ participant.user.name }}<br/>
                                    <u>เหตุผล</u>&emsp;{{ participant.reject_reason }}<br/>
                                    <u>ไม่รับรองนิสิต</u>&emsp;
                                    <template v-for="p in item.participants">
                                        <span v-if="participant.reject_participants.includes(p.id)" class="mr-3">
                                            {{ p.user.name }}
                                        </span>
                                    </template>
                                </li>
                            </template>
                        </ol>
                    </div>
                </div>
            </div>
            <div v-if="item.closure_status <= -1 || item.closure_status >= 10" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        ผลการอนุมัติรายงานผลโครงการ
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-4 sm:px-6">
                        <p class="font-bold text-lg">
                            <ClosureStatusText :closure_status="item.closure_status"/>
                        </p>
                        <p v-if="item.closure_approved_message">
                            <span class="text-gray-600 underline">เหตุผล</span>&ensp;{{ item.closure_approved_message }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            บันทึกเมื่อ {{ item.closure_approved_at }} โดย {{ item.closure_approved_by_user?.name }}
                        </p>
                    </div>
                </div>
            </div>
            <div v-if="!(item.closure_status >= 1 && item.closure_status <= 5) && item.closure_status !== 0" class="relative flex gap-x-3">
                <div class="flex h-6 items-center">
                    <input id="force_approve" v-model="forceShowApproveBox"
                           type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"/>
                </div>
                <div class="text-sm leading-6">
                    <label for="force_approve" class="font-medium text-gray-700">แก้ไขผลการอนุมัติ/ไม่อนุมัติ</label>
                </div>
            </div>
            <div v-if="(item.closure_status >= 1 && item.closure_status <= 5) || forceShowApproveBox"
                 class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        อนุมัติรายงานผลโครงการ และรายชื่อนิสิตผู้เกี่ยวข้องหรือไม่
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-4 sm:px-6">
                        <ul class="grid w-full gap-6 md:grid-cols-5">
                            <li class="md:col-span-3">
                                <input type="radio" id="approve-radio-yes" name="approve-radio" v-model="form.approve" value="yes" class="hidden peer"
                                       required/>
                                <label for="approve-radio-yes"
                                       class="inline-flex items-center justify-between w-full p-5 text-blue-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-2 peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100">
                                    <div class="block">
                                        <div class="w-full text-lg font-semibold">อนุมัติ</div>
                                        <div class="w-full">รายงานผลโครงการ และรายชื่อนิสิตผู้เกี่ยวข้อง {{ selectedParticipants.length }} คน</div>
                                    </div>
                                    <CheckIcon class="w-8 h-8 ms-3"/>
                                </label>
                            </li>
                            <li class="md:col-span-2">
                                <input type="radio" id="approve-radio-no" name="approve-radio" v-model="form.approve" value="no" class="hidden peer">
                                <label for="approve-radio-no"
                                       class="inline-flex items-center justify-between w-full p-5 text-red-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-2 peer-checked:border-red-600 peer-checked:text-red-600 hover:text-gray-600 hover:bg-gray-100">
                                    <div class="block">
                                        <div class="w-full text-lg font-semibold">ไม่อนุมัติ</div>
                                        <div class="w-full">ไม่บันทึกโครงการนี้ใน Activity Transcript</div>
                                    </div>
                                    <XMarkIcon class="w-8 h-8 ms-3"/>
                                </label>
                            </li>
                        </ul>
                        <div v-if="form.approve === 'yes'" class="mt-4">
                            <p v-if="selectedParticipants.length <= 0" class="my-4 text-blue-500">กรุณากดเลือกนิสิตที่ต้องการอนุมัติ</p>
                            <template v-else-if="selectedParticipants.length !== item.participants.length">
                                <Label value="นิสิตที่ไม่อนุมัติ"/>
                                <ol class="my-2 list-inside list-decimal">
                                    <template v-for="participant in item.participants">
                                        <li v-if="!selectedParticipants.includes(participant.id)">
                                            {{ participant.user.name }}
                                        </li>
                                    </template>
                                </ol>
                            </template>
                        </div>
                        <div v-else-if="form.approve === 'no'">
                            <div class="my-4">
                                <Label for="reason" value="เหตุผล"/>
                                <Input id="reason" type="text" class="mt-1 block w-full" v-model.trim="form.reason" ref="reason" required/>
                                <InputError :message="form.errors.reason" class="mt-2"/>
                            </div>
                            <div class="flex items-start col-span-6">
                                <div class="flex items-center h-5">
                                    <Checkbox id="allow_resubmit" :checked="form.allow_resubmit"
                                              @update:checked="newValue => form.allow_resubmit = newValue"/>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="allow_resubmit" class="font-medium text-gray-700">
                                        ให้ไปแก้ไขและส่งใหม่
                                    </label>
                                    <p class="text-gray-400">ระบบจะขยายกรอบเวลาในการส่งใหม่ออกไปอีก 30 วัน</p>
                                </div>
                            </div>
                        </div>
                        <InputError :message="form.errors.approve_participants" class="mt-2"/>
                    </div>
                    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-right sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                        <Button type="button" :disabled="form.processing || !form.approve" @click="submit">
                            บันทึก
                        </Button>
                    </div>
                </div>
            </div>
            <p class="mt-4 px-2 text-xs">
                <a class="text-blue-500 cursor-pointer" @click="showLogDialog = true">ดูประวัติ</a>
                <a class="ml-4 text-blue-500" :href="route('projects.export-closure', {project: item.id})">Export as JSON</a>
                <a v-if="item.closure_status < 10 && item.closure_status >= 0" @click="showMessageDialog = true"
                   class="ml-4 text-green-500 cursor-pointer">
                    บันทึกหมายเหตุ
                </a>
            </p>
        </div>
        <ClosureLogDialog :show-modal="showLogDialog" :project="item" @close="showLogDialog = false"/>
        <ClosureMessageDialog :show-modal="showMessageDialog" :project="item" @close="showMessageDialog = false"/>
        <ParticipantEditDialog :show-modal="showParticipantEditDialog" @close="showParticipantEditDialog = null"
                               :participant="showParticipantEditDialog"/>
        <StudentIdDialog :show-modal="Boolean(showStudentIdDialog)" :list="item.participants.map(p => p.user?.student_id)"
                         @close="showStudentIdDialog = null" @selected="addParticipant($event)"/>
    </AppLayout>
</template>

<script setup lang="ts">
import {CheckIcon, PencilIcon, PlusIcon, XMarkIcon} from "@heroicons/vue/20/solid";
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Jetstream/InputError.vue';
import Label from '@/Jetstream/Label.vue';
import {computed, ref} from 'vue';
import {Link, useForm} from '@inertiajs/vue3';
import {groupBy} from "lodash";
import {PROJECT_PARTICIPANT_ROLES} from "@/static";
import Checkbox from "@/Jetstream/Checkbox.vue";
import Input from "@/Jetstream/Input.vue";
import Button from "@/Jetstream/Button.vue";
import ProjectClosureStatus from "@/Components/ProjectClosureStatus.vue";
import ClosureLogDialog from "@/Components/ClosureLogDialog.vue";
import ClosureStatusText from "@/Components/ClosureStatusText.vue";
import ClosureMessageDialog from "@/Components/ClosureMessageDialog.vue";
import {Project, ProjectParticipant, User} from '@/types';
import ParticipantEditDialog from '@/Components/ParticipantEditDialog.vue';
import StudentIdDialog from '@/Components/StudentIdDialog.vue';

const props = defineProps<{
    item: Project,
}>();

const form = useForm({
    _method: 'POST',
    approve: '', // "yes" or "no"
    reason: props.item.closure_approved_message ?? '',
    approve_participants: [],
    allow_resubmit: false,
});
const oldSelectedParticipants = props.item.participants.filter(p => p.approve_status === 1).map(p => p.id);
const selectedParticipants = ref(oldSelectedParticipants.length > 0 ? oldSelectedParticipants : props.item.participants.map(e => e.id));
const showLogDialog = ref(false);
const showMessageDialog = ref(false);
const showParticipantEditDialog = ref<ProjectParticipant | null>(null);
const showStudentIdDialog = ref<string | null>(null);
const forceShowApproveBox = ref(false);

// Computed
const participantsGrouped = computed(() => {
    return (props.item.participants.length > 0) ? groupBy(props.item.participants, 'type') : null;
});
const organizerCountCompliance = computed(() => {
    if (!participantsGrouped.value || !participantsGrouped.value['organizer'] || !participantsGrouped.value['staff']) return true;
    return participantsGrouped.value['organizer'].length <= 0.2 * participantsGrouped.value['staff'].length;
});
const staffCountCompliance = computed(() => {
    if (!participantsGrouped.value || !participantsGrouped.value['staff']) return true;
    return participantsGrouped.value['staff'].length <= 0.66667 * ((participantsGrouped.value['attendee'] && participantsGrouped.value['attendee'].length > 0) ? props.item.participants.length : (props.item.participants.length + Number(props.item.estimated_attendees)));
});
const hasRejectedVerify = computed(() => Boolean(props.item.participants.find(e => e.verify_status === -1)));
const showCheckbox = computed(() => ((form.approve !== 'no' || props.item.closure_status >= 10) && props.item.closure_status >= 1) || forceShowApproveBox.value);

const selectParticipant = (id) => {
    if (selectedParticipants.value.includes(id)) {
        selectedParticipants.value = selectedParticipants.value.filter(e => e !== id);
    } else {
        selectedParticipants.value.push(id);
    }
};
const toggleAll = () => {
    if (selectedParticipants.value.length > 0) {
        selectedParticipants.value = [];
    } else {
        selectedParticipants.value = props.item.participants.map(e => e.id);
    }
};
const submit = () => {
    form.approve_participants = selectedParticipants.value;
    form.post(route('projects.approvalSubmit', {project: props.item.id}), {
        preserveState: false,
        preserveScroll: false,
    });
};
const addParticipant = (student: User) => {
    if (!props.item.participants.find((p: ProjectParticipant) => p.user.student_id === student.student_id)) {
        useForm({
            type: showStudentIdDialog.value,
            student_ids: [student.student_id],
        }).post(route('projects.addParticipant', {project: props.item.id}));
        showStudentIdDialog.value = null;
    }
};
</script>
