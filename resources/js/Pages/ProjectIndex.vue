<template>
    <app-layout>
        <template #header>
            <div class="flex flex-wrap gap-y-4 items-center">
                <div class="flex-grow">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        โครงการ
                    </h2>
                </div>
                <div class="flex-auto flex items-center justify-end gap-2">
                    <Menu v-if="is_faculty || can_view_transcript" as="div" class="relative inline-block text-left z-10">
                        <div>
                            <MenuButton
                                class="inline-flex py-2 px-4 justify-center items-center text-center text-base font-semibold transition ease-in duration-200 text-orange-500 border-orange-500 border rounded-lg shadow hover:shadow-md focus:ring-orange-500 focus:ring-offset-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                            >
                                สำหรับอาจารย์
                            </MenuButton>
                        </div>

                        <transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <MenuItems
                                class="absolute right-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
                            >
                                <Link v-if="is_faculty" :href="route('projects.approvalIndex')">
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            :class="[active ? 'bg-orange-500 text-white' : 'text-orange-600', 'group flex w-full items-center rounded-md px-2 py-2 text-sm',]"
                                        >
                                            <CheckBadgeIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true"/>
                                            อนุมัติ <span class="hidden sm:inline px-1">Activity </span> Transcript
                                        </button>
                                    </MenuItem>
                                </Link>
                                <Link :href="route('activities.index')">
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            :class="[active ? 'bg-orange-500 text-white' : 'text-orange-600', 'group flex w-full items-center rounded-md px-2 py-2 text-sm',]"
                                        >
                                            ประวัติกิจกรรมภายนอก
                                        </button>
                                    </MenuItem>
                                </Link>
                                <Link :href="route('transcript.index')">
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            :class="[active ? 'bg-orange-500 text-white' : 'text-orange-600', 'group flex w-full items-center rounded-md px-2 py-2 text-sm',]"
                                        >
                                            ดู Transcript ของนิสิต
                                        </button>
                                    </MenuItem>
                                </Link>
                            </MenuItems>
                        </transition>
                    </Menu>
                    <Menu as="div" class="relative inline-block text-left z-10">
                        <div>
                            <MenuButton
                                class="inline-flex py-2 px-4 justify-center items-center text-center text-base font-semibold transition ease-in duration-200 text-yellow-500 border-yellow-500 border rounded-lg shadow hover:shadow-md focus:ring-yellow-500 focus:ring-offset-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                            >
                                <DocumentChartBarIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true"/>
                                รายงาน
                            </MenuButton>
                        </div>

                        <transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <MenuItems
                                class="absolute right-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
                            >
                                <Link :href="route('projects.indexOfYear')">
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            :class="[active ? 'bg-yellow-500 text-white' : 'text-yellow-600', 'group flex w-full items-center rounded-md px-2 py-2 text-sm',]"
                                        >
                                            <Bars4Icon
                                                :active="active"
                                                class="mr-2 h-5 w-5"
                                                aria-hidden="true"
                                            />
                                            รายปี
                                        </button>
                                    </MenuItem>
                                </Link>
                                <Link :href="route('projects.indexAgenda')">
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            :class="[active ? 'bg-yellow-500 text-white' : 'text-yellow-600', 'group flex w-full items-center rounded-md px-2 py-2 text-sm',]"
                                        >
                                            <CalendarDaysIcon
                                                :active="active"
                                                class="mr-2 h-5 w-5"
                                                aria-hidden="true"
                                            />
                                            ตารางวันที่
                                        </button>
                                    </MenuItem>
                                </Link>
                                <Link v-if="is_admin" :href="route('projects.budget')">
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            :class="[active ? 'bg-yellow-500 text-white' : 'text-yellow-600', 'group flex w-full items-center rounded-md px-2 py-2 text-sm',]"
                                        >
                                            <CalculatorIcon
                                                :active="active"
                                                class="mr-2 h-5 w-5"
                                                aria-hidden="true"
                                            />
                                            งบประมาณ
                                        </button>
                                    </MenuItem>
                                </Link>
                            </MenuItems>
                        </transition>
                    </Menu>
                </div>
            </div>
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
                            <Link :href="route('projects.show', {project: item.id})">
                                {{ item.name }}
                            </Link>
                            <SDGBadge :value="item.sdgs" class="ml-2"/>
                            <span class="pl-1">
                                    <Link v-for="document in item.documents" :href="route('documents.show', {document: document.id})">
                                        <DocumentTextIcon v-if="document.tag === 'approval'" class="text-gray-300 h-3 w-3 inline-block"/>
                                        <DocumentChartBarIcon v-if="document.tag === 'summary'" class="text-yellow-700 h-4 w-4 inline-block"/>
                                    </Link>
                                </span>
                        </td>
                        <td class="px-2 py-2 md:px-4 md:py-3 text-sm">
                            <span v-if="item.department_id === 33" class="text-gray-400">-</span>
                            <template v-else-if="item.department">{{ item.department.name }}</template>
                            <i class="text-gray-300" v-else>{{ item.department_id }}</i>
                        </td>
                        <td class="px-2 py-2 md:px-4 md:py-3 text-gray-500 text-xs">
                            {{ item.period_start }}
                            <span v-if="item.period_start !== item.period_end">- {{ item.period_end }}</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <pagination class="mt-6" :links="list.links"/>
        </div>
        <Link :href="route('projects.create')">
            <button
                class="p-0 w-16 h-16 bg-yellow-600 rounded-full hover:bg-yellow-700 active:shadow-lg mouse shadow transition ease-in duration-200 focus:outline-none fixed bottom-6 right-6">
                <PlusIcon class="w-6 h-6 inline-block text-white"/>
            </button>
        </Link>
    </app-layout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchInput from "@/Components/SearchInput.vue";
import SDGBadge from "@/Components/SDGBadge.vue";
import Pagination from "@/Components/Pagination.vue";
import {Bars4Icon, CalculatorIcon, CalendarDaysIcon, CheckBadgeIcon, DocumentChartBarIcon, PlusIcon} from "@heroicons/vue/20/solid";
import {DocumentTextIcon} from "@heroicons/vue/24/outline";
import {Menu, MenuButton, MenuItem, MenuItems} from '@headlessui/vue'
import {Link, router} from '@inertiajs/vue3'
import {ref, watch} from 'vue';
import {debounce} from "lodash/function";

const props = defineProps({
    keyword: {
        type: String,
        default: "",
    },
    list: Object,
    is_admin: Boolean,
    is_faculty: Boolean,
    can_view_transcript: Boolean,
});
const searchKeyword = ref(props.keyword ?? "");
const searchMessage = ref('');

const search = function (keyword) {
    router.get(route('projects.index'), {search: keyword}, {
        only: ['list', 'keyword'],
        preserveState: true
    });
    searchMessage.value = "";
};

watch(searchKeyword, function (newValue) {
    searchMessage.value = "Typing...";
    debouncedSearch(newValue);
});

const debouncedSearch = debounce(search, 500);
</script>
