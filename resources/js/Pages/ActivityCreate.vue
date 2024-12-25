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
                        <Input id="name" type="text" class="mt-1 block w-full" v-model="form.name" required :disabled="view_only"/>
                        <InputError :message="form.errors.name" class="mt-2"/>
                    </div>
                    <div class="col-span-6">
                        <Label for="organization" value="หน่วยงาน"/>
                        <Input id="organization" type="text" class="mt-1 block w-full" v-model="form.organization" required :disabled="view_only"/>
                        <InputError :message="form.errors.organization" class="mt-2"/>
                    </div>
                    <div class="col-span-3 lg:col-span-2">
                        <Label for="period_start" value="วันที่เริ่ม"/>
                        <Input id="period_start" v-model="form.period_start" type="date" pattern="\d{4}-\d{2}-\d{2}" min="2024-01-01"
                               class="mt-1 block w-full" :disabled="view_only"
                        />
                        <InputError v-if="form.errors.period_start" :message="form.errors.period_start" class="mt-2"/>
                        <p v-else-if="form.period_start" class="mt-1 text-xs text-green-500">
                            {{ new Date(form.period_start).toDateString() }}
                        </p>
                    </div>
                    <div class="col-span-3 lg:col-span-2">
                        <Label for="period_end" value="วันที่สิ้นสุด"/>
                        <Input id="period_end" v-model="form.period_end" type="date" pattern="\d{4}-\d{2}-\d{2}" min="2024-01-01"
                               class="mt-1 block w-full" :disabled="view_only"
                        />
                        <InputError v-if="form.errors.period_end" :message="form.errors.period_end" class="mt-2"/>
                        <p v-else-if="form.period_end" class="mt-1 text-xs text-green-500">
                            {{ new Date(form.period_end).toDateString() }}
                        </p>
                    </div>
                    <div class="col-span-3 lg:col-span-1">
                        <Label for="duration" value="ระยะเวลา (ชั่วโมง)"/>
                        <Input id="duration" v-model="form.duration" type="number" min="1" max="999" step="0.5" class="mt-1 block w-full"
                               :disabled="view_only"
                               required/>
                        <InputError :message="form.errors.duration" class="mt-2"/>
                    </div>
                    <div class="col-span-3 lg:col-span-1">
                        <label for="role" class="block text-sm font-medium text-gray-700">บทบาท</label>
                        <select id="role" v-model="form.role" required :disabled="view_only"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option v-for="(name, value) in PROJECT_PARTICIPANT_ROLES"
                                    v-bind:key="value" :value="value">
                                {{ name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.role" class="mt-2"/>
                    </div>
                </template>
            </FormSection>
            <SectionBorder/>
            <FormSection @submitted="submit">
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
                                      v-model.trim="form.description"></textarea>
                        </div>
                        <InputError :message="form.errors.description" class="mt-1"/>
                    </div>
                </template>
            </FormSection>
            <SectionBorder/>
            <FormSection @submitted="submit">
                <template #title>นิสิตผู้เกี่ยวข้อง</template>
                <template #description>
                    นิสิตทุกคนจะมีบทบาทเดียวกัน กรณีกิจกรรมนี้มีนิสิตเกี่ยวข้องในหลายบทบาท ให้บันทึกกิจกรรมแยกกัน
                </template>
                <template #form>
                    <table v-if="participants.length > 0" class="col-span-6 divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                ชื่อ
                            </th>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                เลขประจำตัวนิสิต
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
                        <InputError :message="form.errors.participants" class="mt-2"/>
                    </div>
                </template>
            </FormSection>
            <SectionBorder/>
            <FormSection @submitted="submit">
                <template #title>ไฟล์เอกสาร</template>
                <template #description>กรุณาแนบไฟล์เอกสารทั้งฉบับ รวมเป็นไฟล์เดียว ในรูปแบบ pdf หรือ docx ขนาดไม่เกิน 15 MB</template>
                <template #form>
                    <AttachmentBox class="col-span-6" v-model="form.attachment" :disabled="view_only"
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
        </div>
        <StudentIdDialog :show-modal="showStudentIdDialog" :list="participants.map(x => x.student_id)" @close="showStudentIdDialog = false"
                         @selected="addParticipant($event)"/>
    </app-layout>
</template>

<script setup lang="ts">
import AppLayout from '../Layouts/AppLayout.vue';
import StudentIdDialog from '../Components/StudentIdDialog.vue';
import AttachmentBox from '../Components/AttachmentBox.vue';
import ActionMessage from '../Jetstream/ActionMessage.vue';
import Button from '../Jetstream/Button.vue';
import FormSection from '../Jetstream/FormSection.vue';
import Input from '../Jetstream/Input.vue';
import InputError from '../Jetstream/InputError.vue';
import Label from '../Jetstream/Label.vue';
import SectionBorder from '../Jetstream/SectionBorder.vue';
import {PROJECT_PARTICIPANT_ROLES} from '@/static';
import {Activity} from '@/types';
import {Link, useForm} from '@inertiajs/vue3';
import {ref} from 'vue';

const props = defineProps<{ item: Activity, view_only: boolean }>();
const form = useForm({
    _method: props.item.id ? 'PUT' : 'POST',
    name: props.item.name ?? "",
    organization: props.item.organization ?? "",
    period_start: props.item.period_start ? props.item.period_start : "",
    period_end: props.item.period_end ? props.item.period_end : "",
    duration: props.item.duration ?? "",
    role: props.item.role ?? "",
    description: props.item.description ?? "",
    attachment: null,
    participants: [],
});
const participants = ref<Array<{ name: string; student_id: string; nickname: string; id?: string; }>>(props.item.participants ?? []);
const showStudentIdDialog = ref(false);
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
        ? route('activities.update', {project: props.item.id})
        : route('activities.store'),
    );
};
</script>
