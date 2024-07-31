<script setup>
import {Link} from '@inertiajs/vue3';
import {CheckCircleIcon, XCircleIcon} from '@heroicons/vue/20/solid';
import {PROJECT_PARTICIPANT_ROLES} from '@/static';
import {computed} from 'vue';

const props = defineProps({
    participants: Array,
});
const participantSorted = computed(() => {
    if (!props.participants) {
        return null;
    }
    return props.participants.sort((a, b) => {
        return a.project.id - b.project.id;
    });
});
</script>

<template>
    <div v-if="participantSorted" class="border-b border-gray-200 ">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    เลขที่
                </th>
                <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    โครงการ
                </th>
                <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    หน่วยงาน
                </th>
                <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    วันที่จัดกิจกรรม
                </th>
                <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    ระยะเวลา (ชม.)
                </th>
                <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    บทบาท
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="participant in participantSorted" :key="participant.id"
                :class="{'text-gray-400 bg-gray-100': participant.approve_status !== 1}">
                <td class="px-2 py-2 md:px-4 text-xs">
                    <Link :href="route('projects.show', {project: participant.project.id})">
                        {{ participant.project.year }}-{{ participant.project.number }}
                    </Link>
                </td>
                <td class="px-2 py-2 md:px-4">
                    <Link :href="route('projects.show', {project: participant.project.id})">
                        {{ participant.project.name }}
                    </Link>
                </td>
                <td class="px-2 py-2 md:px-4 text-sm">{{ participant.project.department.name }}</td>
                <td class="px-2 py-2 md:px-4 text-xs">
                    {{ participant.project.period_start }}
                    <span v-if="participant.project.period_start !== participant.project.period_end">- {{
                            participant.project.period_end
                        }}</span>
                </td>
                <td class="px-2 py-2 md:px-4 text-sm">{{ participant.project.duration }}</td>
                <td class="px-2 py-2 md:px-4">
                    {{ PROJECT_PARTICIPANT_ROLES[participant.type] }}
                    <CheckCircleIcon v-if="participant.approve_status === 1" class="inline-block ml-1 h-4 w-4 text-green-500"/>
                    <XCircleIcon v-if="participant.approve_status === -1" class="inline-block ml-1 h-4 w-4 text-red-500"/>
                    <p v-if="participant.title" class="text-xs">{{ participant.title }}</p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
