<script setup lang="ts">
import {CheckCircleIcon} from "@heroicons/vue/20/solid";

defineProps({
    closure_status: Number,
});
</script>

<template>
    <span
        :class="{'text-blue-600': closure_status === 5, 'text-green-600': closure_status === 10,'text-red-600': closure_status <= -1,'text-yellow-600': closure_status === 1}">
        {{
            {
                0: 'ยังไม่รายงานผล',
                1: 'ส่งปิดโครงแล้ว',
                5: 'นิสิตผู้เกี่ยวข้องยืนยันแล้ว รอพิจารณา',
                10: 'อนุมัติ',
                '-1': 'ไม่อนุมัติ',
                '-2': 'ไม่อนุมัติ ให้แก้ไข',
            }[closure_status] ?? closure_status
        }}
        <CheckCircleIcon v-if="closure_status === 10" class="inline-block h-4 w-4"/>
    </span>
</template>
