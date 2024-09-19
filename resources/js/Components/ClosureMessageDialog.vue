<template>
    <DialogModal :show="showModal" @close="$emit('close')">
        <template #title>
            บันทึกหมายเหตุ
        </template>

        <template #content>
            <p>
                หมายเหตุจะแสดงในหน้าโครงการ ซึ่งนิสิตสามารถดูได้
            </p>
            <div class="mt-4">
                <Label for="remark" value="หมายเหตุ"/>
                <Input id="remark" type="text" class="mt-2 block w-full" v-model.trim="form.remark"/>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click.native="$emit('close')">
                ปิด
            </SecondaryButton>
            <Button @click="submit" class="ml-4">
                บันทึก
            </Button>
        </template>
    </DialogModal>
</template>

<script setup>
import {useForm} from '@inertiajs/vue3'
import Input from "@/Jetstream/Input.vue";
import Button from "@/Jetstream/Button.vue";
import SecondaryButton from "@/Jetstream/SecondaryButton.vue";
import DialogModal from "@/Jetstream/DialogModal.vue";
import Label from "@/Jetstream/Label.vue";

const props = defineProps({
    'showModal': Boolean,
    'project': Object,
});
const emit = defineEmits(['close']);
const form = useForm({
    remark: props.project.closure_approved_message,
});
const submit = () => {
    form.post(route('projects.updateRemark', {project: props.project.id}), {
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>
