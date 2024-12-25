<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ประวัติกิจกรรม
            </h2>
            <p class="mt-2 text-gray-500">
                บันทึกข้อมูลกิจกรรมที่ไม่ใช่โครงการสพจ. ลงใน Activity Transcript
            </p>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <SearchInput class="mb-4" v-model="searchKeyword" :status="searchMessage"/>
            <div v-if="list?.data" class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
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
                            วันที่จัด
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            ระยะเวลา (ชม.)
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            บทบาท
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-right text-xs font-medium text-gray-500 tracking-wider">
                            <UserIcon class="h-4 w-4 inline-block"/>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in list.data" :key="item.id">
                        <td class="px-2 py-2 md:px-4 text-sm">
                            {{ item.id }}
                        </td>
                        <td class="px-2 py-2 md:px-4">
                            <Link :href="route('activities.show', {activity: item.id})">
                                {{ item.name }}
                            </Link>
                            <p class="text-sm text-purple-500">{{ item.organization }}</p>
                        </td>
                        <td class="px-2 py-2 md:px-4 text-gray-600 text-xs">
                            {{ item.period_start }}
                            <span v-if="item.period_start !== item.period_end">- {{ item.period_end }}</span>
                        </td>
                        <td class="px-2 py-2 md:px-4 text-sm">
                            {{ item.duration }}
                        </td>
                        <td class="px-2 py-2 md:px-4 text-sm">
                            {{ PROJECT_PARTICIPANT_ROLES[item.role] ?? item.role }}
                        </td>
                        <td class="px-2 py-2 md:px-4 text-right text-sm font-medium hidden md:table-cell">
                            {{ item.participants.length }}
                        </td>
                    </tr>
                    </tbody>
                </table>
                <pagination class="mt-6" :links="list.links"/>
            </div>
            <Link :href="route('activities.create')">
                <button
                    class="p-0 w-16 h-16 bg-purple-600 rounded-full hover:bg-purple-700 active:shadow-lg mouse shadow transition ease-in duration-200 focus:outline-none fixed bottom-6 right-6">
                    <PlusIcon class="w-6 h-6 inline-block text-white"/>
                </button>
            </Link>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '../Layouts/AppLayout.vue';
import Pagination from '../Components/Pagination.vue';
import SearchInput from '../Components/SearchInput.vue';
import {Link, router} from '@inertiajs/vue3';
import {PlusIcon, UserIcon} from '@heroicons/vue/20/solid';
import {ref, watch} from 'vue';
import {debounce} from 'lodash';
import {Activity} from '@/types';
import {PROJECT_PARTICIPANT_ROLES} from '@/static';

// Props
const props = defineProps<{
    keyword: String,
    list: { data: Activity[], links: object[] },
}>();
// Data
const searchKeyword = ref(props.keyword ?? '');
const searchMessage = ref('');

// Methods
const search = (keyword) => {
    router.get(route('activities.index'), {search: keyword}, {
        only: ['list', 'keyword'],
        preserveState: true,
    });
    searchMessage.value = "";
};
const debouncedSearch = debounce(search, 500);
watch(searchKeyword, (newValue) => {
    searchMessage.value = 'Typing...';
    debouncedSearch(newValue);
});
</script>
