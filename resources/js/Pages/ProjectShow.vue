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
            <div v-if="item.can['update-project'] && item.documents.length === 0"
                 class="bg-white shadow overflow-hidden sm:rounded-lg my-4 ring-4 ring-yellow-400">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        สร้างเอกสารขออนุมัติโครงการ
                    </h3>
                </div>
                <div class="border-t border-gray-200 p-4 sm:px-6">
                    <a :href="route('projects.generateApprovalDocument', {project: item.id})"
                       class="inline-block items-center px-4 py-2 mb-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">ดาวน์โหลดแบบขออนุมัติโครงการ</a>
                    <p class="text-sm text-gray-700">เมื่อดาวน์โหลดเอกสารแล้ว กรุณาแก้ไขเพิ่มเติมข้อมูลต่าง ๆ ให้ครบถ้วน
                        และปรับแก้การจัดหน้าให้เรียบร้อยก่อนส่งขออนุมัติตามลำดับขั้น</p>
                    <p v-if="item.expense.filter(e => e.source === 'ฝ่ายกิจการนิสิต').length > 0" class="text-yellow-700">
                        โครงการที่ใช้งบประมาณฝ่ายกิจการนิสิต
                        ควรนำร่างเอกสารปรึกษาเจ้าหน้าที่ฝ่ายกิจการนิสิต เพื่อตรวจสอบความถูกต้องก่อนส่งขออนุมัติ</p>
                </div>
            </div>
            <div v-if="item.shouldBeClosed" class="bg-white shadow overflow-hidden sm:rounded-lg my-4 ring-4 ring-blue-400">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        รายงานผลการดำเนินโครงการ
                    </h3>
                </div>
                <div class="border-t border-gray-200 p-4 sm:px-6">
                    <p class="my-0.5 text-gray-700">
                        เมื่อเสร็จสิ้นโครงการแล้ว ให้รายงานผลการดำเนินโครงการ และส่งเบิกค่าใช้จ่าย (ถ้ามี) ให้เรียบร้อยโดยเร็ว
                    </p>
                    <template v-if="hasProjectClosure">
                        <p class="mt-2 font-bold text-blue-700">
                            <CheckIcon class="h-5 w-5 inline-block"/>
                            ส่งข้อมูลเพื่อบันทึกลงใน Activity Transcript แล้ว กรุณาส่งเอกสารตามขั้นตอนต่อไป
                        </p>
                        <a :href="route('projects.generateSummaryDocument', {project: item.id})"
                           class="inline-block items-center px-4 py-2 mt-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            ดาวน์โหลดแบบรายงานผลโครงการ
                        </a>
                        &ensp;แก้ไขเพิ่มเติมรายละเอียดในไฟล์ แล้ว
                        <a :href="route('documents.create', {project_id: item.id, tag: 'summary'})"
                           class="text-green-600">ออกเลขเอกสาร</a>
                    </template>
                    <template v-else>
                        <p class="my-0.5 text-blue-500">
                            โครงการที่จะบันทึกเป็นส่วนหนึ่งของ Activity Transcript ต้องบันทึกและกดยืนยันการส่ง
                            <b>ภายใน 30 วัน</b>หลังจากสิ้นสุดโครงการ<br/>
                            ข้อมูลผลการดำเนินโครงการ มีผลต่อการพิจารณางบประมาณโครงการ/ฝ่ายครั้งถัดไป
                        </p>
                        <inertia-link :href="route('projects.closureForm', {project: item.id})"
                                      class="inline-block items-center px-4 py-2 mt-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            บันทึกผลการดำเนินโครงการ และสร้างแบบรายงานผลโครงการ
                        </inertia-link>
                        &ensp;หรือ&ensp;
                        <a :href="route('projects.generateSummaryDocument', {project: item.id})"
                           class="text-blue-400">ดาวน์โหลดแบบรายงานผลโครงการ</a>
                    </template>
                </div>
            </div>
            <div v-if="item.shouldVerify" class="bg-white shadow overflow-hidden sm:rounded-lg my-4 ring-4 ring-purple-400">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        กรุณารับรองรายชื่อนิสิตผู้เกี่ยวข้อง
                    </h3>
                </div>
                <div class="border-t border-gray-200 p-4 sm:px-6">
                    <p class="my-0.5 text-gray-700">
                        โครงการที่จะบันทึกเป็นส่วนหนึ่งของ Activity Transcript ต้องผ่านการรับรองรายชื่อนิสิตผู้เกี่ยวข้อง
                        โดยนิสิตผู้รับผิดชอบและผู้ปฏิบัติงานทุกคน<b>ภายใน 60 วัน นับจากสิ้นสุดกิจกรรม</b>
                    </p>
                    <inertia-link :href="route('projects.closureVerifyForm', {project: item.id})"
                                  class="inline-block items-center px-4 py-2 mt-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                        ตรวจสอบและรับรองรายชื่อนิสิตผู้เกี่ยวข้อง
                    </inertia-link>
                </div>
            </div>
            <div v-if="item.closure_status !== 0" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="border-t border-gray-200 p-4 sm:px-6">
                    สถานะการพิจารณาเพื่อบันทึกลงใน Activity Transcript :
                    <ClosureStatusText class="font-bold" :closure_status="item.closure_status"/>
                    <p v-if="item.closure_status < 0 && item.closure_approved_message">
                        <span class="text-red-700">เหตุผล</span> : {{ item.closure_approved_message }}
                    </p>
                    <p v-if="item.closure_status === -2" class="mt-1 text-gray-500">
                        ให้
                        <inertia-link :href="route('projects.closureForm', {project: item.id})"
                                      class="text-green-600">
                            แก้ไขรายละเอียดแล้วส่งใหม่
                        </inertia-link>
                        หรือ
                        <a class="text-red-500 cursor-pointer" @click="showCancelDialog = true">ยกเลิกการส่งเพื่อแก้ไขรายชื่อผู้เกี่ยวข้อง</a>แล้วส่งใหม่
                        ภายใน 30 วัน จากวันที่ได้รับการพิจารณาให้ไปแก้ไข
                    </p>
                </div>
            </div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        ข้อมูลพื้นฐาน
                        <inertia-link v-if="item.can['update-project'] && (item.closure_status === 0 || item.closure_status === -2)"
                                      :href="route('projects.edit', {project: item.id})"
                                      class="text-yellow-600 hover:text-yellow-900 text-sm ml-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                            แก้ไขแผนโครงการ
                        </inertia-link>
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl class="grid grid-cols-2 lg:grid-cols-3 text-gray-900">
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">อาจารย์ที่ปรึกษา</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.advisor }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">สร้างโดย</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.user.name }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">หน่วยงาน</dt>
                            <dd v-if="item.department" class="mt-1 text-sm sm:mt-0 sm:col-span-2"
                                :class="{'text-gray-400': item.department_id === 33}">
                                {{ item.department.name }}
                            </dd>
                            <i class="text-gray-300" v-else>{{ item.department_id }}</i>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ปีวาระ</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.year }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ลักษณะกิจกรรม</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{
                                    {
                                        once: 'กิจกรรมครั้งเดียว',
                                        longitudinal: 'กิจกรรมระยะยาว',
                                        purchase: 'โครงการจัดซื้อ'
                                    }[item.type]
                                }}
                            </dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ประเภท</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ ['โครงการครั้งแรก', 'โครงการต่อเนื่อง'][item.recurrence] }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">วันที่เริ่ม</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.period_start }}</dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">วันที่สิ้นสุด</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.period_end }}</dd>
                        </div>
                        <div v-if="item.duration" class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ระยะเวลา</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.duration }} ชั่วโมง</dd>
                        </div>
                        <div v-if="item.estimated_attendees" class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ประมาณการจำนวนผู้เข้าร่วม (คน)</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ item.estimated_attendees }}</dd>
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
                <div class="border-t border-gray-200 text-gray-900">
                    <dl class="">
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">หลักการและเหตุผล</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2 whitespace-pre-wrap">
                                {{ item.background }}
                            </dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">วัตถุประสงค์</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2 whitespace-pre-wrap">
                                {{ item.aims }}
                            </dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">ผลที่คาดว่าจะได้รับ</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2 whitespace-pre-wrap">
                                {{ item.outcomes }}
                            </dd>
                        </div>
                        <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">เป้าหมาย และวิธีการประเมิน</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                <ol v-if="item.objectives.length" class="list-decimal">
                                    <li v-for="objective in item.objectives">
                                        <u>เป้าหมาย</u>&nbsp;{{ objective.goal }}<br/>
                                        <u>วิธีการประเมิน</u>&nbsp;{{ objective.method }}
                                        <div v-if="hasProjectSummary" class="text-indigo-500">
                                            <u>ผลลัพธ์</u>&nbsp;{{ objective.result }}
                                            <span v-if="objective.percentage">&emsp;คิดเป็นร้อยละ {{ objective.percentage }}</span>
                                        </div>
                                    </li>
                                </ol>
                                <span v-else>-</span>
                                <p v-if="hasProjectSummary" class="mt-2 text-indigo-500">
                                    สรุปผลการดำเนินโครงการ คิดเป็นร้อยละ {{ totalProductPercentage }}
                                </p>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        งบประมาณ
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-4 sm:px-6">
                        <template v-if="item.expense.length > 0">
                            <table class="w-full divide-y divide-gray-200">
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
                                        วางแผนงบ (บ.)
                                    </th>
                                    <th v-if="hasProjectSummary" scope="col"
                                        class="px-2 pb-1 text-left text-xs font-medium text-indigo-400 tracking-wider">
                                        จ่ายจริงไป (บ.)
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="e in item.expense" :class="{'text-indigo-500': e.extra}">
                                    <td class="p-2">
                                        {{ e.name }}
                                    </td>
                                    <td class="p-2">
                                        {{ e.type }}
                                    </td>
                                    <td class="p-2">
                                        {{ e.source }}
                                    </td>
                                    <td class="p-2 text-right">
                                        {{ e.amount ? Number(e.amount).toLocaleString('th-TH') : '-' }}
                                    </td>
                                    <td v-if="hasProjectSummary" class="p-2 text-right text-indigo-500">
                                        {{ e.paid ? Number(e.paid).toLocaleString('th-TH') : '-' }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <p class="mt-2">
                                สรุปวางแผนงบประมาณทั้งสิ้น
                                <span class="px-1">
                                    {{ Number(totalExpense).toLocaleString('th-TH') }} บาท
                                </span>
                                <span v-if="item.expense.find(e => e.paid)" class="px-1 text-indigo-500">
                                    ใช้จ่ายจริง {{ Number(totalPaid).toLocaleString('th-TH') }} บาท&ensp;
                                   (คิดเป็นร้อยละ {{ (totalPaid / totalExpense * 100).toFixed(2) }})
                                </span>
                            </p>
                        </template>
                        <span v-else class="text-gray-500">ไม่ใช้งบประมาณ</span>
                    </div>
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
                        <table class="w-full divide-y divide-gray-200">
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
                                    <inertia-link :href="route('documents.show', {document: e.id})">
                                        <DocumentChartBarIcon v-if="e.tag === 'summary'" class="text-yellow-700 h-4 w-4 inline-block"/>
                                        <DocumentTextIcon v-if="e.tag === 'approval'" class="text-purple-700 h-4 w-4 inline-block"/>
                                        {{ e.title }}
                                    </inertia-link>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div v-if="participantsGrouped" class="bg-white shadow overflow-hidden sm:rounded-lg my-4">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 inline-block">
                        นิสิตผู้เกี่ยวข้อง
                    </h3>
                    <template v-if="item.can['update-project']">
                        <a @click="showImportParticipantDialog = true" v-if="!hasProjectClosure"
                           class="text-sm ml-4 inline-block cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                            <ArrowUpTrayIcon class="h-5 w-5 mr-1 inline"/>
                            <span>นำเข้าด้วยไฟล์ Excel</span>
                        </a>
                        <a :href="route('projects.exportParticipant', {project: item.id})" class="text-green-600 text-sm ml-2 inline-block">
                            <PrinterIcon class="inline-block h-5 w-5"/>
                            พิมพ์
                        </a>
                        <span v-if="!hasProjectClosure" class="ml-2 text-sm">
                            กรุณาเพิ่มรายชื่อนิสิตให้ครบถ้วนก่อนส่งรายงานผล
                        </span>
                    </template>
                </div>
                <ImportParticipantDialog :show-modal="showImportParticipantDialog" :project="item" @close="showImportParticipantDialog = false"/>
                <StudentIdDialog :show-modal="Boolean(showStudentIdDialog)" :list="item.participants.map(p => p.user?.student_id)"
                                 @close="showStudentIdDialog = false" @selected="addParticipant($event)"/>
                <div class="border-t border-gray-200">
                    <dl>
                        <div v-for="(name, type) in PROJECT_PARTICIPANT_ROLES"
                             class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                {{ name }}
                                <PlusIcon v-if="item.can['update-project'] && !hasProjectClosure"
                                          class="inline-block ml-1 h-5 text-green-400 cursor-pointer"
                                          @click="showStudentIdDialog = type"/>
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <ol class="list-decimal" v-if="participantsGrouped[type]">
                                    <li v-for="e in participantsGrouped[type]">
                                        {{ e.user?.name }}
                                        <span v-if="e.user?.student_id" class="ml-4 text-gray-700">เลขประจำตัวนิสิต {{ e.user?.student_id }}</span>
                                        <span v-if="e.title" class="ml-4 px-1.5 py-0.5 rounded bg-gray-200">{{ e.title }}</span>
                                        <XMarkIcon
                                            v-if="e.user?.student_id && !((type === 'organizer') && (participantsGrouped[type].length <= 1)) && item.can['update-project'] && !hasProjectClosure"
                                                   class="inline-block ml-1 h-5 text-red-400 cursor-pointer" @click="removeParticipant(e)"/>
                                    </li>
                                </ol>
                                <span v-else class="text-gray-500">-</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            <p class="mt-1 text-sm text-gray-500 text-right">
                สร้างเมื่อ {{ item.created_at }}
                <span v-if="item.updated_at && (item.created_at !== item.updated_at)">
                    &emsp;แก้ไขเมื่อ {{ item.updated_at }}
                </span>
            </p>
        </div>
        <ClosureCancelDialog :show-modal="showCancelDialog" :project="item" @close="showCancelDialog = false"/>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import JetButton from '@/Jetstream/Button.vue'
import {
    ArrowUpTrayIcon,
    CheckCircleIcon,
    CheckIcon,
    DocumentChartBarIcon,
    DocumentTextIcon,
    PlusIcon,
    PrinterIcon,
    XMarkIcon
} from "@heroicons/vue/20/solid";
import StudentIdDialog from "../Components/StudentIdDialog.vue";
import _ from "lodash";
import ImportParticipantDialog from "../Components/ImportParticipantDialog.vue";
import {PROJECT_PARTICIPANT_ROLES} from "@/static";
import ClosureStatusText from "@/Components/ClosureStatusText.vue";
import ClosureCancelDialog from "@/Components/ClosureCancelDialog.vue";

export default {
    components: {
        ClosureCancelDialog,
        ClosureStatusText,
        CheckCircleIcon,
        CheckIcon, DocumentChartBarIcon, DocumentTextIcon,
        ImportParticipantDialog,
        StudentIdDialog,
        AppLayout,
        PlusIcon,
        XMarkIcon,
        JetButton,
        ArrowUpTrayIcon,
        PrinterIcon,
    },
    computed: {
        participantsGrouped() {
            return (this.item.participants.length > 0) ? _.groupBy(this.item.participants, 'type') : null;
        },
        hasProjectSummary() {
            return Boolean(this.item.objectives.find(o => o.result));
        },
        hasProjectClosure() {
            return Boolean(this.item.closure_submitted_at);
        },
        totalExpense() {
            return this.item.expense.reduce((i, entry) => i - -(entry.amount || 0), 0);
        },
        totalPaid() {
            return this.item.expense.reduce((i, entry) => i - -(entry.paid || 0), 0);
        },
        totalProductPercentage() {
            const values = this.item.objectives.reduce((array, entry) => {
                if (entry.percentage) {
                    array.push(Number(entry.percentage));
                }
                return array;
            }, []);

            return values.length ? Number(values.reduce((a, b) => a - -b) / values.length).toFixed(2) : "-";
        },
    },
    data() {
        return {
            showStudentIdDialog: false,
            showImportParticipantDialog: false,
            showCancelDialog: false,
            addParticipantForm: this.$inertia.form({
                type: '',
                student_ids: [],
            }),
            PROJECT_PARTICIPANT_ROLES: PROJECT_PARTICIPANT_ROLES,
        }
    },
    methods: {
        addParticipant(student) {
            if (!this.item.participants.find(p => p.user.student_id === student.student_id)) {
                this.addParticipantForm.type = this.showStudentIdDialog;
                this.addParticipantForm.student_ids = [student.student_id];
                this.addParticipantForm.post(this.route('projects.addParticipant', {project: this.item.id}));
                this.showStudentIdDialog = false;
                /* this.item.participants.push({
                    type: this.showStudentIdDialog,
                    user: student,
                }); */
            }
        },
        removeParticipant(participant) {
            if (confirm('ต้องการลบนิสิตผู้เกี่ยวข้องใช่หรือไม่?')) {
                this.$inertia.post(this.route('projects.removeParticipant', {participant: participant.id}), {}, {});
            }
        },
    },
    props: {
        item: Object,
    },
};
</script>
