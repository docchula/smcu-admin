<template>
    <jet-action-section>
        <template #title>
            โครงการ
        </template>

        <template #description>
            โครงการที่ฉันมีส่วนร่วม
            <InertiaLink :href="route('profile.printMyProjects')" class="text-green-600 ml-2 inline-block">
                <PrinterIcon class="inline-block h-5 w-5" /> พิมพ์
            </InertiaLink>
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                คุณมีส่วนร่วมใน {{ projects.length }} โครงการ
            </div>

            <div class="mt-5 space-y-6" v-if="projects.length > 0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-2 py-2 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            โครงการ
                        </th>
                        <th scope="col" class="px-2 py-2 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            บทบาท
                        </th>
                        <th scope="col" class="px-2 py-2 md:py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                            หน้าที่
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="participant in projects" :key="participant.id">
                        <td class="px-2 py-2 md:py-3">
                            <inertia-link :href="route('projects.show', {project: participant.project.id})">
                                <span class="text-xs text-gray-500 px-0.5">{{ participant.project.year }}-{{ participant.project.number }}</span>
                                {{ participant.project.name }}
                            </inertia-link>
                        </td>
                        <td class="px-2 py-2 md:py-3 text-sm">
                            {{ {organizer: 'ผู้รับผิดชอบ', staff: 'ผู้จัดกิจกรรม', attendee: 'ผู้เข้าร่วม'}[participant.type] ?? participant.type }}
                        </td>
                        <td class="px-2 py-2 md:py-3 text-gray-500 text-xs">
                            {{ participant.title ?? '-' }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </jet-action-section>
</template>

<script setup>
import {InertiaLink} from '@inertiajs/inertia-vue3'
import JetActionSection from '../../Jetstream/ActionSection.vue'
import {PrinterIcon} from '@heroicons/vue/24/solid'

const props = defineProps({
    projects: {
        type: Object,
        required: true,
    },
});
</script>
