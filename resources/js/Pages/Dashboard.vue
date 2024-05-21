<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Jetstream/Welcome.vue';
import {PROJECT_PARTICIPANT_ROLES} from "@/static";

const props = defineProps({myProjects: Array});
const lastYear = new Date();
lastYear.setMonth(lastYear.getMonth() - 18);
const participants = props.myProjects.map(participant => {
    participant.project.awaitingSummary = participant.project.approval_document && !participant.project.summary_document && ![32, 38, 39].includes(participant.project.department_id) && (new Date(participant.project.created_at) > lastYear);
    participant.project.awaitingSummaryAlert = participant.project.awaitingSummary && (new Date(participant.project.created_at) > lastYear) && (participant.type === 'organizer');
    return participant;
});
const projectsAwaitingSummary = props.myProjects.map(participant => participant.project).filter(project => project.awaitingSummaryAlert);
</script>
<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                หน้าหลัก
            </h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-12">
            <div v-if="projectsAwaitingSummary.length > 0" class="bg-blue-100 border-blue-500 text-blue-500 border-l-4 rounded p-4 mb-6" role="alert">
                <p class="font-bold">
                    มี {{ projectsAwaitingSummary.length }} โครงการที่กำลังดำเนินงานอยู่
                </p>
                <p>เมื่อเสร็จสิ้นโครงการแล้ว ให้ส่งรายงานผลการดำเนินโครงการ</p>
                <table class="text-sm">
                    <tr v-for="project in projectsAwaitingSummary">
                        <td>•</td>
                        <td class="px-2">
                            <inertia-link :href="route('projects.show', {project: project.id})" class="hover:text-blue-600 text-xs">
                                {{ project.year }}-{{ project.number }}
                            </inertia-link>
                        </td>
                        <td>
                            <inertia-link :href="route('projects.show', {project: project.id})" class="hover:text-blue-600">
                                {{ project.name }}
                            </inertia-link>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                <welcome/>
            </div>
        </div>

        <div v-if="myProjects?.length > 0" class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-8">
            <div class="p-8 sm:px-20 w-full bg-white border border-gray-200 sm:rounded-lg shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h5 class="text-xl font-bold leading-none text-gray-900">โครงการที่ฉันมีส่วนร่วม</h5>
                    <!-- a class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-500" href="#">
                        View all
                    </a -->
                </div>
                <div class="flow-root">
                    <ul class="divide-y divide-gray-200" role="list">
                        <li v-for="participant in myProjects" class="py-2 sm:py-3">
                            <div class="items-center md:flex gap-4">
                                <p :class="{'text-gray-400': participant.project.awaitingSummary, 'text-gray-900': !participant.project.awaitingSummary}"
                                   class="flex-auto font-medium">
                                    <span class="text-xs text-gray-500 px-0.5">{{ participant.project.year }}-{{ participant.project.number }}</span>
                                    {{ participant.project.name }}
                                </p>
                                <p class="flex-auto text-sm text-gray-500 md:text-right">
                                    <span v-if="participant.project.awaitingSummary" class="text-xs text-gray-300">(ยังไม่ส่งรายงานผลโครงการ)</span>
                                    <template v-else>
                                        <span v-if="participant.title">{{ participant.title }} :</span>
                                        {{
                                            PROJECT_PARTICIPANT_ROLES[participant.type] ?? participant.type
                                        }}
                                    </template>
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <p class="mt-8 pb-6 max-w-7xl mx-auto sm:px-6 lg:px-8 text-xs text-gray-400 text-center">
            สร้างสรรค์โดย ศิวัช เตชวรนันท์ และฝ่ายเทคโนโลยีสารสนเทศ สพจ. | <a class="text-green-400" href="https://github.com/docchula/smcu-admin" target="_blank">Source
            code</a><br/>
            หากพบปัญหาในการใช้งาน ติดต่ออีเมล itdivision@docchula.com
        </p>
    </app-layout>
</template>
