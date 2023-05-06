<template>
    <jet-dialog-modal :show="showModal" @close="$emit('close')">
        <template #title>
            นำเข้ารายชื่อนิสิตผู้เกี่ยวข้อง
        </template>

        <template #content>
            <ol class="m-4 list-decimal list-outside">
                <li>ดาวน์โหลด<a href="/import_participant_template.xlsx" class="text-green-600">ไฟล์ Template</a></li>
                <li>
                    กรอกรายชื่อนิสิตผู้เกี่ยวข้องกับกิจกรรมลงในตาราง โดยมีคอลัมน์ต่อไปนี้
                    <ul class="list-disc list-outside">
                        <li><strong>student_id</strong> : เลขประจำตัวนิสิต (10 หลัก) หรือ อีเมล docchula ของนิสิต</li>
                        <li><strong>type</strong> : ใส่ organizer, staff, หรือ attendee เท่านั้น (ผู้รับผิดชอบ/ผู้จัดกิจกรรม/ผู้เข้าร่วม)</li>
                        <li><strong>title</strong> : ตำแหน่งงาน (ไม่จำเป็นต้องใส่) เช่น ประธานโครงการ หัวหน้าฝ่ายสถานที่</li>
                    </ul>
                </li>
                <li>
                    <span v-if="uploading" class="text-gray-500">กำลังอัปโหลดไฟล์...</span>
                    <label for="file-upload" v-else
                           class="cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        <span>อัปโหลดไฟล์ Excel</span>
                        <input id="file-upload" type="file" class="sr-only"
                               accept="text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                               @input="uploadFile($event.target.files[0])">
                    </label>
                    <p class="text-gray-500 text-sm">ระบบอาจจำกัดการประมวลผลหากอัปโหลดรายชื่อนิสิตจำนวนมาก หากพบว่ารายชื่อปรากฏไม่ครบ ให้รอ 5
                        นาทีแล้วอัปโหลดไฟล์เดิมซ้ำ</p>
                </li>
            </ol>
            <div v-if="importData.messages.length > 0" class="p-2 m-4 text-red-500 border rounded border-red-500">
                <p v-for="message in importData.messages">{{ message }}</p>
            </div>
            <div class="px-4 py-2 sm:px-6 border border-gray-200" v-if="importData.preview">
                <p class="mb-2 text-xs text-gray-400">ข้อมูลที่จะนำเข้า ({{ importData.preview.length }} คน):</p>
                <table class="divide-y divide-gray-200">
                    <thead>
                    <tr>
                        <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                            นิสิต
                        </th>
                        <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                            บทบาท
                        </th>
                        <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                            ตำแหน่ง
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="e in importData.preview">
                        <td class="p-2" :class="{'bg-yellow-200': e.existing}">
                            {{ e.user_name }}
                        </td>
                        <td class="p-2">
                            {{ {organizer: 'ผู้รับผิดชอบ', staff: 'ผู้จัดกิจกรรม', attendee: 'ผู้เข้าร่วม'}[e.type] ?? e.type }}
                        </td>
                        <td class="p-2">
                            <span v-if="e.title">{{ e.title }}</span>
                            <span v-else class="text-gray-400">-</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <template #footer>
            <jet-secondary-button @click.native="$emit('close')">
                ปิด
            </jet-secondary-button>
            <jet-button :disabled="!importData.import" @click="importCommit" class="ml-4">
                นำเข้าข้อมูล
            </jet-button>
        </template>
    </jet-dialog-modal>
</template>

<script setup>
import JetDialogModal from "../Jetstream/DialogModal";
import JetButton from "../Jetstream/Button";
import JetSecondaryButton from "../Jetstream/SecondaryButton";
import {Inertia} from '@inertiajs/inertia';
import {ref} from "vue";

const props = defineProps({
    'showModal': Boolean,
    'project': Object,
});
const emit = defineEmits(['close']);
const importData = ref({preview: null, import: null, messages: []});
let uploading = ref(false);

const uploadFile = (file) => {
    uploading.value = true;
    importData.value = {preview: null, import: null, messages: []};
    axios.postForm(route('projects.importParticipantUpload', {project: props.project.id}), {
        import: file
    }).then((response) => {
        uploading.value = false;
        importData.value = response.data.data;
    })
};
const importCommit = () => {
    Inertia.post(route('projects.importParticipantCommit', {project: props.project.id}), {import: importData.value.import}, {onSuccess: () => emit('close')});
};
</script>

<style scoped>
ol > li {
    margin-top: 0.5rem;
}
</style>
