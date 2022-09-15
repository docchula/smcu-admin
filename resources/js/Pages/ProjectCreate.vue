<template>
    <app-layout>
        <template #header>
            <inertia-link :href="item.id ? route('projects.show', {project: item.id}) : route('projects.index')" class="mb-4 block flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                <p v-if="item.id">
                    {{ item.name }}
                </p>
                <p v-else>โครงการ</p>
            </inertia-link>
            <h2 v-if="item.id" class="font-semibold text-xl text-gray-800 leading-tight">
                แก้ไข {{ item.name }}
            </h2>
            <template v-else>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">สร้างโครงการใหม่</h2>
                <p class="mt-2 text-gray-500">สามารถกรอกเฉพาะข้อมูลพื้นฐานก่อนได้ กรณีต้องการส่งเฉพาะเพื่อขอจัดสรรงบประมาณ</p>
            </template>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <p v-if="form.hasErrors" class="bg-red-500 text-white p-3 w-full mb-6 rounded-md shadow-md transition">ข้อมูลที่กรอกไม่ถูกต้องครบถ้วน กรุณาตรวจสอบอีกครั้ง</p>
            <jet-form-section @submitted="submit">
                <template #title>ข้อมูลพื้นฐาน</template>
                <template #description>ชื่อผู้ใช้ที่สร้างเอกสารจะถูกบันทึกและแสดงผลในฐานช้อมูล
                    กรณี<a @click="forAcademicPresentation" class="cursor-pointer text-green-500">โครงการนำเสนอผลงานในการประชุมวิชาการ</a>
                    เลือกประเภท "กิจกรรมครั้งเดียว" และ "โครงการครั้งแรก"</template>

                <template #form>
                    <div class="col-span-6">
                        <jet-label for="name" value="ชื่อโครงการ"/>
                        <jet-input id="name" type="text" class="mt-1 block w-full" v-model="form.name" ref="name" required placeholder="ไม่ต้องมีคำว่าโครงการนำหน้า"/>
                        <jet-input-error :message="form.errors.name" class="mt-2"/>
                    </div>
                    <div class="col-span-6">
                        <label for="department" class="block text-sm font-medium text-gray-700">หน่วยงานที่รับผิดชอบ</label>
                        <select id="department" v-model="form.department_id" required
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option v-for="department in static_departments" v-bind:key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                        <jet-input-error :message="form.errors.department_id" class="mt-2"/>
                    </div>
                    <div class="col-span-6">
                        <jet-label for="advisor" value="อาจารย์ที่ปรึกษา"/>
                        <jet-input id="advisor" type="text" class="mt-1 block w-full" v-model="form.advisor" ref="advisor" required placeholder="ชื่อเต็ม เช่น ศ.ดร.นพ.สิทธิศักดิ์ หรรษาเวก"/>
                        <jet-input-error :message="form.errors.advisor" class="mt-2"/>
                    </div>
                    <fieldset class="col-span-6">
                        <div class="flex gap-x-8">
                            <div class="flex items-center">
                                <input id="type_once" v-model="form.type" name="type" value="once" type="radio" required
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="type_once" class="ml-3 block text-sm font-medium text-gray-700">
                                    กิจกรรมครั้งเดียว
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="type_longitudinal" v-model="form.type" name="type" value="longitudinal" type="radio"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="type_longitudinal" class="ml-3 block text-sm font-medium text-gray-700">
                                    กิจกรรมระยะยาว
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="type_purchase" v-model="form.type" name="type" value="purchase" type="radio"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="type_purchase" class="ml-3 block text-sm font-medium text-gray-700">
                                    โครงการจัดซื้อ (ไม่ใช่กิจกรรม)
                                </label>
                            </div>
                        </div>
                        <jet-input-error :message="form.errors.type" class="mt-2"/>
                    </fieldset>
                    <fieldset class="col-span-6">
                        <div class="flex gap-x-8">
                            <div class="flex items-center">
                                <input id="recurrence_no" v-model="form.recurrence" name="recurrence" value="0" type="radio" required
                                       class="focus:ring-pink-500 h-4 w-4 text-pink-600 border-gray-300">
                                <label for="recurrence_no" class="ml-3 block text-sm font-medium text-gray-700">
                                    โครงการครั้งแรก
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="recurrence_yes" v-model="form.recurrence" name="recurrence" value="1" type="radio"
                                       class="focus:ring-pink-500 h-4 w-4 text-pink-600 border-gray-300">
                                <label for="recurrence_yes" class="ml-3 block text-sm font-medium text-gray-700">
                                    โครงการต่อเนื่อง (ปีที่แล้วก็มีโครงการเดียวกัน)
                                </label>
                            </div>
                        </div>
                        <jet-input-error :message="form.errors.recurrence" class="mt-2"/>
                    </fieldset>
                    <div class="col-span-6 lg:col-span-2">
                        <jet-label for="year" value="ปีวาระ"/>
                        <jet-input id="year" v-model="form.year" type="number" min="2564" max="2580" class="mt-1 block w-full" required :disabled="item.number"/>
                        <jet-input-error :message="form.errors.year" class="mt-2"/>
                    </div>
                    <div class="col-span-3 lg:col-span-2">
                        <jet-label for="period_start" :value="(form.type === 'once') ? 'วันที่เริ่มกิจกรรม' : 'วันที่เริ่มดำเนินงาน'"/>
                        <datepicker id="period_start" v-model="form.period_start" type="date" :weekStartsOn="0" startingView="month" inputFormat="yyyy-MM-dd"
                                    class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full"
                        ></datepicker>
                        <p class="mt-1 text-xs text-gray-500 col-span-6" v-if="!form.errors.period_start && (form.type === 'once')">
                            ไม่รวมเตรียมงานและสรุปผล
                        </p>
                        <jet-input-error :message="form.errors.period_start" class="mt-2"/>
                    </div>
                    <div class="col-span-3 lg:col-span-2">
                        <jet-label for="period_end" :value="(form.type === 'once') ? 'วันที่สิ้นสุดกิจกรรม' : 'วันที่สิ้นสุดการดำเนินงาน'"/>
                        <datepicker id="period_end" v-model="form.period_end" type="date" :weekStartsOn="0" startingView="month" inputFormat="yyyy-MM-dd"
                                    class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full"
                        ></datepicker>
                        <jet-input-error :message="form.errors.period_end" class="mt-2"/>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submitted="submit">
                <template #title>เหตุผล และวัตถุประสงค์</template>
                <template #description>โครงการนี้จัดทำเพราะเหตุใด เพื่ออะไร</template>
                <template #form>
                    <div class="col-span-6">
                        <label for="background" class="block text-sm font-medium text-gray-700">หลักการและเหตุผล</label>
                        <div class="mt-1">
                            <textarea id="background" rows="4" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md"
                                      placeholder="แสดงปัญหา ความจำเป็น หรือความต้องการที่ต้องมีการจัดทำโครงการขึ้น โดยอาจเชื่อมโยงกับนโยบาย/วิสัยทัศน์/แผนงาน หรืออ้างอิงหลักฐาน/หลักวิชาการ"
                                      v-model.trim="form.background"></textarea>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">กรอกข้อความเท่านั้น กรณีต้องการขึ้นย่อหน้าใหม่ให้กด Enter 2 ครั้ง (มีบรรทัดว่างคั่น)</p>
                        <jet-input-error :message="form.errors.background" class="mt-1"/>
                    </div>
                    <div class="col-span-6">
                        <label for="aims" class="block text-sm font-medium text-gray-700">วัตถุประสงค์</label>
                        <div class="mt-1">
                            <textarea id="aims" :rows="(aimLines > 2) ? aimLines : 2" placeholder="ผลลัพธ์ที่จะทำให้เกิดขึ้นในระยะยาว ขึ้นต้นด้วย เพื่อ..." v-model.trim="form.aims"
                                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md"
                            ></textarea>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">บรรทัดละข้อ (ตอนนี้มี {{ aimLines }} ข้อ)</p>
                        <jet-input-error :message="form.errors.aims" class="mt-1"/>
                    </div>
                    <div class="col-span-6">
                        <label for="outcomes" class="block text-sm font-medium text-gray-700">ผลที่คาดว่าจะได้รับ</label>
                        <div class="mt-1">
                            <textarea id="outcomes" :rows="(outcomeLines > 2) ? outcomeLines : 2" placeholder="เมื่อทำโครงการแล้ว จะมีผลดีอะไรตามมา ควรสอดคล้องกับวัตถุประสงค์"
                                      v-model.trim="form.outcomes"
                                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md"
                            ></textarea>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">บรรทัดละข้อ (ตอนนี้มี {{ outcomeLines }} ข้อ)</p>
                        <jet-input-error :message="form.errors.outcomes" class="mt-1"/>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <!-- jet-form-section @submitted="submit">
                <template #title>ความสอดคล้องตามแผนงาน</template>
                <template #description>
                    โครงการนี้สอดคล้องกับวิสัยทัศน์หรือแผนงานของสโมสรอย่างไร เลือกได้หลายข้อ
                    <span class="py-0.5 px-2 ml-2 text-white bg-red-500 rounded inline-block">Not Implemented</span>
                </template>
                <template #form>
                    <div class="col-span-6">
                        <label for="category" class="block text-sm font-medium text-red-700">แผนงานของสโมสร</label>
                        <select id="category" required multiple
                                class="mt-1 block w-full h-64 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <-- @todo v-model="okr" ->
                            <optgroup label="1. สนับสนุนกิจกรรมนิสิต">
                                <option>1.1) เพื่อส่งเสริมกิจกรรมเสริมหลักสูตร ในด้านทักษะทางวิชาการ ศิลปะ วัฒนธรรม และพลานามัย</option>
                                <option>1.2) เพื่อส่งเสริมความสัมพันธ์อันดีระหว่างนิสิต อาจารย์ ศิษย์เก่า บุคลากร</option>
                            </optgroup>
                            <optgroup label="2. พัฒนานิสิต">
                                <option>2.1) เพื่อส่งเสริมคุณธรรม จริยธรรม และให้นิสิตมีความรับผิดชอบต่อตนเอง หมู่คณะ และสังคม</option>
                                <option>2.2) เพื่อส่งเสริมและสนับสนุนให้นิสิตปฏิบัติตามกฎระเบียบข้อบังคับของคณะ และมหาวิทยาลัย</option>
                            </optgroup>
                            <optgroup label="3. ดูแลสวัสดิภาพนิสิต">
                                <option>3.1) เพื่อรักษาสิทธิ และผลประโยชน์ของนิสิต</option>
                                <option>3.2) เพื่อส่งเสริมสวัสดิการของนิสิต</option>
                            </optgroup>
                        </select>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/ -->
            <jet-form-section @submitted="submit">
                <template #title>ตัวชี้วัด และวิธีการประเมินผล</template>
                <template #description>
                    ตัวชี้วัด คือ หน่วยวัดความสำเร็จของการปฏิบัติงาน ควรมีผลเป็นตัวเลขที่นับได้จริง และต้องสื่อถึงเป้าหมายในการปฏิบัติงานสำคัญ<br/>
                    <a @click="objectives.push({goal: 'ผู้เข้าร่วมร้อยละ 50 เห็นว่ากิจกรรมนี้ทำให้ตระหนักถึงปัญหาความไม่เท่าเทียมทางเพศในวงการแพทย์', method: 'แบบสอบถาม'})"
                       class="cursor-pointer text-green-600">ดูตัวอย่าง</a>
                </template>
                <template #form>
                    <table v-if="objectives.length > 0" class="col-span-6 divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                ตัวชี้วัด
                            </th>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                วิธีการประเมินผล
                            </th>
                            <th scope="col" class="relative px-1 pb-2"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(member, index) in objectives">
                            <td class="px-3 py-4 whitespace-nowrap">
                                <jet-input type="text" class="block w-full" v-model="member.goal" required/>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <jet-input type="text" class="block w-full" v-model="member.method" required/>
                            </td>
                            <td class="px-1 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 cursor-pointer" viewBox="0 0 20 20" fill="currentColor" @click="objectives.splice(index, 1)">
                                    <path fill-rule="evenodd"
                                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/><!-- X -->
                                </svg>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="col-span-6">
                        <p v-if="objectives.length === 0" class="mb-2 text-red-800">กรุณาเพิ่มข้อมูลเป้าหมาย</p>
                        <jet-button class="bg-purple-500 hover:bg-purple-600 focus:border-purple-900" :disabled="form.processing" type="button" @click="objectives.push({goal: '', method: ''})">
                            เพิ่มเป้าหมาย
                        </jet-button>
                        <jet-input-error :message="form.errors.objectives" class="mt-2"/>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submitted="submit">
                <template #title>งบประมาณ</template>
                <template #description>
                    ค่าใช้จ่ายเพื่อดำเนินโครงการ
                    <a href="http://www.audit.moi.go.th/books0704-33_0704-68.pdf" target="_blank" class="text-green-600 block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                            <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>
                        </svg>
                        หลักการจำแนกประเภทรายจ่าย
                    </a>
                </template>
                <template #form>
                    <table v-if="expense.length > 0" class="col-span-6 divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                รายการ
                            </th>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                ประเภทรายจ่าย
                            </th>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                แหล่งงบประมาณ
                            </th>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                จำนวนเงิน (บ.)
                            </th>
                            <th scope="col" class="relative px-1 pb-2"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(member, index) in expense">
                            <td class="px-3 py-4 whitespace-nowrap">
                                <jet-input type="text" class="block w-full" v-model="member.name" required/>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <select name="department" v-model="member.type" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <optgroup label="งบดำเนินงาน">
                                        <option value="ค่าตอบแทน">ค่าตอบแทน (ให้ผู้ปฏิบัติงาน)</option>
                                        <option value="ค่าใช้สอย">ค่าใช้สอย (ค่าบริการและอื่น ๆ)</option>
                                        <option value="ค่าวัสดุ">ค่าวัสดุ (ของที่ใช้ไม่นานหรือหมดไป)</option>
                                        <option value="ค่าสาธารณูปโภค">ค่าสาธารณูปโภค (เช่น โทรศัพท์ ไปรษณีย์ วิทยุ)</option>
                                    </optgroup>
                                    <optgroup label="งบลงทุน">
                                        <option value="ค่าครุภัณฑ์">ค่าครุภัณฑ์ (ของที่คงทน ถาวร)</option>
                                    </optgroup>
                                    <option value="อื่น ๆ">อื่น ๆ</option>
                                    <option value="">(ไม่ระบุ)</option>
                                </select>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <select name="department" v-model="member.source" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="สพจ.">สพจ.</option>
                                    <option value="ฝ่ายกิจการนิสิต">ฝ่ายกิจการนิสิต</option>
                                    <option value="กองทุนอื่นของคณะ">กองทุนอื่นของคณะ</option>
                                    <option value="เงินบริจาค/สนับสนุน">เงินบริจาค/สนับสนุน</option>
                                    <option value="เงินฝ่าย/หน่วยงานสพจ.">เงินฝ่าย/หน่วยงานสพจ.</option>
                                    <option value="อื่น ๆ">อื่น ๆ</option>
                                </select>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <jet-input type="number" class="block w-full" min="0" step="0.01" v-model.number="member.amount" required/>
                            </td>
                            <td class="px-1 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 cursor-pointer" viewBox="0 0 20 20" fill="currentColor" @click="expense.splice(index, 1)">
                                    <path fill-rule="evenodd"
                                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/><!-- X -->
                                </svg>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="col-span-6">
                        <p v-if="expense.length === 0" class="mb-2">ไม่มีการใช้งบประมาณ</p>
                        <jet-button class="bg-purple-500 hover:bg-purple-600 focus:border-purple-900" :disabled="form.processing" type="button"
                                    @click="expense.push({name: '', source: '', amount: ''})">
                            เพิ่มรายการงบประมาณ
                        </jet-button>
                        <jet-input-error :message="form.errors.expense" class="mt-2"/>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submitted="submit">
                <template #title>นิสิตผู้รับผิดชอบโครงการ</template>
                <template #description>รายชื่อนิสิตที่รับผิดชอบการดำเนินโครงการ โดยนิสิตคนแรกเป็นนิสิตผู้รับผิดชอบหลัก</template>
                <template #form>
                    <table v-if="organizers.length > 0" class="col-span-6 divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                ชื่อ
                            </th>
                            <th scope="col" class="px-3 pb-2 text-left text-sm font-medium text-gray-500 tracking-wider">
                                เลขประจำตัวนิสิต
                            </th>
                            <th scope="col" class="relative px-1 pb-2"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(member, index) in organizers" :key="member.id">
                            <td class="px-3 py-2 whitespace-nowrap">
                                {{ member.name }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                {{ member.student_id }}
                            </td>
                            <td class="px-1 py-2 whitespace-nowrap text-right text-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 cursor-pointer" viewBox="0 0 20 20" fill="currentColor" @click="organizers.splice(index, 1)">
                                    <path fill-rule="evenodd"
                                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/><!-- X -->
                                </svg>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="col-span-6">
                        <p v-if="organizers.length === 0" class="mb-2 text-red-800">กรุณาเพิ่มนิสิตผู้รับผิดชอบโครงการ</p>
                        <jet-button class="bg-purple-500 hover:bg-purple-600 focus:border-purple-900" :disabled="form.processing" type="button" @click="showStudentIdDialog = true">
                            เพิ่มนิสิตผู้รับผิดชอบโครงการ
                        </jet-button>
                        <jet-input-error :message="form.errors.organizers" class="mt-2"/>
                    </div>
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
        <StudentIdDialog :show-modal="showStudentIdDialog" :list="organizers" @close="showStudentIdDialog = false" @selected="addOrganizer($event)"/>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetButton from '@/Jetstream/Button'
import JetFormSection from '@/Jetstream/FormSection'
import JetInput from '@/Jetstream/Input'
import JetInputError from '@/Jetstream/InputError'
import JetLabel from '@/Jetstream/Label'
import JetSectionBorder from '@/Jetstream/SectionBorder'
import Datepicker from 'vue3-datepicker'
import draggable from 'vuedraggable'
import StudentIdDialog from "../Components/StudentIdDialog";

export default {
    components: {
        StudentIdDialog,
        AppLayout, JetActionMessage,
        JetButton,
        JetFormSection,
        JetInput,
        JetInputError,
        JetLabel,
        JetSectionBorder,
        Datepicker,
        draggable,
    },

    data() {
        const now = new Date();
        const defaultYear = new Date();
        defaultYear.setMonth(now.getMonth() - 1);
        return {
            form: this.$inertia.form({
                _method: this.item.id ? 'PUT' : 'POST',
                name: this.item.name ?? "",
                advisor: this.item.advisor ?? "",
                department_id: this.item.department_id ?? "",
                type: this.item.type ?? "",
                recurrence: this.item.recurrence ?? "",
                year: this.item.year ?? (defaultYear.getFullYear() + 543),
                period_start: this.item.period_start ? new Date(this.item.period_start) : now,
                period_end: this.item.period_end ? new Date(this.item.period_end) : now,
                background: this.item.background ?? "",
                aims: this.item.aims ?? "",
                outcomes: this.item.outcomes ?? "",
            }),
            objectives: this.item.objectives ?? [],
            expense: this.item.expense ?? [],
            organizers: this.item.organizers ?? [],
            showStudentIdDialog: false,
        }
    },

    computed: {
        aimLines() {
            return (this.form.aims.length > 0) ? this.form.aims.split("\n").length : 0;
        },
        outcomeLines() {
            return (this.form.outcomes.length > 0) ? this.form.outcomes.split("\n").length : 0;
        },
    },

    methods: {
        addOrganizer(student) {
            if (!this.organizers.find(s => s.student_id === student.student_id)) {
                this.organizers.push(student);
            }
            // this.showStudentIdDialog = false;
        },
        submit() {
            this.form.transform(data => ({
                ...data,
                objectives: this.objectives,
                expense: this.expense,
                organizers: this.organizers,
            })).post(this.item.id
                ? this.route('projects.update', {project: this.item.id})
                : this.route('projects.store')
            )
        },
        forAcademicPresentation() {
            this.form.type = 'once';
            this.form.recurrence = 0;
            this.form.department_id = 38;
        }
    },

    props: {
        item: Object,
        static_departments: Array,
    }
};
</script>
