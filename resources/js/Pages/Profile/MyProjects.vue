<template>
    <jet-action-section>
        <template #title>
            โครงการ
        </template>

        <template #description>
            โครงการที่ฉันมีส่วนร่วม
            <Link :href="route('profile.printMyProjects')" class="text-green-600 ml-2 inline-block">
                <PrinterIcon class="inline-block h-5 w-5" /> พิมพ์
            </Link>
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                คุณมีส่วนร่วมใน {{ participants.length }} โครงการ
            </div>

            <ActivityTranscriptTable v-if="participants && participants.length > 0" :participants="participants"
                                     class="mt-4"/>
        </template>
    </jet-action-section>
</template>

<script setup>
import {Link} from '@inertiajs/vue3'
import JetActionSection from '../../Jetstream/ActionSection.vue'
import {PrinterIcon} from '@heroicons/vue/24/solid'
import ActivityTranscriptTable from "@/Components/ActivityTranscriptTable.vue";

const props = defineProps({
    participants: {
        type: Array,
        required: true,
    },
});
</script>
