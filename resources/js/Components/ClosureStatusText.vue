<script setup lang="ts">
import {CheckCircleIcon} from "@heroicons/vue/20/solid";

defineProps({
    closure_status: Number,
});
</script>

<template>
    <span
        :class="{'text-blue-600': [5,6].includes(closure_status), 'text-green-600': closure_status === 10,'text-red-600': closure_status <= -1 || closure_status === 3,'text-yellow-600': closure_status === 1}">
        {{
            {
                0: 'ยังไม่รายงานผล',
                1: 'ส่งปิดโครงแล้ว',
                3: 'นิสิตผู้เกี่ยวข้องไม่ได้ยืนยันในเวลาที่กำหนด',
                5: 'นิสิตผู้เกี่ยวข้องยืนยันแล้ว รอพิจารณา',
                6: 'นิสิตผู้เกี่ยวข้องยืนยันแล้ว แต่ยังไม่ส่งเอกสาร',
                10: 'อนุมัติ',
                '-1': 'ไม่อนุมัติ',
                '-2': 'ไม่อนุมัติ ให้แก้ไข',
                '-3': 'ไม่อนุมัติ และไม่ได้แก้ไขในเวลาที่กำหนด',
            }[closure_status] ?? closure_status
        }}
        <CheckCircleIcon v-if="closure_status === 10" class="inline-block h-4 w-4"/>
    </span>
</template>
