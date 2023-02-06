<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                หน้าหลัก
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="projectsAwaitingSummary.length > 0" class="bg-blue-100 border-blue-500 text-blue-500 border-l-4 rounded p-4 mb-6" role="alert">
                    <p class="font-bold">
                        มี {{ projectsAwaitingSummary.length }} โครงการที่กำลังดำเนินงานอยู่
                    </p>
                    <p>เมื่อเสร็จสิ้นโครงการแล้ว ให้ส่งรายงานผลการดำเนินโครงการ</p>
                    <table class="text-sm">
                        <tr v-for="project in projectsAwaitingSummary">
                            <td>•</td>
                            <td class="px-2">
                                <inertia-link :href="route('projects.show', {project: project.id})" class="hover:text-blue-600 text-xs">
                                    {{ project.year }}-{{ project.number }}
                                </inertia-link>
                            </td>
                            <td>
                                <inertia-link :href="route('projects.show', {project: project.id})" class="hover:text-blue-600">
                                    {{ project.name }}
                                </inertia-link>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <welcome/>
                </div>
            </div>
        </div>
        <p class="pb-6 max-w-7xl mx-auto sm:px-6 lg:px-8 text-xs text-gray-400 text-center">
            สร้างสรรค์โดย ฝ่ายเทคโนโลยีสารสนเทศ สพจ. | <a href="https://github.com/docchula/smcu-admin" target="_blank" class="text-green-400">Source code</a><br/>
            หากพบปัญหาในการใช้งาน ติดต่ออีเมล itdivision@docchula.com
        </p>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'
import Welcome from '@/Jetstream/Welcome'

export default {
    components: {
        AppLayout,
        Welcome,
    },
    props: {
        projectsAwaitingSummary: Array,
    },
}
</script>
