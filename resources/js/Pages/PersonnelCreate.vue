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

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8" @keyup.enter="submit">
            <jet-form-section @submit="submit">
                <template #title>Office</template>
                <template #description></template>
                <template #form>
                    <div class="col-span-2">
                        <jet-label for="year" value="Year (B.E.)"/>
                        <jet-input id="year" v-model.number="form.year" class="mt-1 block w-full" type="number"/>
                        <jet-input-error :message="errors.year" class="mt-2"/>
                    </div>
                    <div class="col-span-2">
                        <jet-label for="supervisor" value="Supervisor"/>
                        <jet-input id="supervisor" v-model.number="form.supervisor" class="mt-1 block w-full" type="number"/>
                        <jet-input-error :message="errors.supervisor" class="mt-2"/>
                    </div>
                    <div class="col-span-2">
                        <jet-label for="sequence" value="Sequence"/>
                        <jet-input id="sequence" v-model.number="form.sequence" class="mt-1 block w-full" type="number"/>
                        <jet-input-error v-if="errors.sequence" :message="errors.sequence" class="mt-2"/>
                        <p v-else class="mt-2 text-xs text-gray-500">
                            Set >=200 to hide
                        </p>
                    </div>
                    <div class="col-span-6">
                        <jet-label for="position" value="Position TH"/>
                        <jet-input id="position" v-model.trim="form.position" class="mt-1 block w-full" type="text"/>
                        <jet-input-error :message="errors.position" class="mt-2"/>
                    </div>
                    <div class="col-span-6">
                        <jet-label for="position_en" value="Position EN"/>
                        <jet-input id="position_en" v-model.trim="form.position_en" class="mt-1 block w-full" type="text"/>
                        <jet-input-error :message="errors.position_en" class="mt-2"/>
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
                        <jet-input-error :message="errors.department_id" class="mt-2"/>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submit="submit">
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
                        <jet-input-error :message="errors.name" class="mt-2"/>
                    </div>
                    <div class="col-span-3">
                        <jet-label for="name_en" value="Name EN (no title)"/>
                        <jet-input id="name_en" v-model.trim="form.name_en" class="mt-1 block w-full" type="text"/>
                        <jet-input-error :message="errors.name_en" class="mt-2"/>
                    </div>
                </template>
            </jet-form-section>

            <jet-section-border/>
            <jet-form-section @submit="submit">
                <template #title>Photo</template>
                <template #description>The file will be available publicly.</template>
                <template #form>
                    <div class="col-span-6 sm:flex gap-4">
                        <div v-if="item.photo_url" class="basis-36 lg:basis-48">
                            <img :src="item.photo_url"/>
                            <p class="text-xs text-gray-400">Uploaded photo</p>
                        </div>
                        <AttachmentBox v-model="form.attachment" accept="image/jpeg,image/webp,image/avif,image/png">
                            <template #description>
                                JPG/WebP up to 4 MB<br/>
                                Image larger than 50 kB will be automatically resized and converted to WebP.
                            </template>
                        </AttachmentBox>
                    </div>
                    <jet-input-error :message="errors.attachment" class="col-span-6"/>
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
import AttachmentBox from '@/Components/AttachmentBox.vue';
import {defineProps, reactive, ref, watch} from 'vue';
import {debounce} from 'lodash/function';
import {router} from '@inertiajs/vue3';

const props = defineProps({
    item: Object,
    static_departments: Array,
    errors: Object,
});

const searchResult = ref(null);
const keywordError = ref("");
const form = reactive({
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
    if (form.attachment && !(form.attachment.name.endsWith('.jpg') || form.attachment.name.endsWith('.jpeg') || form.attachment.name.endsWith('.webm') || form.attachment.name.endsWith('.avif') || form.attachment.type?.startsWith('image/'))) {
        props.errors.attachment = "Unsupported file type";
        return;
    }
    router.post(props.item.id
        ? route('personnels.update', {personnel: props.item.id})
        : route('personnels.store'),
        form
    )
};

const searchStudent = debounce(function (q) {
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
</script>
