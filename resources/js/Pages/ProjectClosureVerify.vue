<template>
    <app-layout>
        <template #header>
            <Link :href="route('projects.show', {project: item.id})"
                  class="mb-4 flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                <p>
                    {{ item.name }}
                </p>
            </Link>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">รับรองรายชื่อนิสิตผู้เกี่ยวข้อง</h2>
            <p class="mt-2 text-gray-500">
                โครงการที่จะบันทึกเป็นส่วนหนึ่งของ Activity Transcript ต้องผ่านการรับรองรายชื่อนิสิตผู้เกี่ยวข้อง
                โดยนิสิตผู้รับผิดชอบและผู้ปฏิบัติงานทุกคน<b>ภายใน 60 วัน นับจากสิ้นสุดกิจกรรม</b>
            </p>
        </template>
        <div class="max-w-7xl mx-auto pt-4 pb-10 sm:px-6 lg:px-8">
            <div v-if="!my_participant" class="p-4 my-4 rounded bg-orange-100 border border-orange-600 text-orange-600">
                คุณไม่ใช่ผู้มีส่วนร่วมในโครงการนี้
            </div>
            <div v-else-if="isSubmitted" class="p-4 my-4 rounded bg-blue-100 border border-blue-600 text-blue-600">
                <p class="mb-1 font-bold">คุณบันทึกการตรวจสอบข้อมูลรายชื่อนิสิตผู้เกี่ยวข้องแล้ว</p>
                เมื่อรายชื่อนิสิตผู้เกี่ยวข้องผ่านการรับรองจากนิสิตผู้รับผิดชอบและผู้ปฏิบัติงานในโครงการทุกคนตามเงื่อนไข
                จะเข้าสู่ขั้นตอนการพิจารณาโดยรอง/ผู้ช่วยคณบดีฝ่ายกิจการนิสิตต่อไป
            </div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        ข้อมูลพื้นฐาน
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div class="col-span-3 space-y-2">
                                <jet-label value="โครงการ"/>
                                {{ item.name }}
                            </div>
                            <div class="col-span-2 space-y-2">
                                <jet-label value="วันที่จัดกิจกรรม"/>
                                {{ (item.period_start === item.period_end) ? item.period_start : (item.period_start + ' - ' + item.period_end) }}
                                <p class="text-xs text-gray-500">
                                    ใน Transcript อาจปรากฏเฉพาะเดือนและปี
                                </p>
                            </div>
                            <div class="col-span-1 space-y-2">
                                <jet-label value="ระยะเวลา"/>
                                {{ item.duration ?? '?' }} ชั่วโมง
                                <jet-input-error v-if="!item.duration" message="กรุณาแก้ไข"/>
                                <p v-else class="text-xs text-gray-500">
                                    เฉพาะเวลากิจกรรมจริง
                                </p>
                            </div>
                            <div class="col-span-3 space-y-2">
                                <jet-label value="หน่วยงาน (สพจ.)"/>
                                {{ item.department.name }}
                            </div>
                            <div class="col-span-2 space-y-2">
                                <jet-label value="อาจารย์ที่ปรึกษา"/>
                                {{ item.advisor }}
                                <p class="text-xs text-gray-500">
                                    อาจารย์ที่ปรึกษามีหน้าที่ประเมินหลักฐานการเข้าร่วมกิจกรรมของนิสิต (เฉพาะนิสิตหลักสูตรพ.บ. 2567)
                                </p>
                            </div>
                            <div class="col-span-1 space-y-2">
                                <jet-label value="ประมาณการจำนวนผู้เข้าร่วม"/>
                                {{ item.estimated_attendees ?? '?' }} คน
                                <jet-input-error v-if="!item.estimated_attendees" message="กรุณาแก้ไข"/>
                                <p v-else class="text-xs text-gray-500 col-span-6">
                                    อาจเป็นนิสิตแพทย์หรือบุคคลอื่นก็ได้
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <ProjectClosureStatus v-if="isSubmitted" :participants="item.participants"/>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        นิสิตผู้เกี่ยวข้อง
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-4 sm:px-6">
                        <table class="w-full divide-y divide-gray-200">
                            <thead>
                            <tr>
                                <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    ชื่อ
                                </th>
                                <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    ตำแหน่ง
                                </th>
                                <th v-if="isSubmitted" scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    สถานะ
                                </th>
                                <th v-if="form.approve === 'no'"
                                    scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    ไม่รับรอง
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <template v-for="(name, type) in PROJECT_PARTICIPANT_ROLES">
                                <tr class="text-sm bg-gray-300 text-gray-800">
                                    <td :colspan="form.approve === 'no' ? 3 : (isSubmitted ? 1 : 2)" class="px-2 py-0.5">{{ name }}</td>
                                    <td v-if="isSubmitted" colspan="2" class="px-2 py-0.5 text-right">
                                        <span v-if="participantsGrouped[type]">
                                            ตรวจสอบแล้ว
                                            {{ participantsGrouped[type].filter(e => e.verify_status).length }}
                                            จาก {{ participantsGrouped[type].length }} คน
                                        </span>
                                    </td>
                                </tr>
                                <tr v-for="(e, i) in participantsGrouped[type]">
                                    <td class="px-2 py-1"><span class="text-gray-400">{{ i + 1 }}.</span>&ensp;{{ e.user.name }}</td>
                                    <td class="px-2 py-1" :class="{'text-gray-300': !e.title}">{{ e.title ?? '-' }}</td>
                                    <td v-if="isSubmitted" class="px-2 py-1 text-center">
                                        <CheckCircleIcon v-if="e.verify_status" class="w-5 h-5 inline-block text-green-500"/>
                                        <span v-else class="text-sm">ยังไม่ตรวจสอบ</span>
                                    </td>
                                    <td v-if="form.approve === 'no'" class="px-2 py-1 text-center">
                                        <Checkbox :checked="selectedParticipants.includes(e.id)" @click="selectParticipant(e.id)"
                                                  class="text-red-600 focus:border-red-300 focus:ring-red-200"/>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div v-if="my_participant && !isSubmitted" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        กรุณาตรวจสอบรายชื่อนิสิตผู้เกี่ยวข้องข้างต้น
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-4 sm:px-6">
                        <ul class="ml-4 mb-4 list-outside list-disc">
                            <li>สำหรับนิสิตผู้รับผิดชอบ รับรองรายชื่อผู้รับผิดชอบโครงการ และรายชื่อนิสิตผู้ปฏิบัติงานในฝ่ายที่ตนรับผิดชอบ</li>
                            <li>สำหรับนิสิตผู้ปฏิบัติงาน รับรองรายชื่อหัวหน้าฝ่ายและสมาชิกในฝ่ายของตนเอง</li>
                            <li>หากพบรายชื่อตกหล่น ให้แจ้งนิสิตประธานโครงการเพื่อพิจารณาแก้ไข หรือแจ้งผู้ช่วย/รองคณบดีฝ่ายกิจการนิสิตเพื่อพิจารณา</li>
                        </ul>
                        <ul class="grid w-full gap-6 md:grid-cols-5">
                            <li class="md:col-span-3">
                                <input type="radio" id="approve-radio-yes" name="approve-radio" v-model="form.approve" value="yes" class="hidden peer"
                                       required/>
                                <label for="approve-radio-yes"
                                       class="inline-flex items-center justify-between w-full p-5 text-green-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-2 peer-checked:border-green-600 peer-checked:text-green-600 hover:text-gray-600 hover:bg-gray-100">
                                    <div class="block">
                                        <div class="w-full text-lg font-semibold">รับรอง</div>
                                        <div class="w-full">ข้าพเจ้าปฏิบัติงานในโครงการนี้จริง และรับรองรายชื่อข้างต้น</div>
                                    </div>
                                    <CheckIcon class="w-5 h-5 ms-3"/>
                                </label>
                            </li>
                            <li class="md:col-span-2">
                                <input type="radio" id="approve-radio-no" name="approve-radio" v-model="form.approve" value="no" class="hidden peer">
                                <label for="approve-radio-no"
                                       class="inline-flex items-center justify-between w-full p-5 text-red-500 bg-white border border-gray-200 rounded-lg cursor-pointer peer-checked:border-2 peer-checked:border-red-600 peer-checked:text-red-600 hover:text-gray-600 hover:bg-gray-100">
                                    <div class="block">
                                        <div class="w-full text-lg font-semibold">ไม่รับรอง</div>
                                        <div class="w-full">มีนิสิตที่ไม่ได้ปฏิบัติงานจริงในรายชื่อข้างต้น</div>
                                    </div>
                                    <XMarkIcon class="w-5 h-5 ms-3"/>
                                </label>
                            </li>
                        </ul>
                        <div v-if="form.approve === 'no'" class="mt-4">
                            <p v-if="selectedParticipants.length <= 0" class="my-4 text-red-500">กรุณากดเลือกนิสิตที่ท่านไม่รับรอง
                                และระบุเหตุผลเพิ่มเติม</p>
                            <template v-else>
                                <Label value="นิสิตที่ไม่รับรอง"/>
                                <ol class="my-2 list-inside list-decimal">
                                    <li v-for="id in selectedParticipants">{{ item.participants.find(e => e.id === id)?.user.name }}</li>
                                </ol>
                            </template>
                            <div class="my-4">
                                <Label for="reason" value="เหตุผล"/>
                                <Input id="reason" type="text" class="mt-1 block w-full" v-model.trim="form.reason" ref="reason" required/>
                                <InputError :message="form.errors.reason" class="mt-2"/>
                            </div>
                            <p class="my-4 text-sm">ผู้ช่วย/รองคณบดีฝ่ายกิจการนิสิต จะตรวจสอบข้อมูลเพื่อพิจารณาบันทึกใน Activity Transcript ต่อไป</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-right sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                        <Button type="button" :disabled="form.processing || !form.approve" @click="submit">
                            บันทึก
                        </Button>
                    </div>
                </div>
            </div>
            <p v-if="item.can['update-project']" class="mt-4 px-2 text-sm text-gray-500">
                เมื่อส่งข้อมูลรายงานผลโครงการแล้ว ไม่สามารถแก้ไขได้
                หากข้อมูลพื้นฐานหรือรายชื่อนิสิตผู้เกี่ยวข้องไม่ถูกต้อง ต้อง
                <a class="text-red-500 cursor-pointer" @click="showCancelDialog = true">ยกเลิกการส่งข้อมูล</a>
                ซึ่งจะยกเลิกการรับรองรายชื่อทั้งหมด แล้วจึงส่งใหม่
            </p>
        </div>
        <ClosureCancelDialog :show-modal="showCancelDialog" :project="item" @close="showCancelDialog = false"/>
    </app-layout>
