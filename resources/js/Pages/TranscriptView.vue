<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Activity Transcript
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <Label>ชื่อ/อีเมล Docchula/เลขประจำตัวนิสิต</Label>
            <search-input v-model="searchKeyword" :status="searchMessage"/>

            <div v-if="user" class="my-4 p-4 flex gap-4 items-center shadow overflow-hidden border-b border-gray-200 sm:rounded-lg bg-white">
                <div class="items-center">
                    <UserIcon class="h-10 w-10 text-gray-400"/>
                </div>
                <div class="items-center">
                    <p class="">{{ user.name }}</p>
                    <p class="mt-1 text-sm text-gray-500">เลขประจำตัวนิสิต {{ user.student_id }}</p>
                </div>
                <div class="flex-auto items-center text-right">
                    <Link :href="route('transcript.print', {user: user.id})" class="text-green-600 ml-2 inline-block">
                        <PrinterIcon class="inline-block h-5 w-5"/>
                        พิมพ์
                    </Link>
                </div>
            </div>
            <ActivityTranscriptTable :transcript="transcript" v-if="user && transcript"
                                     class="overflow-hidden shadow sm:rounded-lg"/>
        </div>
    </app-layout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchInput from '@/Components/SearchInput.vue';
import Label from "@/Jetstream/Label.vue";
import {Link, router} from '@inertiajs/vue3';
import {UserIcon} from '@heroicons/vue/20/solid';
import {ref, watch} from 'vue';
import {debounce} from 'lodash';
import {PrinterIcon} from "@heroicons/vue/24/solid";
import ActivityTranscriptTable from "@/Components/ActivityTranscriptTable.vue";

const props = defineProps({
    keyword: String,
    user: Object,
    transcript: Array,
    static_departments: Array,
});
const searchKeyword = ref(props.keyword ?? '');
const searchMessage = ref('');

const search = () => {
    router.get(route('transcript.index'), {search: searchKeyword.value}, {
        preserveState: true
    });
    searchMessage.value = "";
};
const debouncedSearch = debounce(search, 500);
watch(searchKeyword, () => {
    searchMessage.value = 'Typing...';
    debouncedSearch();
});
</script>
