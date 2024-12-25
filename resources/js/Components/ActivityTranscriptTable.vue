<script setup lang="ts">
import {CheckCircleIcon, XCircleIcon} from '@heroicons/vue/20/solid';
import {PROJECT_PARTICIPANT_ROLES} from '@/static';
import {TranscriptItem} from '@/types';

const props = defineProps<{
    transcript: TranscriptItem[],
}>();
</script>

<template>
    <div v-if="transcript" class="border-b border-gray-200 ">
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
            <tr v-for="item in transcript" :key="item.identifier"
                :class="{'text-gray-400 bg-gray-100': item.approve_status !== 1}">
                <td class="px-2 py-2 md:px-4 text-xs">
                    <a v-if="item.project_id" target="_blank"
                       :href="route('projects.show', {project: item.project_id})">
                        {{ item.identifier }}
                    </a>
                    <span v-else>{{ item.identifier }}</span>
                </td>
                <td class="px-2 py-2 md:px-4">
                    <a v-if="item.project_id" target="_blank"
                       :href="route('projects.show', {project: item.project_id})">
                        {{ item.name }}
                    </a>
                    <span v-else>{{ item.name }}</span>
                </td>
                <td class="px-2 py-2 md:px-4 text-sm">{{ item.department }}</td>
                <td class="px-2 py-2 md:px-4 text-xs">
                    {{ item.period_start }}
                    <span v-if="item.period_start !== item.period_end">- {{
                            item.period_end
                        }}</span>
                </td>
                <td class="px-2 py-2 md:px-4 text-sm">{{ item.duration }}</td>
                <td class="px-2 py-2 md:px-4">
                    {{ PROJECT_PARTICIPANT_ROLES[item.role] }}
                    <CheckCircleIcon v-if="item.approve_status === 1" class="inline-block ml-1 h-4 w-4 text-green-500"/>
                    <XCircleIcon v-if="item.approve_status === -1" class="inline-block ml-1 h-4 w-4 text-red-500"/>
                    <p v-if="item.title" class="text-xs">{{ item.title }}</p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
