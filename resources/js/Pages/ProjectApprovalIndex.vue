<template>
    <AppLayout>
        <template #header>
            <div class="flex flex-wrap gap-y-4 items-center">
                <div class="flex-grow">
                    <Link :href="route('projects.index')" class="mb-4 block flex items-center text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"
                                  class="text-gray-500"/>
                        </svg>
                        <p>โครงการ</p>
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        สถานะการอนุมัติรายงานผลโครงการ
                    </h2>
                    <p class="mt-2 text-gray-500">
                        เพื่อบันทึกใน Student Profile/Activity Transcript
                    </p>
                </div>
                <div class="flex-auto flex items-center justify-end gap-2">
                    <Link :href="route('transcript.index')"
                       class="inline-flex py-2 px-4 justify-center items-center text-center text-base font-semibold transition ease-in duration-200 text-purple-500 border-purple-500 border rounded-lg shadow hover:shadow-md focus:ring-purple-500 focus:ring-offset-purple-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    >
                        ดู <span class="hidden sm:inline px-1">Activity </span> Transcript ของนิสิต
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <SearchInput class="mb-4" v-model="searchKeyword" :status="searchMessage"/>
            <div v-if="list" class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            เลขที่
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            ชื่อ
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            ส่วนงาน
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            วันที่จัด
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-right text-xs font-medium text-gray-500 tracking-wider">
                            <UserIcon class="h-4 w-4 inline-block"/>
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            สถานะ
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in list" :key="item.id">
                        <td class="px-2 py-2 md:px-4 text-sm">
                            <Link :href="route('projects.approvalForm', {project: item.id})">
                                {{ item.year }}-{{ item.number }}
                            </Link>
                        </td>
                        <td class="px-2 py-2 md:px-4">
                            <Link :href="route('projects.approvalForm', {project: item.id})">
                                {{ item.name }}
                            </Link>
                        </td>
                        <td class="px-2 py-2 md:px-4 text-sm">
                            {{ item.department.name }}
                        </td>
                        <td class="px-2 py-2 md:px-4 text-gray-600 text-xs">
                            {{ item.period_start }}
                            <span v-if="item.period_start !== item.period_end">- {{ item.period_end }}</span>
                        </td>
                        <td class="px-2 py-2 md:px-4 text-right text-sm font-medium hidden md:table-cell">
                            {{ item.participants_count }}
                        </td>
                        <td class="px-2 py-2 md:px-4 text-gray-600 text-xs"
                            :class="{'bg-blue-300': item.status === 5, 'bg-green-300': item.status === 10,'bg-gray-300': item.status === 10,'bg-yellow-300': item.status === 1}">
                            {{ {1: 'ส่งปิดโครง', 5: 'ผู้เกี่ยวข้องยืนยัน', 10: 'อนุมัติ', '-1': 'ไม่อนุมัติ'}[item.status] ?? item.status }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchInput from '@/Components/SearchInput.vue';
import {Link, router} from '@inertiajs/vue3';
import {UserIcon} from '@heroicons/vue/20/solid';
import {ref, watch} from 'vue';
import {debounce} from 'lodash';

// Props
const props = defineProps({
    keyword: String,
    list: Array,
    static_departments: Array,
});
// Data
const searchKeyword = ref(props.keyword ?? '');
const searchMessage = ref('');

// Methods
const search = (keyword) => {
    router.get(route('projects.approvalIndex'), {search: keyword}, {
        only: ['list', 'keyword'],
        preserveState: true
    });
    searchMessage.value = "";
};
const debouncedSearch = debounce(search, 500);
watch(searchKeyword, (newValue) => {
    searchMessage.value = 'Typing...';
    debouncedSearch(newValue);
});
</script>
