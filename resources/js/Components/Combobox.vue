<template>
    <Combobox v-model="selected" :name="name">
        <ComboboxInput
            :id="name"
            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            :placeholder="placeholder"
            @change="query = $event.target.value"
        />
        <ComboboxOptions class="border border-indigo-200">
            <ComboboxOption v-for="option in filteredOption" :key="option" :value="option">
                <li class="relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900 hover:bg-indigo-600 hover:text-white" role="option">
                    <span class="block truncate">{{ option }}</span>
                </li>
            </ComboboxOption>
            <ComboboxOption v-if="allowCustom" :value="queryOption"><!-- if queryOption -->
                <li class="relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900 hover:bg-indigo-600 hover:text-white" role="option">
                    <span class="block"><span class="text-blue-700">กดเพื่อเลือก</span> "{{ query }}"</span>
                </li>
            </ComboboxOption>
        </ComboboxOptions>
    </Combobox>
</template>

<script setup>
import {computed, ref} from 'vue'
import {Combobox, ComboboxInput, ComboboxOption, ComboboxOptions} from '@headlessui/vue'

const props = defineProps({
    modelValue: String,
    name: String,
    placeholder: String,
    options: Array,
    allowCustom: Boolean,
});
const emit = defineEmits(['update:modelValue']);
const selected = computed({
    get() {
        return props.modelValue
    },
    set(value) {
        emit('update:modelValue', value)
    }
});

const query = ref('')

const queryOption = computed(() => {
    return query.value === '' ? null : query.value
})

const filteredOption = computed(() =>
    query.value === ''
        ? props.options.slice(0, 7)
        : props.options.filter((option) => {
            return option.toLowerCase().includes(query.value.toLowerCase())
        }).slice(0, 7)
)
</script>
