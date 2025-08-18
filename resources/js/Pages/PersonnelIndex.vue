<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                กรรมการสโมสร ปีวาระ {{ year }}
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div v-if="is_outdated" class="text-sm text-gray-900 rounded border border-blue-400 p-3 bg-blue-50 mb-6">
                <p class="text-xs text-blue-400">Outdated information</p>
                ยังไม่มีข้อมูลกรรมการปีวาระปัจจุบัน กรุณาแจ้งฝ่ายเทคโนโลยีสารสนเทศ สพจ. เพื่อปรับปรุงข้อมูล
            </div>
            <search-input v-model="searchKeyword" :status="searchMessage" class="mb-4" placeholder="Year (B.E.)"/>
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" scope="col">
                            #
                        </th>
                        <th class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" scope="col">
                            Name / Email
                        </th>
                        <th class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" scope="col">
                            Position
                        </th>
                        <th class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" scope="col">
                            Department
                        </th>
                        <th v-if="is_admin" class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" scope="col">
                            Sequence / Supervisor
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in list" :id="'row-'+item.id" :key="item.id" :class="{'text-gray-400': item.sequence >= 200}">
                        <td class="px-2 py-2 md:px-4 md:py-3 text-xs">
                            {{ item.id }}
                        </td>
                        <td class="px-2 py-2 md:px-4 md:py-3">
                            <inertia-link :href="is_admin ? route('personnels.edit', {personnel: item.id}) : ('#row-'+item.id)">{{
                                    item.name
                                }}
                            </inertia-link>
                            <p class="text-xs">{{ item.name_en }}</p>
                            <p v-if="item.email" class="text-xs">{{ item.email }}</p>
                        </td>
                        <td class="px-2 py-2 md:px-4 md:py-3">
                            {{ item.position }}
                            <p class="text-xs">{{ item.position_en }}</p>
                        </td>
                        <td class="px-2 py-2 md:px-4 md:py-3 text-sm">
                            {{ item.department?.name }}
                        </td>
                        <td v-if="is_admin" class="px-2 py-2 md:px-4 md:py-3">
                            {{ item.sequence }}
                            <p v-if="item.supervisor" class="text-xs">#{{
                                    item.supervisor
                                }} {{ list.find(i => i.id === item.supervisor)?.name.split(' ')[0] }}</p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <inertia-link v-if="is_admin" :href="route('personnels.create', {year})">
            <button
                class="p-0 w-16 h-16 bg-pink-600 rounded-full hover:bg-pink-700 active:shadow-lg mouse shadow transition ease-in duration-200 focus:outline-none fixed bottom-6 right-6">
                <svg class="w-6 h-6 inline-block" enable-background="new 0 0 20 20" viewBox="0 0 20 20">
                    <path d="M16,10c0,0.553-0.048,1-0.601,1H11v4.399C11,15.951,10.553,16,10,16c-0.553,0-1-0.049-1-0.601V11H4.601
                                    C4.049,11,4,10.553,4,10c0-0.553,0.049-1,0.601-1H9V4.601C9,4.048,9.447,4,10,4c0.553,0,1,0.048,1,0.601V9h4.399
                                    C15.952,9,16,9.447,16,10z" fill="#FFFFFF"/>
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
import {debounce} from "lodash/function";

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
            searchKeyword: String(this.year) ?? "",
            searchMessage: ""
        };
    },
    methods: {
        search(keyword) {
            this.$inertia.get(this.route('personnels.index'), {year: keyword}, {
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
        year: Number | null,
        list: Object,
        is_admin: Boolean,
        is_outdated: Boolean,
    }
};
</script>
