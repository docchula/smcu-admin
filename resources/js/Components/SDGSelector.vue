<script setup lang="ts">
const model = defineModel<number[] | null>();
const props = defineProps<{
    view?: boolean;
}>();
const toggleSelection = (i: number) => {
    if (props.view) return;
    if (model.value?.includes(i)) {
        model.value = model.value.filter((j) => j !== i);
    } else if (model.value) {
        model.value = [...model.value, i].sort((a, b) => a - b);
    } else {
        model.value = [i];
    }
};
</script>
<template>
    <div class="space-y-4">
        <p v-if="model?.length > 0">สอดคล้องกับเป้าหมายข้อที่ {{ model.join(' ') }}</p>
        <p v-else>ไม่มีเป้าหมายที่สอดคล้อง</p>
        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            <template v-for="i in 17">
                <div @click="toggleSelection(i)" v-if="!view || model?.includes(i)" class="item transition p-0.5"
                     :class="{'opacity-30 hover:opacity-50': !model?.includes(i),
                     'ring ring-gray-300 hover:opacity-80': model?.includes(i) && !view, 'cursor-pointer': !view}">
                    <img :src="'/assets/sdgs/' + String(i).padStart(2, '0') + '.svg'" :alt="'SDG '+ i"
                         class="w-full max-w-28 lg:max-w-none opacity-100 m-auto"/>
                </div>
            </template>
        </div>
    </div>
</template>
