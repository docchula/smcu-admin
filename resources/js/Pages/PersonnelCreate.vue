<template>
    <app-layout>
        <template #header>
            <inertia-link :href="route('personnels.index', {year: item.year})"
                          class="mb-4 block flex items-center text-gray-700">
                <svg class="inline h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path class="text-gray-500" d="M7 16l-4-4m0 0l4-4m-4 4h18" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
                <p>Personnel, Year {{ item.year }}</p>
            </inertia-link>
            <h2 v-if="item.id" class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Personnel Record #{{ item.id }}
            </h2>
            <h2 v-else class="font-semibold text-xl text-gray-800 leading-tight">Create Personnel Record</h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <jet-form-section>
                <template #title>Office</template>
                <template #description></template>
                <template #form>
                    <div class="col-span-2">
                        <jet-label for="year" value="Year (B.E.)"/>
                        <jet-input id="year" v-model.number="form.year" class="mt-1 block w-full" type="number"/>
                        <jet-input-error :message="form.errors.year" class="mt-2"/>
                    </div>
                    <div class="col-span-2">
                        <jet-label for="supervisor" value="Supervisor"/>
                        <jet-input id="supervisor" v-model.number="form.supervisor" class="mt-1 block w-full" type="number"/>
                        <jet-input-error :message="form.errors.supervisor" class="mt-2"/>
                    </div>
                    <div class="col-span-2">
                        <jet-label for="sequence" value="Sequence"/>
                        <jet-input id="sequence" v-model.number="form.sequence" class="mt-1 block w-full" type="number"/>
                        <jet-input-error :message="form.errors.sequence" class="mt-2"/>
                    </div>
                    <div class="col-span-6">
                        <jet-label for="position" value="Position TH"/>
                        <jet-input id="position" v-model.trim="form.position" class="mt-1 block w-full" type="text"/>
                        <jet-input-error :message="form.errors.position" class="mt-2"/>
                    </div>
                    <div class="col-span-6">
                        <jet-label for="position_en" value="Position EN"/>
                        <jet-input id="position_en" v-model.trim="form.position_en" class="mt-1 block w-full" type="text"/>
                        <jet-input-error :message="form.errors.position_en" class="mt-2"/>
                    </div>
                    <div class="col-span-6">
                        <label class="block text-sm font-medium text-gray-700" for="department">Department</label>
                        <select id="department" v-model="form.department_id"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                required>
                            <!-- hide if sequence >= 200 (deprecated values) -->
                            <option v-for="department in static_departments"
                                    v-show="department.sequence < 200 || department.id === form.department_id"
                                    v-bind:key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                        <jet-input-error :message="form.errors.department_id" class="mt-2"/>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section>
                <template #title>Student</template>
                <template #description>
                    For current students, enter email or student ID to search.
                </template>
                <template #form>
                    <div class="col-span-6">
                        <jet-label for="email" value="Email"/>
                        <jet-input id="email" v-model.trim="form.email" class="mt-1 block w-full" type="text"/>
                        <jet-input-error v-if="keywordError" :message="keywordError" class="mt-2"/>
                        <p v-else-if="searchResult" class="mt-2 text-xs text-green-500">Found {{ searchResult.nickname }}!</p>
                    </div>
                    <div class="col-span-3">
                        <jet-label for="name" value="Name TH (including title)"/>
                        <jet-input id="name" v-model.trim="form.name" class="mt-1 block w-full" type="text"/>
                        <jet-input-error :message="form.errors.name" class="mt-2"/>
                    </div>
                    <div class="col-span-3">
                        <jet-label for="name_en" value="Name EN (no title)"/>
                        <jet-input id="name_en" v-model.trim="form.name_en" class="mt-1 block w-full" type="text"/>
                        <jet-input-error :message="form.errors.name_en" class="mt-2"/>
                    </div>
                </template>
                <template #actions>
                    <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                        Saved.
                    </jet-action-message>
                    <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" type="submit">
                        Save
                    </jet-button>
                </template>
            </jet-form-section>

            <jet-section-border/>
            <jet-form-section @submit="submit">
                <template #title>Photo</template>
                <template #description>The file will be available publicly.</template>
                <template #form>
                    <div class="col-span-6 sm:flex gap-4">
                        <div v-if="item.photo_url" class="basis-36">
                            <img :src="item.photo_url"/>
                        </div>
                        <div class="flex-auto">
                            <p v-if="form.attachment" class="">
                                <svg class="h-5 w-5 text-green-600 inline mr-1" fill="currentColor" viewBox="0 0 20 20"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd"
                                          d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z"
                                          fill-rule="evenodd"/>
                                </svg>
                                {{ form.attachment.name }}
                                <svg class="h-5 w-5 text-gray-500 cursor-pointer ml-4 inline" fill="currentColor" viewBox="0 0 20 20"
                                     xmlns="http://www.w3.org/2000/svg" @click="form.attachment = null">
                                    <path clip-rule="evenodd"
                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                          fill-rule="evenodd"/>
                                </svg>
                            </p>
                            <div v-cloak v-else class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
                                 @drop.prevent="dropFile"
                                 @dragover.prevent>
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"/>
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"
                                            for="file-upload">
                                            <span>Upload a file</span>
                                            <input id="file-upload" accept="image/jpeg,image/webp,image/avif" class="sr-only"
                                                   type="file"
                                                   @input="form.attachment = $event.target.files[0]">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        JPG/WebP up to 500 KB
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <jet-input-error :message="form.errors.attachment" class="col-span-6"/>
                </template>
                <template #actions>
                    <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                        Saved.
                    </jet-action-message>
                    <progress v-if="form.progress" :value="form.progress.percentage" class="mr-3" max="100">
                        {{ form.progress.percentage }}%
                    </progress>

                    <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" type="submit">
                        Save
                    </jet-button>
                </template>
            </jet-form-section>
        </div>
    </app-layout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import JetActionMessage from '@/Jetstream/ActionMessage.vue'
