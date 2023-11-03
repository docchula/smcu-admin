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
                กำหนดการโครงการ
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" scope="col">
                            วันที่จัด
                        </th>
                        <th class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" scope="col">
                            ถึง
                        </th>
                        <th class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" scope="col">
                            เลขที่
                        </th>
                        <th class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" scope="col">
                            ชื่อ
                        </th>
                        <th class="px-2 py-2 md:px-4 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider" scope="col">
                            สังกัด
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in list" :key="item.id">
                        <td class="px-2 py-2 md:px-4 md:py-3" :colspan="(item.period_start !== item.period_end) ? 1 : 2">
                            {{ item.period_start }}
                        </td>
                        <td v-if="item.period_start !== item.period_end" class="px-2 py-2 md:px-4 md:py-3">
                            {{ item.period_end }}
                        </td>
                        <td class="px-2 py-2 md:px-4 md:py-3 text-xs">
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
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </app-layout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import {DocumentChartBarIcon} from "@heroicons/vue/20/solid";
import {DocumentTextIcon} from "@heroicons/vue/24/outline";

const props = defineProps({
    list: Array,
});
</script>
