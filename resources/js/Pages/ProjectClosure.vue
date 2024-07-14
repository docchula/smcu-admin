<template>
    <app-layout>
        <template #header>
            <Link :href="route('projects.show', {project: item.id})"
                  class="mb-4 flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                <p>
                    {{ item.name }}
                </p>
            </Link>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">รายงานผลการดำเนินโครงการ</h2>
            <p class="mt-2 text-gray-500">
                เมื่อดำเนินโครงการเสร็จสิ้นแล้ว ให้กรอกข้อมูลผลการปฏิบัติงานดังต่อไปนี้<br/>
                โครงการที่จะบันทึกเป็นส่วนหนึ่งของ Activity Transcript (Student Profile) ต้องบันทึกและกดยืนยันการส่ง <b>ภายใน 30 วัน
                นับจากสิ้นสุดกิจกรรม</b>
            </p>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <p v-if="form.hasErrors" class="bg-red-500 text-white p-3 w-full mb-6 rounded-md shadow-md transition">ข้อมูลที่กรอกไม่ถูกต้องครบถ้วน
                กรุณาตรวจสอบอีกครั้ง</p>
            <jet-form-section @submitted="submit">
                <template #title>ตรวจสอบข้อมูลพื้นฐาน</template>
                <template #description>
                    กรุณาตรวจสอบข้อมูลของโครงการ หากมีข้อผิดพลาด ให้
                    <Link :href="route('projects.edit', {project: item.id})" class="text-blue-500">แก้ไข</Link>
                    ให้เรียบร้อยก่อน
                </template>
                <template #form>
                    <div class="col-span-6 space-y-2">
                        <jet-label value="โครงการ"/>
                        {{ item.name }}
                    </div>
                    <div class="col-span-4 space-y-2">
                        <jet-label value="วันที่จัดกิจกรรม"/>
                        {{ (item.period_start === item.period_end) ? item.period_start : (item.period_start + ' - ' + item.period_end) }}
                        <p class="text-xs text-gray-500">
                            ใน Transcript อาจปรากฏเฉพาะเดือนและปี
                        </p>
                    </div>
                    <div class="col-span-2 space-y-2">
                        <jet-label value="ระยะเวลา"/>
                        {{ item.duration ?? '?' }} ชั่วโมง
                        <jet-input-error v-if="!item.duration" message="กรุณาแก้ไข"/>
                        <p v-else class="text-xs text-gray-500">
                            เฉพาะเวลากิจกรรมจริง
                        </p>
                    </div>
                    <div class="col-span-6 space-y-2">
                        <jet-label value="หน่วยงาน (สพจ.)"/>
                        {{ item.department.name }}
                    </div>
                    <div class="col-span-3 space-y-2">
                        <jet-label value="อาจารย์ที่ปรึกษา"/>
                        {{ item.advisor }}
                        <p class="text-xs text-gray-500">
                            อาจารย์ที่ปรึกษามีหน้าที่ประเมินหลักฐานการเข้าร่วมกิจกรรมของนิสิต (เฉพาะนิสิตหลักสูตรพ.บ. 2567)
                        </p>
                    </div>
                    <div class="col-span-3 space-y-2">
                        <jet-label value="ประมาณการจำนวนผู้เข้าร่วม"/>
                        {{ item.estimated_attendees ?? '?' }} คน
                        <jet-input-error v-if="!item.estimated_attendees" message="กรุณาแก้ไข"/>
                        <p v-else class="text-xs text-gray-500 col-span-6">
                            อาจเป็นนิสิตแพทย์หรือบุคคลอื่นก็ได้
                        </p>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section>
                <template #title>ตรวจสอบรายชื่อนิสิตผู้เกี่ยวข้อง</template>
                <template #description>
                    กรุณาเพิ่มเติมข้อมูลให้ครบถ้วนที่หน้า
                    <Link :href="route('projects.show', {project: item.id})" class="text-blue-500">โครงการ</Link>
                    ก่อนดำเนินการต่อ
                    <p class="mt-1">สำหรับนิสิตหลักสูตรแพทยศาสตรบัณฑิต (หลักสูตรปรับปรุง พ.ศ. 2560) จะไม่บันทึกบทบาท "ผู้เข้าร่วม"
                        ใน Activity Transcript</p>
                </template>
                <template #form>
                    <div v-for="(name, type) in PROJECT_PARTICIPANT_ROLES"
                         class="col-span-6 sm:grid sm:grid-cols-3 sm:gap-4 md:grid-cols-4">
                        <dt class="text-sm font-medium text-gray-500">
                            {{ name }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 md:col-span-3">
                            <ol class="list-decimal" v-if="participantsGrouped[type]">
                                <li v-for="e in participantsGrouped[type]">
                                    {{ e.user.name }}
                                    <span v-if="e.user.student_id" class="ml-4 text-gray-700">เลขประจำตัวนิสิต {{ e.user.student_id }}</span>
                                    <span v-if="e.title" class="ml-4 px-1.5 py-0.5 rounded bg-gray-200">{{ e.title }}</span>
                                </li>
                            </ol>
                            <span v-else class="text-gray-500">-</span>
                        </dd>
                    </div>
                    <div class="col-span-6">
                        <div class="mt-4 text-gray-500">
                            <h6 class="font-semibold">เงื่อนไขจำนวนนิสิตผู้เกี่ยวข้อง เพื่อบันทึกใน Activity Transcript</h6>
                            <p v-if="!organizerCountCompliance || !staffCountCompliance"
                               class="mt-2 mb-1 text-orange-500 border-orange-500 border p-2 w-full rounded-md">
                                <span class="font-semibold">ไม่ตรงตามเงื่อนไข</span>
                                เมื่อยืนยันและส่งเอกสารแล้ว ให้ติดต่อชี้แจงกับผู้ช่วยคณบดี/รองคณบดีที่ได้รับมอบหมาย
                            </p>
                            <ul class="mt-1 space-y-1 text-sm text-gray-500 list-inside list-disc">
                                <li>บทบาทของนิสิตผู้เกี่ยวข้อง ประกอบด้วย ผู้รับผิดชอบ ผู้ปฏิบัติงาน และผู้เข้าร่วม
                                    ตามสัดส่วนความรับผิดชอบในการดำเนินงาน
                                </li>
                                <li class="font-bold"
                                    :class="{'text-green-600': organizerCountCompliance, 'text-red-500': !organizerCountCompliance}">
                                    ผู้รับผิดชอบ พึงมีจำนวนไม่เกินร้อยละ 20 ของจำนวนผู้ปฏิบัติงาน ยกเว้นโครงการที่ไม่มีนิสิตเป็นผู้ปฏิบัติงาน
                                </li>
                                <li class="font-bold" :class="{'text-green-600': staffCountCompliance, 'text-red-500': !staffCountCompliance}">
                                    ผู้ปฏิบัติงาน พึงมีจำนวนไม่เกิน 2 ใน 3 ของผู้มีส่วนร่วมในกิจกรรมทั้งหมด ทั้งผู้รับผิดชอบ ผู้ปฏิบัติงาน
                                    และผู้เข้าร่วม ทั้งนิสิตและบุคคลภายนอก
                                </li>
                                <li>ผู้เข้าร่วม ต้องลงชื่อเข้าร่วมกิจกรรมทุกวัน ทุกครึ่งวัน หรือตามความเหมาะสมต่อลักษณะกิจกรรม
                                    โดยมีหลักฐานว่าเข้าร่วมกิจกรรมไม่น้อยกว่า 2 ใน 3 ของระยะเวลากิจกรรมทั้งหมด
                                    ให้ผู้รับผิดชอบโครงการเก็บรักษาหลักฐานดังกล่าวไว้
                                </li>
                                <li>กิจกรรมที่มีความจำเป็นต้องแบ่งบทบาทของนิสิตในสัดส่วนที่ไม่เป็นไปตามเงื่อนไขข้างต้น
                                    ให้ปรึกษาผู้ช่วย/รองคณบดีฝ่ายกิจการนิสิตพิจารณาอนุญาตเป็นรายกรณี
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section>
                <template #title>ประเมินความสำเร็จตามเป้าหมาย</template>
                <template #description>
                    ตามที่ได้กำหนดเป้าหมายของโครงการไว้ หลังดำเนินโครงการเสร็จสิ้นแล้ว ได้ผลอย่างไร
                </template>
                <template #form>
                    <table v-if="form.objectives.length > 0" class="col-span-6 divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th scope="col" class="px-1 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                ตัวชี้วัด
                            </th>
                            <th scope="col" class="px-1 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                วิธีการประเมินผล
                            </th>
                            <th scope="col" class="px-1 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                ผลที่ได้
                            </th>
                            <th scope="col" class="px-1 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                คิดเป็น
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="text-gray-400 text-sm">
                            <td class="px-1 py-2">
                                <u>ตัวอย่าง</u> มีนิสิตเข้าร่วมกิจกรรมไม่น้อยกว่า 50 คน
                            </td>
                            <td class="px-1 py-2">
                                แบบลงชื่อลงทะเบียนเข้าร่วม
                            </td>
                            <td class="px-1 py-2 whitespace-nowrap">
                                <jet-input type="text" class="text-sm block w-full" value="45 คน" disabled/>
                            </td>
                            <td class="px-1 py-2 whitespace-nowrap">
                                <jet-input type="text" class="text-sm w-14" value="90" disabled/>
                                <span class="ml-1 text-xs">%</span>
                            </td>
                        </tr>
                        <tr v-for="(member, index) in form.objectives">
                            <td class="px-1 py-4">
                                <span class="text-indigo-500">{{ index + 1 }}.</span> {{ member.goal }}
                            </td>
                            <td class="px-1 py-4 text-sm">
                                {{ member.method }}
                            </td>
                            <td class="px-1 py-4 whitespace-nowrap">
                                <jet-input type="text" class="block w-full" v-model="member.result" required/>
                            </td>
                            <td class="px-1 py-4 whitespace-nowrap">
                                <jet-input type="number" class="w-16 lg:w-20" v-model="member.percentage" step="0.01" min="0" max="100"/>
                                <span class="ml-1 text-xs">%</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <jet-input-error v-if="form.errors.objectives" :message="form.errors.objectives" class="col-span-6"/>
                    <p v-else class="col-span-6 text-xs text-gray-500">
                        หากไม่สามารถประเมินได้ ให้ระบุในช่องผลที่ได้ว่าไม่สามารถประเมินได้เพราะเหตุใด และเว้นว่างช่องคิดเป็น (%)
                    </p>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section>
                <template #title>รายจ่าย</template>
                <template #description>
                    จากที่ได้วางแผนของบประมาณไว้ ใช้จริงไปเท่าใด
                </template>
                <template #form>
                    <table v-if="form.expense.length > 0" class="col-span-6 divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th scope="col" class="px-1 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                รายการ
                            </th>
                            <th scope="col" class="px-1 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                ประเภท
                            </th>
                            <th scope="col" class="px-1 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                แหล่ง
                            </th>
                            <th scope="col" class="px-1 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                วางแผนงบ (บ.)
                            </th>
                            <th scope="col" class="px-1 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                จ่ายจริงไป (บ.)
                            </th>
                            <th scope="col" class="relative px-1 pb-2"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(member, index) in form.expense">
                            <template v-if="member.extra">
                                <td class="px-1 py-4 whitespace-nowrap">
                                    <jet-input type="text" class="block w-full" v-model="member.name" placeholder="รายจ่ายนอกแผน" required/>
                                    <jet-input-error message="จำเป็นต้องกรอก"
                                                     v-if="form.errors['selectedProject.expense.'+index+'.name'] ?? false" class="mt-2"/>
                                </td>
                                <td class="px-1 py-4 whitespace-nowrap">
                                    <select v-model="member.type" required
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <optgroup label="งบดำเนินงาน"/>
                                        <optgroup label="- ค่าตอบแทน">
                                            <option value="ค่าวิทยากร">ค่าตอบแทนวิทยากร</option>
                                            <option value="ค่าเจ้าหน้าที่นอกเวลา">ค่าเจ้าหน้าที่นอกเวลา</option>
                                            <option value="ค่าตอบแทน">ค่าตอบแทนอื่น ๆ (ให้ผู้ปฏิบัติงาน)</option>
                                        </optgroup>
                                        <optgroup label="- ค่าใช้สอย">
                                            <option value="ค่าใช้สอย">ค่าใช้สอย (เช่น จ้างเหมาบริการ)</option>
                                            <option value="ค่าอาหาร">ค่าอาหารและเครื่องดื่ม</option>
                                            <option value="ค่าที่พัก">ค่าที่พัก</option>
                                            <option value="ค่าพาหนะ">ค่าพาหนะ (ยกเว้นค่าน้ำมัน)</option>
                                        </optgroup>
                                        <optgroup label="- ค่าวัสดุ">
                                            <option value="ค่าวัสดุ">ค่าวัสดุ (ของที่ใช้ไม่นานหรือหมดไป)</option>
                                            <option value="ค่าเชื้อเพลิง">ค่าน้ำมันเชื้อเพลิง</option>
                                        </optgroup>
                                        <option value="- ค่าสาธารณูปโภค">ค่าสาธารณูปโภค (เช่น โทรศัพท์ ไปรษณีย์ วิทยุ)</option>
                                        <optgroup label="งบลงทุน">
                                            <option value="ค่าครุภัณฑ์">ค่าครุภัณฑ์ (ของที่คงทน ถาวร)</option>
                                        </optgroup>
                                        <option value="อื่น ๆ">อื่น ๆ</option>
                                        <option value="">(ไม่ระบุ)</option>
                                    </select>
                                    <jet-input-error message="จำเป็นต้องกรอก" v-if="form.errors['expense.'+index+'.type'] ?? false"
                                                     class="mt-2"/>
                                </td>
                                <td class="px-1 py-4 whitespace-nowrap">
                                    <select v-model="member.source" required
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="สพจ.">สพจ.</option>
                                        <option value="ฝ่ายกิจการนิสิต">ฝ่ายกิจการนิสิต</option>
                                        <option value="กองทุนวันอานันทมหิดล">กองทุนวันอานันทมหิดล</option>
                                        <option value="กองทุนอื่นของคณะ">กองทุนอื่นของคณะ</option>
                                        <option value="เงินบริจาค/สนับสนุน">เงินบริจาค/สนับสนุน</option>
                                        <option value="เงินฝ่าย/หน่วยงานสพจ.">เงินฝ่าย/หน่วยงานสพจ.</option>
                                        <option value="อื่น ๆ">อื่น ๆ</option>
                                    </select>
                                    <jet-input-error message="จำเป็นต้องกรอก" v-if="form.errors['expense.'+index+'.source'] ?? false"
                                                     class="mt-2"/>
                                </td>
                                <td class="px-1 py-4 text-center">
                                    (นอกแผน)
                                </td>
                                <td class="px-1 py-4 whitespace-nowrap">
                                    <jet-input type="number" class="block w-full sm:w-32 lg:w-36" min="0" step="0.01" v-model.number="member.paid"
                                               required/>
                                    <jet-input-error message="จำเป็นต้องกรอก" v-if="form.errors['expense.'+index+'.paid'] ?? false"
                                                     class="mt-2"/>
                                </td>
                                <td class="px-1 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 cursor-pointer" viewBox="0 0 20 20"
                                         fill="currentColor" @click="form.expense.splice(index, 1)">
                                        <path fill-rule="evenodd"
                                              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                              clip-rule="evenodd"/><!-- X -->
                                    </svg>
                                </td>
                            </template>
                            <template v-else>
                                <td class="px-1 py-4">
                                    {{ member.name }}
                                </td>
                                <td class="px-1 py-4 text-sm">
                                    {{ member.type }}
                                </td>
                                <td class="px-1 py-4 text-sm">
                                    {{ member.source }}
                                </td>
                                <td class="px-1 py-4 text-right">
                                    {{ Number(member.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                                </td>
                                <td colspan="2" class="px-1 py-4 whitespace-nowrap">
                                    <jet-input type="number" class="block w-full sm:w-32 lg:w-36" min="0" step="0.01" v-model.number="member.paid"
                                               required/>
                                    <jet-input-error message="จำเป็นต้องกรอก" v-if="form.errors['expense.'+index+'.paid'] ?? false"
                                                     class="mt-2"/>
                                </td>
                                <td></td>
                            </template>
                        </tr>
                        </tbody>
                    </table>
                    <div class="col-span-6">
                        <p v-if="form.expense.length === 0" class="mb-2">ไม่ได้ใช้งบประมาณ</p>
                        <jet-button class="bg-purple-500 hover:bg-purple-600 focus:border-purple-900" :disabled="form.processing"
                                    type="button"
                                    @click="form.expense.push({name: '', source: '', amount: '', extra: true})">
                            + รายจ่ายที่ไม่ได้วางแผน
                        </jet-button>
                        <jet-input-error v-if="form.errors.expense" :message="form.errors.expense" class="mt-2"/>
                        <p v-else-if="form.expense.length > 0" class="mt-2 text-xs text-gray-500">
                            หากไม่ได้ใช้จ่ายในรายการใด ให้ใส่ 0
                        </p>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section>
                <template #title>ดาวน์โหลดแบบรายงานผลโครงการ</template>
                <template #description>กรุณาดาวน์โหลดไฟล์ แก้ไข/เติมรายละเอียดให้ครบถ้วน (หรือใช้แบบฟอร์มที่สพจ. แจกจ่าย)
                    แล้วจึง
                    <Link :href="route('documents.create', {project_id: item.id, tag: 'summary'})" class="text-blue-500">ส่งเอกสาร</Link>
                    เรียนรองคณบดีฝ่ายกิจการนิสิตตามขั้นตอนต่อไป (ออกเลขเอกสารในเมนู สารบรรณ)
                </template>
                <template #form>
                    <div class="col-span-6">
                        <jet-button type="button" @click="generateSummaryDocument">
                            บันทึกและดาวน์โหลดแบบรายงานผลโครงการ
                        </jet-button>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submitted="submit">
                <template #title>บันทึกใน Activity Transcript</template>
                <template #description>
                    โครงการต้องส่งรายงานผลการปฏิบัติงานตามขั้นตอน ภายใน 30 วัน จึงจะมีสิทธิ์บันทึกลงใน Activity Transcript
                </template>
                <template #form>
                    <fieldset class="col-span-6">
                        <jet-label value="ยืนยันส่งเพื่อพิจารณาบันทึกลงใน Activity Transcript หรือไม่?"/>
                        <ul class="mt-1.5 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg">
                            <li class="w-full border-b border-gray-200 rounded-t-lg">
                                <div class="flex items-center ps-3">
                                    <input v-model="form.action" id="radio-no" type="radio" value="no" name="action" required
                                           class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500 focus:ring-2">
                                    <label for="radio-no" class="w-full py-3 ms-2 font-medium text-gray-900">
                                        ไม่ส่ง
                                        <p class="mt-1 text-xs text-gray-600">
                                            กรณีต้องการแก้ไขเพิ่มเติมภายหลัง (ต้องกลับมายืนยันในกำหนดเวลา 30 วัน) หรือสำหรับโครงการที่จะไม่บันทึกลงใน
                                            Activity Transcript
                                        </p>
                                    </label>
                                </div>
                            </li>
                            <li class="w-full border-b border-gray-200 rounded-t-lg">
                                <div class="flex items-center ps-3">
                                    <input v-model="form.action" id="radio-yes"
                                           type="radio" value="yes" name="action"
                                           :disabled="item.year < 2567 || !can_submit"
                                           class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 focus:ring-green-500 focus:ring-2">
                                    <label for="radio-yes" class="w-full py-3 ms-2 font-medium text-gray-900">
                                        ยืนยันส่งบันทึกลงใน Activity Transcript
                                        <p v-if="item.year < 2567" class="mt-1 text-xs text-red-600">
                                            เริ่มใช้ Activity Transcript สำหรับโครงการปีวาระ 2567 เป็นต้นไป
                                        </p>
                                        <p v-else-if="!can_submit" class="mt-1 text-xs text-red-600">
                                            หมดเขตส่ง ต้องบันทึกและยืนยันรายงานผลการปฏิบัติงาน ภายใน 30 วันนับจากเสร็จสิ้นกิจกรรม
                                        </p>
                                        <p v-else class="mt-1 text-xs text-gray-600">
                                            <b>เมื่อยืนยันแล้วไม่สามารถกลับมาแก้ไขข้อมูลได้อีก</b>
                                            ระบบจะให้นิสิตผู้รับผิดชอบและผู้ปฏิบัติงานทุกคนมาตรวจสอบและยืนยันรายชื่อผู้ปฏิบัติงานต่อไป
                                        </p>
                                    </label>
                                </div>
                            </li>
                        </ul>
                        <jet-input-error :message="form.errors.action" class="mt-2"/>
                    </fieldset>
                    <p class="col-span-6 text-gray-500">
                        กิจกรรมที่สามารถบันทึกใน Activity Transcript จะต้องมีระยะเวลาไม่น้อยกว่า 3 ชั่วโมง
                        และสนับสนุน Outcome ของหลักสูตรแพทยศาสตรบัณฑิต โดยผ่านการพิจารณาของผู้ช่วย/รองคณบดีฝ่ายกิจการนิสิต
                    </p>
                </template>
                <template #actions>
                    <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                        Saved.
                    </jet-action-message>

                    <jet-button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing && !form.wasSuccessful">
                        Save
                    </jet-button>
                </template>
            </jet-form-section>
        </div>
    </app-layout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import JetActionMessage from '@/Jetstream/ActionMessage.vue'
import JetButton from '@/Jetstream/Button.vue'
import JetFormSection from '@/Jetstream/FormSection.vue'
import JetInput from '@/Jetstream/Input.vue'
import JetInputError from '@/Jetstream/InputError.vue'
import JetLabel from '@/Jetstream/Label.vue'
import JetSectionBorder from '@/Jetstream/SectionBorder.vue'
import {computed} from 'vue';
import {Link, useForm} from '@inertiajs/vue3'
import {groupBy} from "lodash";
import {PROJECT_PARTICIPANT_ROLES} from "@/static";

const props = defineProps({
    item: Object,
    can_submit: Boolean,
});

// Data
const form = useForm({
    _method: 'POST',
    objectives: props.item.objectives ?? [],
    expense: props.item.expense ?? [],
    action: '',
});

// Computed
const participantsGrouped = computed(() => {
    return (props.item.participants.length > 0) ? groupBy(props.item.participants, 'type') : null;
});
const organizerCountCompliance = computed(() => {
    if (!participantsGrouped.value || !participantsGrouped.value['organizer'] || !participantsGrouped.value['staff']) return true;
    return participantsGrouped.value['organizer'].length <= 0.2 * participantsGrouped.value['staff'].length;
});
const staffCountCompliance = computed(() => {
    if (!participantsGrouped.value || !participantsGrouped.value['staff']) return true;
    return participantsGrouped.value['staff'].length <= 0.66667 * ((participantsGrouped.value['attendee'] && participantsGrouped.value['attendee'].length > 0) ? props.item.participants.length : (props.item.participants.length + Number(props.item.estimated_attendees)));
});

// Methods
const submit = function () {
    if (form.objectives.filter(o => o.result && o.result.length > 0).length !== form.objectives.length) {
        form.errors.objectives = "กรุณากรอกผลการประเมินทุกตัวชี้วัด";
        return;
    } else {
        form.errors.objectives = "";
    }
    if (form.expense.filter(e => e.paid || (e.paid === 0)).length !== form.expense.length) {
        form.errors.expense = "กรุณากรอกยอดเงินที่จ่ายจริงให้ครบทุกรายการ หากไม่ได้ใช้จ่าย ให้ใส่ 0";
        return;
    } else {
        form.errors.expense = "";
    }
    form.post(route('projects.closureSubmit', {project: props.item.id}));
};
const generateSummaryDocument = function () {
    form.action = 'generate_document';
    submit();
}
</script>