import JetButton from '@/Jetstream/Button.vue'
import JetFormSection from '@/Jetstream/FormSection.vue'
import JetInput from '@/Jetstream/Input.vue'
import JetInputError from '@/Jetstream/InputError.vue'
import JetLabel from '@/Jetstream/Label.vue'
import JetSectionBorder from '@/Jetstream/SectionBorder.vue'
import _ from "lodash";
import {useForm} from "@inertiajs/inertia-vue3";
import {ref, watch} from "vue";

const props = defineProps({
    item: Object,
    static_departments: Array,
});

const searchResult = ref(null);
const keywordError = ref("");
const form = useForm({
    _method: props.item.id ? 'PUT' : 'POST',
    email: props.item.email ?? "",
    name: props.item.name,
    name_en: props.item.name_en,
    position: props.item.position,
    position_en: props.item.position_en,
    department_id: props.item.department_id,
    year: props.item.year ?? ((new Date()).getFullYear() + 543),
    supervisor: props.item.supervisor,
    sequence: props.item.sequence ?? 10,
    attachment: null,
});

const submit = () => {
    if (form.attachment && !(form.attachment.name.endsWith('.jpg') || form.attachment.name.endsWith('.jpeg') || form.attachment.name.endsWith('.webm') || form.attachment.name.endsWith('.avif'))) {
        form.errors.attachment = "Unsupported file type";
    }
    form.post(props.item.id
        ? route('personnels.update', {personnel: props.item.id})
        : route('personnels.store')
    )
};

const searchStudent = _.debounce(function (q) {
    keywordError.value = 'Searching...';
    axios.get(route('personnels.searchStudent', {q})).then((response) => {
        keywordError.value = '';
        searchResult.value = response.data;
        form.name = response.data.name;
        form.name_en = response.data.name_en;
        form.email = response.data.email;
    }).catch((error) => {
        if (error.response.data.error) {
            keywordError.value = error.response.data.error;
        } else {
            keywordError.value = 'Error: ' + error;
        }
        searchResult.value = null;
    })
}, 500);

watch(() => form.email, async (newValue) => {
    keywordError.value = "Typing...";
    if (!newValue || newValue === "") {
        keywordError.value = '';
        searchResult.value = null;
        return;
    } else if (newValue.length < 3) {
        keywordError.value = 'Please enter email or student ID.';
        return;
    }
    searchStudent(newValue);
});
const dropFile = (e) => {
    // from https://www.raymondcamden.com/2019/08/08/drag-and-drop-file-upload-in-vuejs
    let droppedFiles = e.dataTransfer.files;
    if (!droppedFiles) return;
    // this tip, convert FileList to array, credit: https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
    ([...droppedFiles]).slice(0, 1).forEach(f => {
        form.attachment = f;
    });
};
</script>
