<template>
    <app-layout>
        <template #header>
            <inertia-link :href="route('projects.index')" class="mb-4 block flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                <p>โครงการ</p>
            </inertia-link>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                สรุปโครงการ ตามหน่วยงาน
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <search-input class="mb-4" v-model="searchKeyword" :status="searchMessage"/>
            <p class="text-xs text-gray-300"><a class="cursor-pointer" @click="toggleTableMode">แสดงแบบตาราง</a></p>
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
                        <th v-if="tableMode" scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            ส่วนงาน
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            วันที่จัด
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            อาจารย์ที่ปรึกษา
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            SDGs
                        </th>
                        <th scope="col" class="relative px-2 py-2 md:px-4 md:py-3 hidden md:table-cell">
                            <span class="sr-only">Edit</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <template v-for="(projects, department_id) in list" :key="department_id">
                        <tr v-if="!tableMode" class="bg-gray-200 text-gray-700">
                            <td class="px-2 py-1 md:px-4"></td>
                            <td class="px-2 py-1 md:px-4" colspan="2">{{ static_departments.find(a => parseInt(a.id) === parseInt(department_id))?.name ?? department_id }}</td>
                            <td class="px-2 py-1 md:px-4 text-sm text-right" colspan="3">{{ projects.length }} โครงการ</td>
                        </tr>
                        <template v-for="item in projects" :key="item.id">
                            <tr>
                                <td class="px-2 py-2 md:px-4">
                                    <inertia-link :href="route('projects.show', {project: item.id})">
                                        {{ item.year }}-{{ item.number }}
                                    </inertia-link>
                                </td>
                                <td class="px-2 py-2 md:px-4">
                                    <inertia-link :href="route('projects.show', {project: item.id})">
                                        {{ item.name }}
                                    </inertia-link>
                                </td>
                                <td v-if="tableMode" class="px-2 py-2 md:px-4">
                                    {{ item.department.name }}
                                </td>
                                <td class="px-2 py-2 md:px-4 text-gray-600 text-xs">
                                    {{ item.period_start }}
                                    <span v-if="item.period_start !== item.period_end">- {{ item.period_end }}</span>
                                </td>
                                <td class="px-2 py-2 md:px-4 text-gray-600 text-xs">
                                    {{ item.advisor }}
                                </td>
                                <td class="px-2 py-2 md:px-4 text-gray-600 text-xs">
                                    {{ item.sdgs?.join(', ') }}
                                </td>
                                <td class="px-2 py-2 md:px-4 text-right text-xs font-medium hidden md:table-cell">
                                    <span v-if="item.documents.find(d => d.tag === 'approval') && item.documents.find(d => d.tag === 'summary')" class="text-green-500 mx-1">✓</span>
                                    {{ item.participants_count }} 👤
                                </td>
                            </tr>
                            <tr v-if="!tableMode" v-for="document in item.documents" :key="document.id" class="text-sm text-gray-700">
                                <td class="px-2 py-1 md:px-4 text-right">
                                    <inertia-link :href="route('documents.show', {document: document.id})">
                                        {{ document.number }}<span v-if="document.number_to">-{{ item.number_to }}</span>
                                        <span class="text-xs">/{{ document.year }}</span>
                                    </inertia-link>
                                </td>
                                <td class="px-2 py-1 md:px-4" colspan="5">
                                    <DocumentChartBarIcon class="text-yellow-700 h-4 w-4 inline-block" v-if="document.tag === 'summary'" />
                                    <DocumentTextIcon class="text-purple-700 h-4 w-4 inline-block" v-if="document.tag === 'approval'"/>
                                    <inertia-link :href="route('documents.show', {document: document.id})">
                                        {{ document.title }}
                                    </inertia-link>
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

export default {
    components: {
        AppLayout,
        Pagination,
        SearchInput,
        DocumentChartBarIcon,
        DocumentTextIcon,
    },
    data() {
        return {
            searchKeyword: this.keyword ?? "",
            searchMessage: "",
            tableMode: false
        };
    },
    methods: {
        search(keyword) {
            this.$inertia.get(this.route('projects.indexOfYear'), {search: keyword}, {
                only: ['list', 'keyword'],
                preserveState: true
            });
            this.searchMessage = "";
        },
        toggleTableMode() {
            this.tableMode = !this.tableMode;
        }
    },
    watch: {
        // whenever question changes, this function will run
        searchKeyword: function (newValue, oldValue) {
            this.searchMessage = "Typing...";
            this.debouncedSearch(newValue)
        }
    },
    created: function (keyword) {
        this.debouncedSearch = _.debounce(this.search, 500)
    },
    props: {
        keyword: String | null,
        list: Object,
        static_departments: Array,
    }
};
</script>
