<template>
    <app-layout>
        <template #header>
            <Link :href="route('activities.index')"
                  class="mb-4 flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                <p>ประวัติกิจกรรม</p>
            </Link>
            <h2 v-if="item.id" class="font-semibold text-xl text-gray-800 leading-tight">
                {{ view_only ? 'ดู' : 'แก้ไข' }}ประวัติกิจกรรมเลขที่ {{ item.id }}
            </h2>
            <h2 v-else class="font-semibold text-xl text-gray-800 leading-tight">บันทึกประวัติกิจกรรมใหม่</h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <p v-if="form.hasErrors" class="bg-red-500 text-white p-3 w-full mb-6 rounded-md shadow-md transition">ข้อมูลที่กรอกไม่ถูกต้องครบถ้วน
                กรุณาตรวจสอบอีกครั้ง</p>
            <FormSection @submitted="submit">
                <template #title>ข้อมูลพื้นฐาน</template>
                <template #description>ข้อมูลเหล่านี้จะแสดงผลใน Activity Transcript
                </template>
                <template #form>
                    <div class="col-span-6">
                        <Label for="name" value="ชื่อโครงการ"/>
                        <Input id="name" type="text" class="mt-1 block w-full" v-model="form.name" required :disabled="view_only"
                               :class="{'border-0': view_only}"/>
                        <InputError :message="form.errors.name" class="mt-2"/>
                    </div>
                    <div class="col-span-6">
                        <Label for="organization" value="หน่วยงาน"/>
                        <Input id="organization" type="text" class="mt-1 block w-full" v-model="form.organization" required :disabled="view_only"
                               :class="{'border-0': view_only}"/>
                        <InputError :message="form.errors.organization" class="mt-2"/>
                    </div>
                    <div class="col-span-2">
                        <Label for="period_start" value="วันที่เริ่ม (ค.ศ.)"/>
                        <Input id="period_start" v-model="form.period_start" type="date" pattern="\d{4}-\d{2}-\d{2}" min="2024-01-01"
                               class="mt-1 block w-full" :disabled="view_only" :class="{'border-0': view_only}"
                        />
                        <InputError v-if="form.errors.period_start" :message="form.errors.period_start" class="mt-2"/>
                        <p v-else-if="form.period_start" class="mt-1 text-xs text-green-500">
                            {{ new Date(form.period_start).toDateString() }}
                        </p>
                    </div>
                    <div class="col-span-2">
                        <Label for="period_end" value="วันที่สิ้นสุด"/>
                        <Input id="period_end" v-model="form.period_end" type="date" pattern="\d{4}-\d{2}-\d{2}" min="2024-01-01"
                               class="mt-1 block w-full" :disabled="view_only" :class="{'border-0': view_only}"
                        />
                        <InputError v-if="form.errors.period_end" :message="form.errors.period_end" class="mt-2"/>
                        <p v-else-if="form.period_end" class="mt-1 text-xs text-green-500">
                            {{ new Date(form.period_end).toDateString() }}
                        </p>
                    </div>
                    <div class="col-span-2">
                        <Label for="duration" value="ระยะเวลา (ชั่วโมง)"/>
                        <Input id="duration" v-model="form.duration" type="number" min="1" max="999" step="0.5" class="mt-1 block w-full"
                               :disabled="view_only" :class="{'border-0': view_only}" required/>
                        <InputError :message="form.errors.duration" class="mt-2"/>
                    </div>
                </template>
            </FormSection>
            <template v-if="!view_only || form.description">
                <SectionBorder/>
                <FormSection>
                    <template #title>รายละเอียดเพิ่มเติม</template>
                    <template #description>
                        ไม่ปรากฏใน Activity Transcript
                    </template>
                    <template #form>
                        <div class="col-span-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">หมายเหตุ</label>
                            <div class="mt-1">
                                <textarea id="description" rows="3" :disabled="view_only"
                                          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md"
                                          :class="{'border-0': view_only}" v-model.trim="form.description"></textarea>
                            </div>
                            <InputError :message="form.errors.description" class="mt-1"/>
                        </div>
                    </template>
                </FormSection>
            </template>
            <SectionBorder/>
            <FormSection @submitted="submit">
                <template #title>นิสิตผู้เกี่ยวข้อง</template>
                <template #description v-if="!item.id">
                    สามารถนำเข้ารายชื่อจากไฟล์ Excel ได้หลังจากที่บันทึกข้อมูลครั้งแรกแล้ว
                </template>
                <template #form>
                    <table v-if="participants.length > 0" class="col-span-6 divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                ชื่อ
                            </th>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                เลขประจำตัว
                            </th>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                บทบาท
                            </th>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                ตำแหน่ง (ถ้ามี)
                            </th>
                            <th scope="col" class="relative px-1 pb-2"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(member, index) in participants" :key="member.id">
                            <td class="px-3 py-2 whitespace-nowrap">
                                {{ member.name }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                {{ member.student_id }}
                            </td>
                            <td class="px-3 py-1 whitespace-nowrap">
                                <select v-model="member.type" required :disabled="view_only"
                                        :class="{'border-red-500': !member.type, 'border-0': view_only}"
                                        class="mt-1 block w-full py-1 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">-</option>
                                    <option v-for="(name, value, i) in PROJECT_PARTICIPANT_ROLES"
                                            v-bind:key="value" :value="value">
                                        {{ i + 1 }}. {{ name }}
                                    </option>
                                </select>
                            </td>
                            <td class="px-3 py-1 whitespace-nowrap">
                                <Input type="text" class="block w-full h-8" v-model="member.title" required :disabled="view_only"
                                       :class="{'border-0': view_only}"/>
                            </td>
                            <td class="px-1 py-2 whitespace-nowrap text-right text-sm font-medium">
                                <svg v-if="!view_only" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 cursor-pointer"
                                     viewBox="0 0 20 20"
                                     fill="currentColor" @click="participants.splice(index, 1)">
                                    <path fill-rule="evenodd"
                                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/><!-- X -->
                                </svg>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p v-else-if="view_only" class="mb-2">ไม่มีนิสิต</p>
                    <div v-if="!view_only" class="col-span-6">
                        <p v-if="participants.length === 0" class="mb-2 text-red-800">กรุณาเพิ่มนิสิตผู้เกี่ยวข้อง</p>
                        <Button class="bg-purple-500 hover:bg-purple-600 focus:border-purple-900"
                                v-if="!view_only" :disabled="form.processing" type="button" @click="showStudentIdDialog = true">
                            เพิ่มนิสิต
                        </Button>
                        <a @click="showImportParticipantDialog = true" v-if="item.id"
                           class="text-sm ml-4 inline-block cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                            <ArrowUpTrayIcon class="h-5 w-5 mr-1 inline"/>
                            <span>นำเข้าด้วยไฟล์ Excel</span>
                        </a>
                        <InputError :message="form.errors.participants" class="mt-2"/>
                    </div>
                </template>
            </FormSection>
            <template v-if="!view_only || item.attachment_path">
                <SectionBorder/>
                <FormSection @submitted="submit">
                    <template #title>เอกสารแนบ</template>
                    <template #description v-if="!item.attachment_path">กรุณาแนบไฟล์เอกสารทั้งฉบับ รวมเป็นไฟล์เดียว ในรูปแบบ pdf หรือ docx ขนาดไม่เกิน
                        15 MB (ไม่จำเป็น)
                    </template>
                    <template #form>
                        <a v-if="view_only && item.attachment_path" :href="route('activities.download', {activity: item.id})" target="_blank"
                           class="block items-center px-4 py-2 mb-2 bg-blue-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                            ดูเอกสาร
                        </a>
                        <AttachmentBox v-else class="col-span-6" v-model="form.attachment" :disabled="view_only"
                                       accept="application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword"
                                       description="PDF or DOCX up to 15 MB"/>
                        <InputError :message="form.errors.attachment" class="col-span-6"/>
                    </template>
                    <template #actions v-if="!view_only">
                        <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                            Saved.
                        </ActionMessage>

                        <Button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing && !form.wasSuccessful">
                            Save
                        </Button>
                    </template>
                </FormSection>
            </template>
            <p class="mt-2 text-xs text-gray-500 text-right">
                สร้างเมื่อ {{ item.created_at }}
                <span v-if="item.updated_at && (item.created_at !== item.updated_at)">
                &emsp;แก้ไขเมื่อ {{ item.updated_at }}
            </span>
            </p>
        </div>
        <Link :href="route('activities.edit', {activity: item.id})" v-if="view_only && can_edit">
            <button
                class="p-0 w-16 h-16 bg-purple-600 rounded-full hover:bg-purple-700 active:shadow-lg mouse shadow transition ease-in duration-200 focus:outline-none fixed bottom-6 right-6">
                <PencilIcon class="w-6 h-6 inline-block text-white"/>
            </button>
        </Link>
        <ImportParticipantDialog :show-modal="showImportParticipantDialog" :activity="item" @close="showImportParticipantDialog = false"/>
        <StudentIdDialog :show-modal="showStudentIdDialog" :list="participants.map(x => x.student_id)" @close="showStudentIdDialog = false"
                         @selected="addParticipant($event)"/>
    </app-layout>
</template>

<script setup lang="ts">
import AppLayout from '../Layouts/AppLayout.vue';
import StudentIdDialog from '../Components/StudentIdDialog.vue';
import AttachmentBox from '../Components/AttachmentBox.vue';
import ImportParticipantDialog from '../Components/ImportParticipantDialog.vue';
import ActionMessage from '../Jetstream/ActionMessage.vue';
import Button from '../Jetstream/Button.vue';
import FormSection from '../Jetstream/FormSection.vue';
import Input from '../Jetstream/Input.vue';
import InputError from '../Jetstream/InputError.vue';
import Label from '../Jetstream/Label.vue';
import SectionBorder from '../Jetstream/SectionBorder.vue';
import {PROJECT_PARTICIPANT_ROLES} from '@/static';
import {Activity, ActivityParticipant} from '@/types';
import {Link, useForm} from '@inertiajs/vue3';
import {ref} from 'vue';
import {ArrowUpTrayIcon, PencilIcon} from '@heroicons/vue/20/solid';

const props = defineProps<{ item: Activity, view_only: boolean, participants: ActivityParticipant[], can_edit: boolean }>();
const form = useForm({
    _method: props.item.id ? 'PUT' : 'POST',
    name: props.item.name ?? "",
    organization: props.item.organization ?? "",
    period_start: props.item.period_start ? props.item.period_start : "",
    period_end: props.item.period_end ? props.item.period_end : "",
    duration: props.item.duration ?? "",
    description: props.item.description ?? "",
    attachment: null,
    participants: [],
});
const participants = ref<ActivityParticipant[]>(props.participants ?? []);
const showStudentIdDialog = ref(false);
const showImportParticipantDialog = ref(false);
const addParticipant = (student) => {
    if (!participants.value.find(s => s.student_id === student.student_id)) {
        participants.value.push(student);
    }
};
const submit = () => {
    form.transform(data => ({
        ...data,
        participants: participants.value,
    })).post(props.item.id
        ? route('activities.update', {activity: props.item.id})
        : route('activities.store'),
    );
};
</script>
