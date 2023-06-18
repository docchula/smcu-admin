<template>
    <div class="font-sans text-gray-900 antialiased">
        <div class="pt-4 bg-gray-100">
            <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
                <div>
                    <jet-authentication-card-logo />
                </div>

                <div v-html="policy" class="w-full sm:max-w-2xl my-6 p-6 bg-white shadow-md overflow-hidden sm:rounded-lg prose">
                </div>

                <inertia-link href="/" class="mb-6 block flex items-center text-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                    </svg>
                    <p>กลับสู่หน้าหลัก</p>
                </inertia-link>
            </div>
        </div>
    </div>
</template>

<script>
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo.vue'

export default {
    props: ['policy'],

    components: {
        JetAuthenticationCardLogo,
    },
}
</script>
