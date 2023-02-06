<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                หน้าหลัก
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <welcome />
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
    }
</script>
