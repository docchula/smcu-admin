<template>
    <AppLayout>
        <Head :title="'หนังสือสพจ. ที่ '+item.number+'/'+item.year"/>
        <template #header>
            <Link :href="route('documents.index')" class="mb-4 flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                สารบรรณ
            </Link>
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
                        <Link v-if="can['update-document']" :href="route('documents.edit', {document: item.id})"
                              class="text-yellow-600 hover:text-yellow-900 text-sm ml-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                            แก้ไข
                        </Link>
                    </h3>
                    <p v-if="!can['update-document']" class="mt-1 max-w-2xl text-sm text-gray-500">หากต้องการแก้ไขข้อมูล กรุณาติดต่อผู้ดูแลระบบ</p>
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
                                <Link :href="route('projects.show', {project: item.project_id})" class="text-yellow-600 hover:text-yellow-900">
                                    {{ item.project.name }}
                                </Link>
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
                        <div v-if="item.status" class="px-3 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">สถานะการพิจารณา (DocHub)</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2"
                                :class="{'text-red-500': item.status !== 'APPROVED'}">{{
                                    {APPROVED: "อนุมัติ", REJECTED: "ปฏิเสธ", UNDELIVERED: "ที่อยู่อีเมลผิด"}[item.status] ?? item.status
                                }}
                            </dd>
                        </div>
                        <div class="px-3 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">สร้างเมื่อ</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.created_at }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
            <div v-if="can['download-document']" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        เอกสารต้นฉบับ
                    </h3>
                </div>
                <div class="border-t border-gray-200 p-4 sm:px-6">
                    <a v-if="has_attachment" :href="route('documents.download', {document: item.id})"
                       class="inline-block items-center px-4 py-2 mb-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">ดาวน์โหลดร่างเอกสาร</a>
                    <p v-else class="text-gray-500">ไม่พบไฟล์</p>
                </div>
            </div>

            <div v-if="has_approved" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        เอกสารได้รับการอนุมัติแล้ว
                    </h3>
                </div>
                <div class="border-t border-gray-200 p-4 sm:px-6">
                    <Link :href="route('documents.downloadApproved', {document: item.id})"
                                  class="inline-block items-center px-4 py-2 mb-2 bg-blue-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                        ดูเอกสาร
                    </Link>
                    <a :href="route('documents.downloadApproved', {document: item.id, download: true})">
                        <ArrowDownTrayIcon class="inline-block ml-2 h-4 w-4 text-gray-400"/>
                    </a>
                </div>
            </div>
            <!-- File Naming Instruction -->
            <div v-else-if="can['update-document']" class="bg-blue-100 border-blue-500 text-blue-600 border-l-4 rounded p-4 mb-6" role="alert">
                <p class="font-bold">
                    ขั้นตอนต่อไป : ส่งให้ผู้เกี่ยวข้องลงลายมือชื่อ
                </p>
                <p class="mb-1">
                    กรอกเลขที่หนังสือ "สพจ. {{ item.number }}/{{ item.year }}" ลงในหัวหนังสือ แล้วส่งให้ผู้เกี่ยวข้องต่อไป<br/>
                    กรณีส่งลงลายมือชื่อผ่านทางระบบอิเล็กทรอนิกส์ กรุณาตั้งชื่อไฟล์ในรูปแบบ “สพจ xx-256x หัวเรื่อง” เช่น (สามารถแก้ไขได้ตามความเหมาะสม)
                </p>
                <span class="text-lg py-1 px-2 bg-blue-200 text-blue-600">
                    สพจ <span class="font-mono">{{ item.number }}-{{ item.year }}</span> {{ item.title.substring(0, 90) }}<span class="font-mono">.pdf</span>
                </span>
                <template v-if="signers && signers.length > 0">
                    <p class="mt-2">
                        และส่งให้ผู้เกี่ยวข้องลงลายมือชื่อตามลำดับ ซึ่งอาจมีดังนี้
                    </p>
                    <table>
                        <tr v-for="signer in signers">
                            <td class="px-1">-</td>
                            <td class="px-2">{{ signer.name }}</td>
                            <td class="px-2">{{ signer.position }}</td>
                            <td class="px-2 font-mono">{{ signer.email }}</td>
                        </tr>
                        <tr v-if="item.project_id">
                            <td class="px-1">-</td>
                            <td class="px-2">{{ item.project.advisor }}</td>
                            <td class="px-2" colspan="2">อาจารย์ที่ปรึกษาโครงการ</td>
                        </tr>
                        <tr v-if="item.tag === 'approval'">
                            <td class="px-1">-</td>
                            <td class="px-2" colspan="3">แล้วส่งต่อไฟล์ที่ผู้เกี่ยวข้องลงลายมือชื่อผ่านระบบแล้ว ไปยังฝ่ายกิจการนิสิต
                                (studentmd@chula.md) โดยขอให้เจ้าหน้าที่ฝ่ายกิจการนิสิตพิมพ์และนำเรียนรองคณบดีลงลายมือชื่อบนกระดาษ
                            </td>
                        </tr>
                        <tr v-else-if="item.recipient === 'รองคณบดีฝ่ายกิจการนิสิต'">
                            <td class="px-1">-</td>
                            <td class="px-2" colspan="3">รองคณบดีฝ่ายกิจการนิสิต (เป็นผู้รับไม่ต้องมีช่องให้ลงชื่อในเอกสาร)</td>
                        </tr>
                        <tr v-else>
                            <td class="px-1">-</td>
                            <td class="px-2">ผศ.นพ.อติคุณ ธนกิจ</td>
                            <td class="px-2" colspan="2">รองคณบดีฝ่ายกิจการนิสิต</td>
                        </tr>
                    </table>
                </template>
                <p class="mt-1 text-xs text-blue-400">
                    ท่านสามารถศึกษาคู่มือการใช้งานระบบลงลายมือชื่อแบบอิเล็กทรอนิกส์ (DocHub) ได้ที่เมนู
                    <a :href="route('manual')" class="text-blue-500" target="_blank">คู่มือ</a> > การส่งหนังสือสพจ.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import {Head, Link} from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {ArrowDownTrayIcon} from '@heroicons/vue/20/solid';
import {Document, Personnel} from '@/types';

defineProps<{
    item: Document,
    can: { downloadDocument: boolean, updateDocument: boolean },
    has_attachment: boolean,
    has_approved: boolean,
    signers: Personnel[],
}>();
</script>