</template>

<script setup>
import {CheckCircleIcon, CheckIcon, XMarkIcon} from "@heroicons/vue/20/solid";
import AppLayout from '@/Layouts/AppLayout.vue'
import JetInputError from '@/Jetstream/InputError.vue'
import InputError from '@/Jetstream/InputError.vue'
import JetLabel from '@/Jetstream/Label.vue'
import Label from '@/Jetstream/Label.vue'
import {computed, ref} from 'vue';
import {Link, useForm} from '@inertiajs/vue3'
import {groupBy} from "lodash";
import {PROJECT_PARTICIPANT_ROLES} from "@/static";
import Checkbox from "@/Jetstream/Checkbox.vue";
import Input from "@/Jetstream/Input.vue";
import Button from "@/Jetstream/Button.vue";
import ClosureCancelDialog from "@/Components/ClosureCancelDialog.vue";
import ProjectClosureStatus from "@/Components/ProjectClosureStatus.vue";

const props = defineProps({
    item: Object,
    my_participant: {type: Object, required: false},
});

const form = useForm({
    _method: 'POST',
    approve: '', // "yes" or "no"
    reason: '',
    reason_participants: [],
});
const selectedParticipants = ref([]);
const showCancelDialog = ref(false);

// Computed
const participantsGrouped = computed(() => {
    return (props.item.participants.length > 0) ? groupBy(props.item.participants, 'type') : null;
});
const isSubmitted = computed(() => {
    return props.my_participant && props.my_participant.verify_status;
});

const selectParticipant = (id) => {
    if (selectedParticipants.value.includes(id)) {
        selectedParticipants.value = selectedParticipants.value.filter(e => e !== id);
    } else {
        selectedParticipants.value.push(id);
    }
};
const submit = () => {
    form.reason_participants = selectedParticipants.value;
    form.post(route('projects.closureVerifySubmit', {project: props.item.id}));
};
</script>
