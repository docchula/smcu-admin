<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                แผนยุทธศาสตร์สโมสร ปีการศึกษา 2565
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <p class="py-1.5">
                <span class="inline-flex bg-green-600 text-white rounded-full h-6 px-3 mr-2 justify-center items-center">วิสัยทัศน์ (Vision)</span>
                เป็นองค์กรที่มุ่งพัฒนาศักยภาพและคุณภาพชีวิตของนิสิตแพทย์ โดยการมีส่วนร่วมของนิสิตแพทย์ทุกคน
            </p>
            <p class="py-1.5">
                <span class="inline-flex bg-gray-600 text-white rounded-full h-6 px-3 mr-2 justify-center items-center">พันธกิจ (Mission)</span>
                (1) สนับสนุนการดำเนินกิจกรรมโดยนิสิต เพื่อพัฒนาศักยภาพทั้งในด้านวิชาการและด้านอื่น ๆ และเพื่อส่งเสริมคุณภาพชีวิตของนิสิต และ
                (2) สร้างเสริมการมีส่วนร่วมของนิสิตในการบริหารงานของคณะที่เกี่ยวข้องกับนิสิต
            </p>
            <p>
                <span class="inline-flex bg-pink-600 text-white rounded-full h-6 px-3 mr-2 mb-2 justify-center items-center">ยุทธศาสตร์ #1</span>
                พัฒนาศักยภาพนิสิต ทั้งในด้านวิชาการ ศิลปะ วัฒนธรรม พลานามัย และทักษะชีวิต
            </p>
            <p>
                <span class="inline-flex bg-pink-600 text-white rounded-full h-6 px-3 mr-2 mb-2 justify-center items-center">ยุทธศาสตร์ #2</span>
                สร้างเสริมสวัสดิการและพัฒนาคุณภาพชีวิตของนิสิต ทั้งด้านร่างกาย จิตใจ สังคม และสภาพแวดล้อม
            </p>
            <p>
                <span class="inline-flex bg-pink-600 text-white rounded-full h-6 px-3 mr-2 mb-2 justify-center items-center">ยุทธศาสตร์ #3</span>
                สร้างเสริมความสัมพันธ์อันดีระหว่างนิสิต นิสิตเก่า คณาจารย์ และนิสิตนักศึกษาสถาบันอื่นทั้งในและต่างประเทศ
            </p>
            <p>
                <span class="inline-flex bg-pink-600 text-white rounded-full h-6 px-3 mr-2 mb-2 justify-center items-center">ยุทธศาสตร์ #4</span>
                สร้างเสริมการมีส่วนร่วมของนิสิตในการพัฒนาสู่การเป็นโรงเรียนแพทย์ที่เป็นผู้นำในระดับนานาชาติ
            </p>
            <p>
                <span class="inline-flex bg-pink-600 text-white rounded-full h-6 px-3 mr-2 mb-2 justify-center items-center">ยุทธศาสตร์ #5</span>
                ส่งเสริมคุณธรรม จริยธรรมของนิสิต
            </p>
            <p>
                <span class="inline-flex bg-pink-600 text-white rounded-full h-6 px-3 mr-2 mb-2 justify-center items-center">ยุทธศาสตร์ #6</span>
                ช่วยเหลือสังคมในการบรรลุเป้าหมายการพัฒนาอย่างยั่งยืน
            </p>
            <p>
                <span class="inline-flex bg-pink-600 text-white rounded-full h-6 px-3 mr-2 mb-2 justify-center items-center">ยุทธศาสตร์ #7</span>
                พัฒนาการดำเนินงานของสโมสรให้มีการบริหารจัดการที่ดี
            </p>
            <!-- div class="mt-1 py-3 grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-300 md:border-0">
                <div>
                    <span class="inline-flex bg-pink-600 text-white rounded-full h-6 px-3 mr-2 mb-2 justify-center items-center">เป้าหมาย (Goal) #1</span><br/>
                    นิสิตได้ดำเนินการหรือเข้าร่วมกิจกรรมตามความสนใจ
                </div>
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg md:col-span-2">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 sm:px-6 py-1 text-left text-xs font-medium text-gray-500 tracking-wider">ยุทธศาสตร์ (Strategy)</th>
                            <th scope="col" class="px-3 sm:px-6 py-1 text-right text-xs font-medium text-gray-500 tracking-wider">โครงการ</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-3 sm:px-6 py-2">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    1.1 จัดกิจกรรมด้านวิชาการ วิทยาศาสตร์ และเทคโนโลยี
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">5</td>
                        </tr>
                        <tr>
                            <td class="px-3 sm:px-6 py-2">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    1.2 จัดกิจกรรมด้านศิลปะและวัฒนธรรม
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">9</td>
                        </tr>
                        <tr>
                            <td class="px-3 sm:px-6 py-2">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    1.3 จัดกิจกรรมด้านกีฬา
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">6</td>
                        </tr>
                        <tr>
                            <td class="px-3 sm:px-6 py-2">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    1.4 จัดกิจกรรมทั่วไป
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">3</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div -->
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
        list: Array
    }
};
</script>
