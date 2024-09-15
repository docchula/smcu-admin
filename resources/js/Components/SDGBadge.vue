<script setup lang="ts">
const props = defineProps<{ value: number[] | null }>();
</script>
<template>
    <div class="inline-block p-0.5 text-xs bg-sky-100 rounded-sm text-sky-600" v-if="value">
        SDGs:
        <span v-for="i in value" :key="i" :class="'sdg-goal-' + i" class="ml-0.5 px-1 text-white text-center inline-block min-w-4">
            {{ i }}
        </span>
    </div>
</template>
<style scoped>
.sdg-goal-1 {
    background-color: #E5233D;
}

.sdg-goal-2 {
    background-color: #DDA73A;
}

.sdg-goal-3 {
    background-color: #4CA146;
}

.sdg-goal-4 {
    background-color: #C5192D;
}

.sdg-goal-5 {
    background-color: #EF402C;
}

.sdg-goal-6 {
    background-color: #27BFE6;
}

.sdg-goal-7 {
    background-color: #FBC412;
}

.sdg-goal-8 {
    background-color: #A31C44;
}

.sdg-goal-9 {
    background-color: #F26A2D;
}

.sdg-goal-10 {
    background-color: #E01483;
}

.sdg-goal-11 {
    background-color: #F89D2A;
}

.sdg-goal-12 {
    background-color: #BF8D2C;
}

.sdg-goal-13 {
    background-color: #407F46;
}

.sdg-goal-14 {
    background-color: #1F97D4;
}

.sdg-goal-15 {
    background-color: #59BA48;
}

.sdg-goal-16 {
    background-color: #126A9F;
}

.sdg-goal-17 {
    background-color: #13496B;
}
</style>
