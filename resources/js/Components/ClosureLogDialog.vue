<template>
    <DialogModal :show="showModal" @close="$emit('close')">
        <template #title>
            ประวัติการบันทึกรายงานผลโครงการ {{ project.name }}
        </template>

        <template #content>
            <template v-if="logs">
                <table v-if="logs.length > 0" class="min-w-full divide-y divide-gray-200 border-b border-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            เวลา
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            ผู้กระทำ
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            รายการ
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="log in logs" :key="log.id">
                        <td class="px-2 py-2 md:px-4 text-sm">{{ log.created_at }}</td>
                        <td class="px-2 py-2 md:px-4 text-sm">{{ log.causer }}</td>
                        <td class="px-2 py-2 md:px-4 text-sm">{{ log.description }}</td>
                    </tr>
                    </tbody>
                </table>
                <p v-else>ไม่พบข้อมูล</p>
                <p class="text-xs text-gray-500 mt-4">แสดงเฉพาะ 100 รายการล่าสุด ประวัติรายการเก่าอาจถูกลบออกจากระบบ</p>
            </template>
            <p v-else class="my-8 text-center text-gray-500">กำลังโหลด</p>
        </template>

        <template #footer>
            <SecondaryButton @click.native="$emit('close')">
                ปิด
            </SecondaryButton>
        </template>
    </DialogModal>
</template>

<script setup>
import {ref, watch} from "vue";
import SecondaryButton from "@/Jetstream/SecondaryButton.vue";
import DialogModal from "@/Jetstream/DialogModal.vue";

const props = defineProps({
    'showModal': Boolean,
    'project': Object,
});
defineEmits(['close']);
const logs = ref(null);

watch(() => props.showModal, async (showModal) => {
    if (showModal) {
        logs.value = (await axios.get(route('projects.logs', {project: props.project.id})))?.data?.logs;
    }
});
</script>
