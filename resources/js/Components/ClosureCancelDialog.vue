<template>
    <DialogModal :show="showModal" @close="$emit('close')">
        <template #title>
            ยกเลิกการส่งข้อมูลเพื่อบันทึกใน Activity Transcript
        </template>

        <template #content>
            <p>
                ระบบไม่อนุญาตให้แก้ไขข้อมูลโครงการและรายชื่อนิสิตผู้เกี่ยวข้องหลังยืนยันส่งข้อมูลเพื่อบันทึกใน Activity Transcript แล้ว
                หากต้องการแก้ไขข้อมูล ท่านต้องยกเลิกการส่งข้อมูลก่อน
            </p>
            <p class="mt-4">
                เมื่อยกเลิกการส่งข้อมูล ระบบจะยกเลิกบันทึกการตรวจสอบข้อมูลรายชื่อนิสิตผู้เกี่ยวข้องทั้งหมด หากส่งข้อมูลเพื่อบันทึกใน Activity
                Transcript ใหม่ นิสิตผู้เกี่ยวข้องทุกคนต้องเข้ามายืนยันข้อมูลอีกครั้ง โดย<u>ไม่สามารถกู้คืน</u>สถานะการยืนยันข้อมูลเดิมได้
            </p>
            <p class="mt-4">
                โปรดระวังว่า หากท่านต้องการส่งข้อมูลเพื่อบันทึกใน Activity Transcript อีกครั้ง ต้องส่งภายในกำหนดเวลา
                <u>30 วัน นับจากสิ้นสุดกิจกรรม</u>
            </p>
            <p class="mt-4 font-bold">
                หากท่านเข้าใจและต้องการยกเลิกการส่งข้อมูล กรุณาพิมพ์ "This cannot be undone." ในช่องด้านล่างเพื่อยืนยัน
            </p>
            <Input type="text" class="mt-2 block w-full" v-model.trim="confirmText" required placeholder="ข้อความยืนยัน"/>
        </template>

        <template #footer>
            <SecondaryButton @click.native="$emit('close')">
                ปิด
            </SecondaryButton>
            <DangerButton :disabled="confirmText !== 'This cannot be undone.'" @click="submit" class="ml-4">
                ยืนยันยกเลิก
            </DangerButton>
        </template>
    </DialogModal>
</template>

<script setup>
import {ref} from "vue";
import {router} from '@inertiajs/vue3'
import Input from "@/Jetstream/Input.vue";
import DangerButton from "@/Jetstream/DangerButton.vue";
import SecondaryButton from "@/Jetstream/SecondaryButton.vue";
import DialogModal from "@/Jetstream/DialogModal.vue";

const props = defineProps({
    'showModal': Boolean,
    'project': Object,
});
const emit = defineEmits(['close']);
const confirmText = ref('');
const submit = () => {
    router.post(route('projects.closureCancel', {project: props.project.id}), {confirm: true});
};
</script>
