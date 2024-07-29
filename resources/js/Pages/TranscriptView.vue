<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Activity Transcript
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <Label>ชื่อ/อีเมล Docchula/เลขประจำตัวนิสิต</Label>
            <search-input v-model="searchKeyword" :status="searchMessage"/>

            <div v-if="user" class="my-4 p-4 flex gap-4 items-center shadow overflow-hidden border-b border-gray-200 sm:rounded-lg bg-white">
                <div class="items-center">
                    <UserIcon class="h-10 w-10 text-gray-400"/>
                </div>
                <div class="items-center">
                    <p class="">{{ user.name }}</p>
                    <p class="mt-1 text-sm text-gray-500">เลขประจำตัวนิสิต {{ user.student_id }}</p>
                </div>
                <div class="flex-auto items-center text-right">
                    <Link :href="route('transcript.print', {user: user.id})" class="text-green-600 ml-2 inline-block">
                        <PrinterIcon class="inline-block h-5 w-5"/>
                        พิมพ์
                    </Link>
                </div>
            </div>
            <div v-if="participantSorted" class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
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
        </div>
    </app-layout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchInput from '@/Components/SearchInput.vue';
import Label from "@/Jetstream/Label.vue";
import {Link, router} from '@inertiajs/vue3';
import {CheckCircleIcon, UserIcon, XCircleIcon} from '@heroicons/vue/20/solid';
import {computed, ref, watch} from 'vue';
import {debounce} from 'lodash';
import {PROJECT_PARTICIPANT_ROLES} from '@/static';
import {PrinterIcon} from "@heroicons/vue/24/solid";

const props = defineProps({
    keyword: String,
    user: Object,
    static_departments: Array,
});
const searchKeyword = ref(props.keyword ?? '');
const searchMessage = ref('');
const participantSorted = computed(() => {
    if (!props.user || !props.user.participants) {
        return null;
    }
    return props.user.participants.sort((a, b) => {
        return a.project.id - b.project.id;
    });
});

const search = () => {
    router.get(route('transcript.index'), {search: searchKeyword.value}, {
        preserveState: true
    });
    searchMessage.value = "";
};
const debouncedSearch = debounce(search, 500);
watch(searchKeyword, () => {
    searchMessage.value = 'Typing...';
    debouncedSearch();
});
</script>
