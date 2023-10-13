<template>
    <app-layout>
        <template #header>
            <div class="flex flex-wrap gap-y-4 items-center">
                <div class="flex-grow">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        โครงการ
                    </h2>
                </div>
                <div class="flex-auto flex items-center justify-end">
                    <inertia-link :href="route('projects.indexOfYear')">
                        <button type="button"
                                class="py-2 px-4 flex justify-center items-center text-center text-base font-semibold transition ease-in duration-200 text-yellow-500 border-yellow-500 border rounded-lg shadow hover:shadow-md focus:ring-yellow-500 focus:ring-offset-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <Bars4Icon class="h-5 w-5 mr-2"/>
                            สรุปรายปี
                        </button>
                    </inertia-link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <search-input class="mb-4" v-model="searchKeyword" :status="searchMessage" />
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
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
                                สังกัด
                            </th>
                            <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                                วันที่จัด
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in list.data" :key="item.id">
                            <td class="px-2 py-2 md:px-4 md:py-3">
                                    {{ item.year }}-{{ item.number }}
                            </td>
                            <td class="px-2 py-2 md:px-4 md:py-3">
                                <inertia-link :href="route('projects.show', {project: item.id})">
                                    {{ item.name }}
                                </inertia-link>
                                <span class="pl-1">
                                    <inertia-link v-for="document in item.documents" :href="route('documents.show', {document: document.id})">
                                        <DocumentTextIcon v-if="document.tag === 'approval'" class="text-gray-300 h-3 w-3 inline-block"/>
                                        <DocumentChartBarIcon v-if="document.tag === 'summary'" class="text-yellow-700 h-4 w-4 inline-block"/>
                                    </inertia-link>
                                </span>
                            </td>
                            <td class="px-2 py-2 md:px-4 md:py-3 text-sm">
                                <span v-if="item.department_id === 33" class="text-gray-400">-</span>
                                <template v-else>{{ item.department.name }}</template>
                            </td>
                            <td class="px-2 py-2 md:px-4 md:py-3 text-gray-500 text-xs">
                                {{ item.period_start }}
                                <span v-if="item.period_start !== item.period_end">- {{ item.period_end }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <pagination class="mt-6" :links="list.links" />
        </div>
        <inertia-link :href="route('projects.create')">
            <button
                class="p-0 w-16 h-16 bg-yellow-600 rounded-full hover:bg-yellow-700 active:shadow-lg mouse shadow transition ease-in duration-200 focus:outline-none absolute bottom-6 right-6">
                <svg viewBox="0 0 20 20" enable-background="new 0 0 20 20" class="w-6 h-6 inline-block">
                    <path fill="#FFFFFF" d="M16,10c0,0.553-0.048,1-0.601,1H11v4.399C11,15.951,10.553,16,10,16c-0.553,0-1-0.049-1-0.601V11H4.601
                                    C4.049,11,4,10.553,4,10c0-0.553,0.049-1,0.601-1H9V4.601C9,4.048,9.447,4,10,4c0.553,0,1,0.048,1,0.601V9h4.399
                                    C15.952,9,16,9.447,16,10z"/>
                </svg>
            </button>
        </inertia-link>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchInput from "@/Components/SearchInput.vue";
import Pagination from "@/Components/Pagination.vue";
import {Bars4Icon} from "@heroicons/vue/24/solid";
import {DocumentChartBarIcon} from "@heroicons/vue/20/solid";
import {DocumentTextIcon} from "@heroicons/vue/24/outline";

export default {
    components: {
        DocumentChartBarIcon, DocumentTextIcon,
        AppLayout,
        Pagination,
        SearchInput,
        Bars4Icon,
    },
    data() {
        return {
            searchKeyword: this.keyword ?? "",
            searchMessage: ""
        };
    },
    methods: {
        search(keyword) {
            this.$inertia.get(this.route('projects.index'), {search: keyword}, {
                only: ['list', 'keyword'],
                preserveState: true
            });
            this.searchMessage = "";
        },
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
        keyword: String|null,
        list: Object
    }
};
</script>
