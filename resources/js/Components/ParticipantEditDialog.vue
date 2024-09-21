<template>
    <DialogModal :show="showModal" @close="$emit('close')">
        <template #title>
            แก้ไขข้อมูลนิสิตผู้เกี่ยวข้อง
        </template>

        <template #content v-if="participant">
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label>นิสิต</Label>
                    {{ participant.user.name }}
                </div>
                <div class="space-y-2">
                    <Label>เลขประจำตัว</Label>
                    {{ participant.user.student_id }}
                </div>
            </div>
            <div class="mt-4">
                <label for="type" class="block text-sm font-medium text-gray-700">บทบาท</label>
                <select id="type" v-model="form.type" required
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option v-for="(name, value) in PROJECT_PARTICIPANT_ROLES"
                            :key="value" :value="value">
                        {{ name }}
                    </option>
                </select>
                <InputError :message="form.errors.department_id" class="mt-2"/>
            </div>
            <div class="mt-4">
                <Label for="title" value="ตำแหน่ง"/>
                <Input id="title" type="text" class="mt-2 block w-full" v-model.trim="form.title"/>
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

<script setup lang="ts">
import {useForm} from '@inertiajs/vue3';
import Button from "@/Jetstream/Button.vue";
import DialogModal from "@/Jetstream/DialogModal.vue";
import Input from "@/Jetstream/Input.vue";
import InputError from '@/Jetstream/InputError.vue';
import Label from "@/Jetstream/Label.vue";
import SecondaryButton from "@/Jetstream/SecondaryButton.vue";
import {ProjectParticipant} from '@/types';
import {PROJECT_PARTICIPANT_ROLES} from '@/static';
import {watch} from 'vue';

const props = defineProps<{
    showModal: Boolean,
    participant: ProjectParticipant | null,
}>();
const emit = defineEmits(['close']);
const form = useForm({
    title: props.participant?.title,
    type: props.participant?.type,
});
watch(() => props.participant, (participant) => {
    form.title = participant?.title;
    form.type = participant?.type;
});
const submit = () => {
    form.post(route('projects.editParticipant', {participant: props.participant.id}), {
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>
