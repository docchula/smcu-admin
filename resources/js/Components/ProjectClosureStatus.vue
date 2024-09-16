<template>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                ความคืบหน้าการตรวจสอบรับรองรายชื่อนิสิตผู้เกี่ยวข้อง
            </h3>
        </div>
        <div class="flex border-t border-gray-200">
            <div class="flex-auto px-4 py-4 sm:px-6">
                <div class="flex justify-between mb-1">
                    <span class="text-base font-medium text-yellow-600">ผู้รับผิดชอบ</span>
                    <span class="text-sm font-medium">
                        <FaceSmileIcon v-if="organizerPercentage >= 100"
                                       class="w-5 h-5 inline-block text-yellow-600"/>
                        {{ organizerPercentage }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-yellow-600 h-2.5 rounded-full" :style="
                    {'width': organizerPercentage+'%'}"></div>
                </div>
            </div>
            <div v-if="staff.length > 0" class="flex-auto px-4 py-4 sm:px-6">
                <div class="flex justify-between mb-1">
                    <span class="text-base font-medium text-cyan-600">ผู้ปฏิบัติงาน</span>
                    <span class="text-sm font-medium">
                        <FaceSmileIcon v-if="staffPercentage >= 50"
                                       class="w-5 h-5 inline-block text-cyan-600"/>
                        {{ staffPercentage }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-cyan-600 h-2.5 rounded-full" :style="
                    {'width': staffPercentage+'%'}"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {computed} from 'vue';
import {FaceSmileIcon} from '@heroicons/vue/24/outline';

const props = defineProps({
    participants: Array
});
const organizerPercentage = computed(() => {
    const organizers = props.participants.filter(p => p.type === 'organizer');
    return Math.round(organizers.filter(p => p.verify_status).length / organizers.length * 100);
});
const staff = computed(() => props.participants.filter(p => p.type === 'staff'));
const staffPercentage = computed(() => {
    return Math.round(staff.value.filter(p => p.verify_status).length / staff.value.length * 100);
});
</script>
