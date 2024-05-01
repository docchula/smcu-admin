<template>
    <app-layout>
        <template #header>
            <Link :href="route('projects.index')" class="mb-4 flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                <p>โครงการ</p>
            </Link>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                สรุปงบประมาณโครงการ
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <Label>ปีวาระ/ชื่อโครงการ</Label>
            <search-input class="mb-4" v-model="searchKeyword" :status="searchMessage"/>
            <p class="mt-2 text-xs text-gray-500">
                ใช้ , แทนความหมาย "หรือ" ในการค้นหา
            </p>

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
                            ประเภทรายจ่าย
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            แหล่งงบประมาณ
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            วางแผนงบ (บ.)
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            จ่ายจริงไป (บ.)
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <template v-for="(projects, department_id) in list" :key="department_id">
                        <tr class="bg-gray-400 text-gray-800">
                            <td class="px-2 py-1 md:px-4"></td>
                            <td class="px-2 py-1 md:px-4" colspan="3">
                                {{ static_departments.find(a => parseInt(a.id) === parseInt(department_id))?.name ?? department_id }}
                            </td>
                            <td class="px-2 py-1 md:px-4 text-sm text-right" colspan="2">{{ projects.length }} โครงการ</td>
                        </tr>
                        <template v-for="item in projects" :key="item.id">
                            <tr class="bg-green-100">
                                <td class="px-2 py-2 md:px-4">
                                    <Link :href="route('projects.show', {project: item.id})">
                                        {{ item.year }}-{{ item.number }}
                                    </Link>
                                </td>
                                <td class="px-2 py-2 md:px-4">
                                    <Link :href="route('projects.show', {project: item.id})">
                                        {{ item.name }}
                                    </Link>
                                </td>
                                <td colspan="2" class="px-2 py-2 md:px-4 text-gray-600 text-xs">
                                    {{ item.period_start }}
                                    <span v-if="item.period_start !== item.period_end">- {{ item.period_end }}</span>
                                </td>
                                <template v-if="item.expense && item.expense.length > 0">
                                    <td class="px-2 py-2 text-right">
                                        {{ Number(item.expense.reduce((i, entry) => i - -(entry.amount || 0), 0)).toLocaleString('th-TH') }}
                                    </td>
                                    <td v-if="item.expense.find(e => e.paid)" class="px-2 py-2 text-right text-indigo-500">
                                        {{ Number(item.expense.reduce((i, entry) => i - -(entry.paid || 0), 0)).toLocaleString('th-TH') }}
                                    </td>
                                    <td v-else class="px-2 py-2 text-right">
                                        -
                                    </td>
                                </template>
                                <template v-else>
                                    <td class="px-2 py-2 text-right">-</td>
                                    <td class="px-2 py-2 text-right">-</td>
                                </template>
                            </tr>
                            <tr v-for="(entry, entryKey) in item.expense" class="text-sm text-gray-800">
                                <td class="text-xs text-white">
                                    {{ item.year }}-{{ item.number }}-E{{ String(entryKey).padStart(2, '0') }}
                                </td>
                                <td class="px-2 py-1 md:px-4">
                                    {{ entry.name }}
                                </td>
                                <td class="px-2 py-1 md:px-4 text-xs">
                                    {{ entry.type }}
                                </td>
                                <td class="px-2 py-1 md:px-4 text-xs">
                                    {{ entry.source }}
                                </td>
                                <td class="px-2 py-1 text-right">
                                    {{ entry.amount ? Number(entry.amount).toLocaleString('th-TH') : '-' }}
                                </td>
                                <td class="px-2 py-1 text-right text-indigo-500">
                                    {{ entry.paid ? Number(entry.paid).toLocaleString('th-TH') : '-' }}
                                </td>
                            </tr>
                        </template>
                    </template>
                    </tbody>
                </table>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchInput from "@/Components/SearchInput.vue";
import Pagination from "@/Components/Pagination.vue";
import {DocumentChartBarIcon, DocumentTextIcon} from "@heroicons/vue/20/solid";
import Label from "@/Jetstream/Label.vue";
import {debounce} from "lodash/function";
import {Link} from "@inertiajs/vue3";
import JetInputError from "@/Jetstream/InputError.vue";

export default {
    components: {
        JetInputError,
        Label,
        AppLayout,
        Pagination,
        SearchInput,
        DocumentChartBarIcon,
        DocumentTextIcon,
        Link,
    },
    data() {
        return {
            searchKeyword: this.keyword ?? "",
            searchMessage: "",
        };
    },
    methods: {
        search(keyword) {
            this.$inertia.get(route('projects.budget'), {search: keyword}, {
                only: ['list', 'keyword'],
                preserveState: true
            });
            this.searchMessage = "";
        },
    },
    watch: {
        // whenever question changes, this function will run
        searchKeyword: function (newValue) {
            this.searchMessage = "Typing...";
            this.debouncedSearch(newValue)
        }
    },
    created: function () {
        this.debouncedSearch = debounce(this.search, 500)
    },
    props: {
        keyword: String | Number | null,
        list: Object,
        static_departments: Array,
    }
};
</script>
