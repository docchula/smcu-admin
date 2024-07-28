<template>
    <jet-banner />
    <div class="relative bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:pb-28 xl:pb-32">
                <div class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="text-green-600">SMCU</span> <span class="text-gray-600">Administrative System</span>
                        </h1>
                        <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                            ระบบบริหารงานสโมสร สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย
                        </p>
                        <!--div class="">
                        </div-->
                        <div v-if="canLogin" class="mt-5 sm:mt-12 sm:flex sm:justify-center lg:justify-start">
                            <div v-if="$page.props.auth.user" class="mt-3 sm:mt-0 sm:ml-3">
                                <Link :href="route('dashboard')"
                                              class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 md:py-4 md:text-lg md:px-10">
                                    หน้าแรก
                                </Link>
                            </div>
                            <div v-else>
                                <p class="block mb-4">เมื่อกดปุ่มต่อไปนี้ ถือว่าท่านได้ยินยอมให้มีการดำเนินการต่อข้อมูลส่วนบุคคลตาม
                                    <Link :href="route('policy.show')" class="text-green-500">นโยบายการคุ้มครองข้อมูลส่วนบุคคล</Link>
                                </p>
                                <div class="rounded-md shadow">
                                    <a :href="route('login.google')"
                                       class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 md:py-4 md:text-lg md:px-10">
                                        Log in with Docchula
                                    </a>
                                </div>
                                <p class="pt-4 text-xs text-gray-400">ท่านอาจไม่สามารถเข้าสู่ระบบได้จาก in-app browser (เช่น Facebook หรือ LINE) ให้นำ URL ไปเปิดในเว็บเบราเซอร์ เช่น Chrome หรือ Safari</p>
                            </div>
                        </div>
                        <p v-if="healthCheck !== 0" class="mt-8 lg:mt-12 sm:mb-4 text-xs text-gray-400">
                            สถานะ :
                            <span v-if="healthCheck === 1" class="text-green-400">
                                ⬤ ระบบพร้อมใช้งาน
                            </span>
                            <span v-else class="text-red-400">
                                ⬤ ระบบมีปัญหา อาจไม่สามารถดู/บันทึกข้อมูลบางอย่างได้ กรุณาติดต่อผู้ดูแลระบบ
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="container mx-auto lg:mt-16 xl:mt-32">
                <div class="mt-8 border-t-2 border-gray-200 flex flex-col">
                    <div class="py-6 px-4">
                        <p class="text-sm text-green-700 font-bold mb-2">
                            <a href="https://docchula.com">The Student Union of the Faculty of Medicine, Chulalongkorn University</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import JetBanner from '@/Jetstream/Banner.vue';
import {Link} from '@inertiajs/vue3';
import {onMounted, ref} from 'vue';
import axios from 'axios';

defineProps({
    canLogin: Boolean,
});
const healthCheck = ref(0);
onMounted(() => {
    axios.get('/health').then(response => {
        healthCheck.value = response.data ? 1 : -1;
    }).catch(() => {
        healthCheck.value = -1;
    });
});
</script>
