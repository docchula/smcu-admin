<template>
    <div class="flex-auto" @paste="onPaste">
        <p v-if="modelValue" class="">
            <PaperClipIcon class="h-5 w-5 text-green-600 inline mr-1"/>
            {{ modelValue.name }} ({{ modelValue.size / 1000 }} kB)
            <XMarkIcon class="h-5 w-5 text-gray-500 cursor-pointer ml-4 inline" @click="emit('update:modelValue', null)"/>
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
                <div class="text-sm text-gray-600">
                    <label
                        class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        <input :accept="accept" class="sr-only"
                               type="file"
                               @input="emit('update:modelValue', $event.target.files[0])">
                        <span>Upload a file</span>
                    </label>, drag and drop, or paste here.
                </div>
                <p class="text-xs text-gray-500">
                    {{ description }}
                    <slot name="description"></slot>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import {PaperClipIcon, XMarkIcon} from '@heroicons/vue/20/solid';

const props = defineProps({
    modelValue: {
        required: true,
    },
    accept: {
        // type: String|Array,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
const emit = defineEmits(['update:modelValue']);

const selectFile = (file) => {
    // Validate file type
    const acceptableFileTypes = Array.isArray(props.accept) ? props.accept : props.accept.split(',').map(type => type.trim());
    if (acceptableFileTypes.map(type => {
        if (type.startsWith('.')) {
            return file.name.toLowerCase().endsWith(type.toLowerCase());
        } else if (type.endsWith('/*')) {
            return file.type.startsWith(type.slice(0, -1));
        } else {
            return file.type === type;
        }
    }).filter(isValid => isValid).length === 0) {
        // No match
        return;
    }
    emit('update:modelValue', file);
};

const dropFile = (e) => {
    // from https://www.raymondcamden.com/2019/08/08/drag-and-drop-file-upload-in-vuejs
    let droppedFiles = e.dataTransfer.files;
    if (!droppedFiles) return;
    // this tip, convert FileList to array, credit: https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
    ([...droppedFiles]).slice(0, 1).forEach(f => {
        selectFile(f);
    });
};

const onPaste = async (e) => {
    e.preventDefault();
    const clipboardItems = typeof navigator?.clipboard?.read === 'function' ? await navigator.clipboard.read() : e.clipboardData.files;

    for (const clipboardItem of clipboardItems) {
        if (clipboardItem.type) {
            // For files from `e.clipboardData.files`.
            selectFile(clipboardItem);
        } else {
            // For files from `navigator.clipboard.read()`.
            const fileTypes = clipboardItem.types?.filter(type => type.contains('/'))
            for (const fileType of fileTypes) {
                selectFile(await clipboardItem.getType(fileType));
            }
        }
    }
};
</script>
