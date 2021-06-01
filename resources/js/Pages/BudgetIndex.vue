<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                งบประมาณรายปีสโมสร วาระ 2565
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                                โครงการ
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                                งบรายปีสพจ.
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                                งบอื่น ๆ
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-1 bg-gray-100 text-gray-500">
                                คณะกรรมการบริหาร
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 whitespace-nowrap">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    ค่ายอยากเป็นแมว
                                </inertia-link>
                            </td>
                            <td class="px-6 py-2 text-right whitespace-nowrap">
                                -
                            </td>
                            <td class="px-6 py-2 text-right whitespace-nowrap">
                                300,000.00
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 whitespace-nowrap">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    Unblock
                                </inertia-link>
                            </td>
                            <td class="px-6 py-2 text-right whitespace-nowrap">
                                -
                            </td>
                            <td class="px-6 py-2 text-right whitespace-nowrap">
                                5,000.00
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-1 bg-gray-100 text-gray-500">
                                ฝ่ายพัฒนาตนเอง
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 whitespace-nowrap">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    ค่ายปลายภาค
                                </inertia-link>
                            </td>
                            <td class="px-6 py-2 text-right whitespace-nowrap">
                                32,000.00
                            </td>
                            <td class="px-6 py-2 text-right whitespace-nowrap">
                                120,000.00
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-2 whitespace-nowrap">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    ค่ายเพื่อนใหม่
                                </inertia-link>
                            </td>
                            <td class="px-6 py-2 text-right whitespace-nowrap">
                                6,000.00
                            </td>
                            <td class="px-6 py-2 text-right whitespace-nowrap">
                                15,000.00
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'

export default {
    components: {
        AppLayout,
    },
    props: {
        list: Array
    }
};
</script>
