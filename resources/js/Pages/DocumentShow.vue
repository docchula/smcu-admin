<template>
    <app-layout>
        <template #header>
            <inertia-link :href="route('documents.index')" class="mb-4 block flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                สารบรรณ
            </inertia-link>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                หนังสือสพจ. ที่ {{ item.number }}<span v-if="item.number_to">-{{ item.number_to }}</span>/{{ item.year }}
            </h2>
        </template>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="mb-2">
                <span class="font-semibold text-3xl text-gray-800 leading-tight">
                    {{ item.title }}
                </span>
            </div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        ข้อมูลพื้นฐาน
                        <inertia-link v-if="item.can['update-document']" :href="route('documents.edit', {document: item.id})" class="text-yellow-600 hover:text-yellow-900 text-sm ml-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                            แก้ไข
                        </inertia-link>
                    </h3>
                    <p v-if="!item.can['update-document']" class="mt-1 max-w-2xl text-sm text-gray-500">หากต้องการแก้ไขข้อมูล กรุณาติดต่อผู้ดูแลระบบ</p>
                </div>
                <div class="border-t border-gray-200">
                    <dl class="grid grid-cols-2 text-gray-900">
                        <div class="px-3 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6" v-if="item.recipient">
                            <dt class="text-sm font-medium text-gray-500">ผู้รับ</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.recipient }}</dd>
                        </div>
                        <div class="px-3 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">หน่วยงาน</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2" :class="{'text-gray-400': item.department_id === 33}">
                                {{ item.department.name }}
                            </dd>
                        </div>
                        <div class="px-3 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6" v-if="item.project">
                            <dt class="text-sm font-medium text-gray-500">โครงการ</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                <inertia-link :href="route('projects.show', {project: item.project_id})" class="text-yellow-600 hover:text-yellow-900">{{ item.project.name }}</inertia-link>
                            </dd>
                        </div>
                        <div class="px-3 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ผู้รับผิดชอบ</dt>
                            <dd v-if="item.user" class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.user.name }}</dd>
                            <dd v-else class="mt-1 text-sm text-gray-400 sm:mt-0 sm:col-span-2">N/A</dd>
                        </div>
                        <div v-if="item.tag" class="px-3 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ประเภท</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ {approval: "ขออนุมัติดำเนินโครงการ", summary: "สรุปผลการดำเนินโครงการ"}[item.tag] ?? item.tag }}</dd>
                        </div>
                        <div class="px-3 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">สร้างเมื่อ</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.created_at }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
            <div v-if="item.can['update-document']" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        เอกสารต้นฉบับ
                    </h3>
                </div>
                <div class="border-t border-gray-200 p-4 sm:px-6">
                    <a v-if="item.has_attachment" :href="route('documents.download', {document: item.id})"
                       class="inline-block items-center px-4 py-2 mb-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">ดาวน์โหลดร่างเอกสาร</a>
                    <p v-else class="text-gray-500">ไม่พบไฟล์</p>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'

export default {
    components: {
        AppLayout,
    },
    props: {
        item: Object,
    },
};
</script>
