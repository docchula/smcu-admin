<template>
    <app-layout>
        <Head title="สารบรรณ"/>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                สารบรรณ
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <search-input class="mb-4" v-model="searchKeyword" :status="searchMessage"/>
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            เลขที่
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            หัวเรื่อง
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" v-if="hasDepartment">
                            หน่วยงาน
                        </th>
                        <th scope="col" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            สร้างเมื่อ
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in list.data" :key="item.id">
                        <td class="px-2 py-2 md:px-4 md:py-3">
                            <inertia-link :href="route('documents.show', {document: item.id})">
                                {{ item.number }}<span v-if="item.number_to">-{{ item.number_to }}</span><span class="text-sm">/{{ item.year }}</span>
                            </inertia-link>
                        </td>
                        <td class="px-2 py-2 md:px-4 md:py-3">
                            <inertia-link :href="route('documents.show', {document: item.id})">
                                {{ item.title }}
                            </inertia-link>
                            <span class="pl-1">
                                <DocumentTextIcon v-if="item.tag === 'approval'" class="text-purple-700 h-4 w-4 inline-block"/>
                                <DocumentChartBarIcon v-if="item.tag === 'summary'" class="text-yellow-700 h-4 w-4 inline-block"/>
                                <DocumentCheckIcon v-if="item.status === 'APPROVED'" class="text-green-700 h-4 w-4 inline-block"/>
                                <ExclamationCircleIcon v-if="item.status === 'REJECTED'" class="text-amber-700 h-4 w-4 inline-block"/>
                                <PaperAirplaneIcon v-if="item.status === 'UNDELIVERED'" class="text-red-600 h-4 w-4 inline-block"/>
                            </span>
                        </td>
                        <td v-if="hasDepartment" class="px-2 py-2 md:px-4 md:py-3 text-sm">
                            <span v-if="item.department_id === 33" class="text-gray-400">-</span>
                            <template v-else-if="item.department">{{ item.department.name }}</template>
                            <i class="text-gray-300" v-else>{{ item.department_id }}</i>
                        </td>
                        <td class="px-2 py-2 md:px-4 md:py-3 text-gray-500 text-xs">
                                <span v-if="item.created_at">
                                    {{ item.created_at }}
                                </span>
                            <span v-else>-</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <pagination class="mt-6" :links="list.links"/>
        </div>
        <inertia-link :href="route('documents.create')">
            <button
                class="p-0 w-16 h-16 bg-green-600 rounded-full hover:bg-green-700 active:shadow-lg mouse shadow transition ease-in duration-200 focus:outline-none fixed bottom-6 right-6">
                <PlusIcon class="w-6 h-6 inline-block text-white"/>
            </button>
        </inertia-link>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchInput from "@/Components/SearchInput.vue";
import Pagination from "@/Components/Pagination.vue";
import {DocumentChartBarIcon, DocumentTextIcon, PlusIcon} from '@heroicons/vue/20/solid';
import {DocumentCheckIcon, ExclamationCircleIcon, PaperAirplaneIcon} from "@heroicons/vue/24/outline";
import {Head} from "@inertiajs/vue3";

export default {
    components: {
        Head,
        PlusIcon,
        AppLayout,
        Pagination,
        SearchInput,
        DocumentChartBarIcon,
        DocumentCheckIcon,
        DocumentTextIcon,
        ExclamationCircleIcon,
        PaperAirplaneIcon,
    },
    data() {
        return {
            searchKeyword: this.keyword ?? "",
            searchMessage: ""
        };
    },
    methods: {
        search(keyword) {
            this.$inertia.get(this.route('documents.index'), {search: keyword}, {
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
    },
    computed: {
        hasDepartment() {
            return this.list.data.filter(item => item.department_id !== null && item.department_id !== 33).length > 0;
        }
    }
};
</script>
