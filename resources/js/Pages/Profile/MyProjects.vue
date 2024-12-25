<template>
    <ActionSection>
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
                คุณมีส่วนร่วมใน {{ transcript.length }} โครงการ
            </div>

            <ActivityTranscriptTable v-if="transcript && transcript.length > 0" :transcript="transcript"
                                     class="mt-4"/>
        </template>
    </ActionSection>
</template>

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import {PrinterIcon} from '@heroicons/vue/24/solid';
import ActivityTranscriptTable from "@/Components/ActivityTranscriptTable.vue";
import ActionSection from '@/Jetstream/ActionSection.vue';
import {TranscriptItem} from '@/types';

const props = defineProps({
    transcript: {
        type: Array<TranscriptItem>,
        required: true,
    },
});
</script>
