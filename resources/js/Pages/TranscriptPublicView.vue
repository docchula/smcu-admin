<template>
    <Head :title="user_name"/>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-6 space-y-6 bg-white sm:overflow-hidden shadow-sm sm:rounded-lg component-dialog">
                <h2 class="mb-4 text-3xl text-gray-900">
                    ระเบียนประวัติการเข้าร่วมกิจกรรมนอกหลักสูตร
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pb-4 border-b border-gray-100">
                    <div class="space-y-2">
                        <InputLabel>นิสิต</InputLabel>
                        {{ user_name }}
                    </div>
                </div>
                <ActivityTranscriptTable v-if="transcript" :transcript="transcript" :hide-tick="true"
                                         class="overflow-hidden shadow sm:rounded-lg"/>
            </div>
        </div>
        <a href="https://www.md.chula.ac.th" target="_blank">
            <p class="mt-4 text-sm text-gray-500 max-w-5xl mx-auto sm:px-6 lg:px-8">คณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย</p>
        </a>
    </div>
</template>

<script setup>
import ActivityTranscriptTable from "@/Components/ActivityTranscriptTable.vue";
import InputLabel from "../../../vendor/laravel/jetstream/stubs/inertia/resources/js/Components/InputLabel.vue";
import {Head} from "@inertiajs/vue3";

defineProps({
    user_name: String,
    transcript: Array,
});
</script>
<style>
body {
    @apply bg-gray-200;
}
</style>
