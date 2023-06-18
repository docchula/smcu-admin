<template>
    <div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <div>
                <jet-application-logo class="block h-12 w-auto"/>
            </div>
            <div class="mt-6 text-2xl">
                ระบบบริหารงานสโมสร
                <span class="lg:hidden"><br/></span>
                สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย
            </div>
            <div class="mt-6 text-gray-500">
                ประกอบด้วยระบบงานสารบรรณ และฐานข้อมูลโครงการของสโมสร เพื่อเป็นข้อมูลในการบริหารจัดการ กำหนดนโยบาย วางแผนงบประมาณ และติดตามประเมินผลการดำเนินงาน ให้ทุกโครงการกรอกข้อมูลลงในเว็บไซต์เพื่อใช้ขออนุมัติดำเนินโครงการ
            </div>
            <p class="text-gray-500 border border-gray-500 p-3 w-full mt-4 rounded-md transition">
                การส่งเอกสารเพื่อเตรียมจัดกิจกรรม ควรดำเนินการล่วงหน้าอย่างน้อย 1 เดือน หรือตามลักษณะของกิจกรรม
            </p>
        </div>

        <div class="bg-gray-400 bg-opacity-25 text-gray-500 px-6 py-1">
            ขั้นตอนการดำเนินโครงการ (<a :href="route('manual')" target="_blank" class="font-semibold text-green-700">คู่มือ</a>)
        </div>
        <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2">
            <div class="p-6">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">1. วางแผนและขอจัดสรรงบประมาณ</div>
                </div>
                <div class="ml-12">
                    <div class="mt-2 text-sm text-gray-500">
                        นิสิตผู้รับผิดชอบโครงการวางแผนการดำเนินงานและปรึกษากรรมการสพจ. กรณีส่วนงานมีความประสงค์จะจัดกิจกรรมหรือโครงการที่ใช้งบประมาณสโมสร ให้แจ้งคณะกรรมการสโมสรเพื่อขอจัดสรรงบประมาณ โดยควรทำในช่วงต้นวาระ
                    </div>
                    <!-- inertia-link :href="route('budget.index')">
                        <div class="mt-3 flex items-center text-sm font-semibold text-indigo-700">
                            <div>ไปที่หน้า งบประมาณ</div>

                            <div class="ml-1 text-indigo-500">
                                <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                    <path fill-rule="evenodd"
                                          d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                          clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </inertia-link -->
                </div>
            </div>

            <div class="p-6 border-t border-gray-200 md:border-t-0 md:border-l">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">2. ขออนุมัติดำเนินโครงการ</div>
                </div>
                <div class="ml-12">
                    <div class="mt-2 text-sm text-gray-500">
                        ลงทะเบียนข้อมูลโครงการ และส่งหนังสือขออนุมัติดำเนินโครงการ โดยระบุเลขที่โครงการในหนังสือด้วย
                    </div>
                    <inertia-link :href="route('projects.create')">
                        <div class="mt-3 flex items-center text-sm font-semibold text-green-700">
                            <div>ไปที่หน้า เพิ่มโครงการ</div>

                            <div class="ml-1 text-green-500">
                                <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                    <path fill-rule="evenodd"
                                          d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                          clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </inertia-link>
                </div>
            </div>

            <div class="p-6 border-t border-gray-200">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">3. การออกหนังสือสพจ.</div>
                </div>

                <div class="ml-12">
                    <div class="mt-2 text-sm text-gray-500">
                        ในการดำเนินงาน หากมีความประสงค์จะออกหนังสือสพจ. เพื่อประสานงานกับหน่วยงานต่าง ๆ ให้ลงทะเบียนข้อมูลในเว็บไซต์นี้
                    </div>
                    <inertia-link :href="route('documents.index')">
                        <div class="mt-3 flex items-center text-sm font-semibold text-green-700">
                            <div>ไปที่หน้า สารบรรณ</div>

                            <div class="ml-1 text-green-500">
                                <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                    <path fill-rule="evenodd"
                                          d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                          clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </inertia-link>
                </div>
            </div>

            <div class="p-6 border-t border-gray-200 md:border-l">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">4. เบิกค่าใช้จ่าย (ถ้ามี)</div>
                </div>

                <div class="ml-12">
                    <div class="mt-2 text-sm text-gray-500">
                        เมื่อส่วนงานใช้จ่ายตามที่ได้รับอนุมัติแล้ว ให้นำหลักฐานการจ่ายแจ้งต่อเหรัญญิกสโมสรหรือฝ่ายกิจการนิสิตเพื่อขอเบิกค่าใช้จ่าย (ขึ้นอยู่กับแหล่งงบประมาณ)
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-gray-200 md:border-l md:col-span-2">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">5. รายงานผล</div>
                </div>

                <div class="ml-12">
                    <div class="mt-2 text-sm text-gray-500">
                        เมื่อสิ้นสุดการดำเนินโครงการ ให้วัดประเมินผลเป็นข้อมูลเชิงปริมาณว่าโครงการบรรลุเป้าหมายและวัตถุประสงค์เพียงใด
                    </div>
                    <!-- inertia-link :href="route('plan.index')">
                        <div class="mt-3 flex items-center text-sm font-semibold text-indigo-700">
                            <div>ไปที่หน้า แผนงาน</div>

                            <div class="ml-1 text-indigo-500">
                                <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                    <path fill-rule="evenodd"
                                          d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                          clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </inertia-link -->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import JetApplicationLogo from '@/Jetstream/ApplicationLogo.vue'

export default {
    components: {
        JetApplicationLogo,
    },
}
</script>
