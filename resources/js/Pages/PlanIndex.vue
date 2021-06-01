<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                แผนยุทธศาสตร์สโมสร วาระ 2565
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <p class="py-1.5">
                <span class="inline-flex bg-green-600 text-white rounded-full h-6 px-3 mr-2 justify-center items-center">วิสัยทัศน์ (Vision)</span>
                ให้นิสิตได้ทำกิจกรรมที่อยากทำ มีความสุข
            </p>
            <p class="py-1.5">
                <span class="inline-flex bg-gray-600 text-white rounded-full h-6 px-3 mr-2 justify-center items-center">พันธกิจ (Mission)</span>
                ผู้สนับสนุนและส่งเสริมการจัดกิจกรรมต่าง ๆ ของนิสิตเพื่อพัฒนานิสิต และดูแลสวัสดิภาพของนิสิต
            </p>
            <div class="mt-1 py-3 grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-300 md:border-0">
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
            </div>
            <div class="mt-1 py-3 grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-300 md:border-0">
                <div>
                    <span class="inline-flex bg-pink-600 text-white rounded-full h-6 px-3 mr-2 mb-2 justify-center items-center">เป้าหมาย (Goal) #2</span><br/>
                    นิสิตได้พัฒนาตนเอง
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
                                    2.1 ส่งเสริมคุณธรรม จริยธรรม และให้นิสิตมีความรับผิดชอบต่อตนเอง หมู่คณะ และสังคม
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">4</td>
                        </tr>
                        <tr>
                            <td class="px-3 sm:px-6 py-2">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    2.2 ส่งเสริมให้นิสิตมีส่วนร่วมในการบำเพ็ญสาธารณะประโยชน์
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">5</td>
                        </tr>
                        <tr>
                            <td class="px-3 sm:px-6 py-2">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    2.3 ส่งเสริมและสนับสนุนให้นิสิตปฏิบัติตามกฎระเบียบข้อบังคับของคณะ และมหาวิทยาลัย
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">2</td>
                        </tr>
                        <tr>
                            <td class="px-3 sm:px-6 py-2">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    2.4 พัฒนาให้นิสิตมีทักษะในการทำงานร่วมกันและประสบความสำเร็จ
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">15</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-1 py-3 grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-300 md:border-0">
                <div>
                    <span class="inline-flex bg-pink-600 text-white rounded-full h-6 px-3 mr-2 mb-2 justify-center items-center">เป้าหมาย (Goal) #3</span><br/>
                    นิสิตศึกษาในคณะโดยมีสวัสดิภาพ
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
                                    3.1 รักษาสิทธิ และผลประโยชน์ของนิสิต
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">6</td>
                        </tr>
                        <tr>
                            <td class="px-3 sm:px-6 py-2">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    3.2 ส่งเสริมความสัมพันธ์อันดีระหว่างนิสิต อาจารย์ ศิษย์เก่า บุคลากร
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">3</td>
                        </tr>
                        <tr>
                            <td class="px-3 sm:px-6 py-2">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    3.3 ส่งเสริมสวัสดิการของนิสิต
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">7</td>
                        </tr>
                        <tr>
                            <td class="px-3 sm:px-6 py-2">
                                <inertia-link :href="route('projects.show', {project: 'xxx'})" class="hover:underline">
                                    3.4 จัดกิจกรรมพัฒนาสุขภาพและพลานามัยของนิสิต
                                </inertia-link>
                            </td>
                            <td class="px-3 sm:px-6 py-2 text-right">4</td>
                        </tr>
                        </tbody>
                    </table>
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
        list: Array
    }
};
</script>
