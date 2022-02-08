<template>
    <app-layout>
        <template #header>
            <inertia-link :href="route('projects.index')" class="mb-4 block flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                โครงการ
            </inertia-link>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                โครงการที่ {{ item.year }}-{{ item.number }}
            </h2>
        </template>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <p class="mb-2 px-4 sm:px-0 font-semibold text-3xl text-gray-800 leading-tight">
                {{ item.name }}
            </p>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        ข้อมูลพื้นฐาน
                        <inertia-link :href="route('projects.edit', {project: item.id})" class="text-yellow-600 hover:text-yellow-900 text-sm ml-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                            แก้ไข
                        </inertia-link>
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl class="grid grid-cols-2 lg:grid-cols-3">
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">อาจารย์ที่ปรึกษา</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ item.advisor }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">สร้างโดย</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ item.user.name }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">หน่วยงาน</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ item.department.name }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ปีวาระ</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ item.year }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ลักษณะกิจกรรม</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ {once: 'กิจกรรมครั้งเดียว', longitudinal: 'กิจกรรมระยะยาว', purchase: 'โครงการจัดซื้อ'}[item.type] }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ประเภท</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ ['โครงการครั้งแรก', 'โครงการต่อเนื่อง'][item.recurrence] }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">วันที่เริ่ม</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ item.period_start }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">วันที่สิ้นสุด</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ item.period_end }}</dd>
                        </div>
                        <div v-if="item.approval_document_id" class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">หนังสือขออนุมัติ</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <inertia-link :href="route('documents.show', {document: item.approval_document_id})">
                                    ดูเอกสาร
                                </inertia-link>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        เหตุผล และวัตถุประสงค์
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl class="">
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">หลักการและเหตุผล</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-wrap">
                                {{ item.background }}
                            </dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">วัตถุประสงค์</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-wrap">
                                {{ item.aims }}
                            </dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ผลที่คาดว่าจะได้รับ</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-wrap">
                                {{ item.outcomes }}
                            </dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">เป้าหมาย และวิธีการประเมิน</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <ol v-if="item.objectives.length" class="list-decimal">
                                    <li v-for="objective in item.objectives">
                                        <u>เป้าหมาย</u>&nbsp;{{ objective.goal }}<br/>
                                        <u>วิธีการประเมิน</u>&nbsp;{{ objective.method }}
                                    </li>
                                </ol>
                                <span v-else>-</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            <div v-if="item.expense.length" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        ทรัพยากร
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">งบประมาณ</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <table v-if="item.expense.length > 0" class="divide-y divide-gray-200">
                                    <thead>
                                    <tr>
                                        <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                            รายการ
                                        </th>
                                        <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                            ประเภทรายจ่าย
                                        </th>
                                        <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                            แหล่งงบประมาณ
                                        </th>
                                        <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                            จำนวนเงิน (บ.)
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="e in item.expense">
                                        <td class="p-2">
                                            {{ e.name }}
                                        </td>
                                        <td class="p-2">
                                            {{ e.type }}
                                        </td>
                                        <td class="p-2">
                                            {{ e.source }}
                                        </td>
                                        <td class="p-2 whitespace-nowrap">
                                            {{ e.amount.toLocaleString('th-TH') }}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <span v-else>-</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            <div v-if="item.documents.length > 0" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        เอกสารที่เกี่ยวข้อง
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-4 sm:px-6">
                        <table class="divide-y divide-gray-200">
                            <thead>
                            <tr>
                                <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    เลขที่
                                </th>
                                <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    วันที่
                                </th>
                                <th scope="col" class="px-2 pb-1 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    หัวเรื่อง
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="e in item.documents">
                                <td class="p-2">
                                    <inertia-link :href="route('documents.show', {document: e.id})" class="text-green-500">
                                        {{ e.number }}<span v-if="e.number_to">-{{ e.number_to }}</span><span class="text-sm">/{{ e.year }}</span>
                                    </inertia-link>
                                </td>
                                <td class="p-2">
                                    <span v-if="e.created_at">{{ e.created_at }}</span>
                                    <span v-else>-</span>
                                </td>
                                <td class="p-2">
                                    {{ e.title }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div v-if="participantsGrouped" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        นิสิตผู้เกี่ยวข้อง
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div v-for="(participants, type) in participantsGrouped" class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">{{ {organizer: 'ผู้รับผิดชอบ', staff: 'ผู้จัดกิจกรรม', attendee: 'ผู้เข้าร่วม'}[type] }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <ol class="list-decimal">
                                    <li v-for="e in participants">
                                        {{ e.user.name }}
                                        <span class="ml-4 text-gray-700">เลขประจำตัวนิสิต {{ e.user.student_id }}</span>
                                        <span v-if="e.title" class="ml-4 px-1.5 py-0.5 rounded bg-gray-200">{{ e.title }}</span>
                                    </li>
                                </ol>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            <div v-if="!item.approval_document_id" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        สร้างเอกสารขออนุมัติโครงการ
                    </h3>
                </div>
                <div class="border-t border-gray-200 p-4 sm:px-6">
                    <a :href="route('projects.generateApprovalDocument', {project: item.id})"
                       class="inline-block items-center px-4 py-2 mb-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">สร้างเอกสารขออนุมัติโครงการ</a>
                    <p>เมื่อดาวน์โหลดเอกสารแล้ว กรุณาแก้ไขเพิ่มเติมข้อมูลต่าง ๆ ให้ครบถ้วน และปรับแก้การจัดหน้าให้เรียบร้อยก่อนส่งขออนุมัติตามลำดับขั้น</p>
                    <p v-if="item.expense.filter(e => e.source === 'ฝ่ายกิจการนิสิต').length > 0" class="text-yellow-700">โครงการที่ใช้งบประมาณฝ่ายกิจการนิสิต
                        ควรนำร่างเอกสารปรึกษาเจ้าหน้าที่ฝ่ายกิจการนิสิต เพื่อตรวจสอบความถูกต้องก่อนส่งขออนุมัติ</p>
                </div>
            </div>
            <p class="mt-1 text-sm text-gray-500 text-right">
                สร้างเมื่อ {{ item.created_at }}
                <span v-if="item.updated_at && (item.created_at !== item.updated_at)">
                    &emsp;แก้ไขเมื่อ {{ item.updated_at }}
                </span>
            </p>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'

export default {
    components: {
        AppLayout,
    },
    computed: {
        participantsGrouped() {
            return (this.item.participants.length > 0) ? _.groupBy(this.item.participants, 'type') : null;
        }
    },
    props: {
        item: Object,
    },
};
</script>
