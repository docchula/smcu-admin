<template>
    <app-layout>
        <template #header>
            <inertia-link :href="item.id ? route('documents.show', {document: item.id}) : route('documents.index')" class="mb-4 block flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="inline h-3 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" class="text-gray-500"/>
                </svg>
                <p v-if="item.id">
                    {{ item.name }}
                </p>
                <p v-else>สารบรรณ</p>
            </inertia-link>
            <h2 v-if="item.id" class="font-semibold text-xl text-gray-800 leading-tight">
                แก้ไข {{ item.name }}
            </h2>
            <template v-else>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">สร้างเอกสารใหม่</h2>
                <p class="mt-2 text-gray-500">กรุณาร่างเอกสารให้เสร็จก่อน เหลือเฉพาะเลขที่เอกสาร</p>
            </template>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <jet-form-section @submitted="submit">
                <template #title>ข้อมูลพื้นฐาน</template>
                <template #description></template>
                <template #form>
                    <div class="col-span-6">
                        <jet-label for="title" value="หัวเรื่อง"/>
                        <jet-input id="title" type="text" class="mt-1 block w-full" v-model.trim="form.title" ref="title" required />
                        <jet-input-error v-if="form.errors.title" :message="form.errors.title" class="mt-2"/>
                        <p v-else class="mt-2 text-xs text-gray-500">ข้อความสั้น ๆ ที่ทำให้ผู้รับเข้าใจความประสงค์หรือเนื้อหาโดยสังเขป</p>
                    </div>
                    <div class="col-span-6">
                        <jet-label for="recipient" value="ผู้รับ"/>
                        <jet-input id="recipient" type="text" class="mt-1 block w-full" v-model.trim="form.recipient" ref="recipient" required />
                        <jet-input-error v-if="form.errors.recipient" :message="form.errors.recipient" class="mt-2"/>
                        <p v-else class="mt-2 text-xs text-gray-500">ควรใช้ชื่อตำแหน่ง (ถ้ามี) เช่น รองคณบดีฝ่ายกิจการนิสิต</p>
                    </div>
                    <div class="col-span-6">
                        <label for="department" class="block text-sm font-medium text-gray-700">หน่วยงานที่รับผิดชอบ</label>
                        <select id="department" name="department"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                v-model="form.department" required>
                            <option v-for="department in static_departments" v-bind:key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submitted="submit">
                <template #title>โครงการ</template>
                <template #description>กรณีหนังสือนี้มีความเกี่ยวข้องกับการดำเนินโครงการ ให้ใส่เลขที่หนังสือขออนุมัติโครงการนั้น ๆ</template>
                <template #form>
                    <div class="col-span-6">
                        <jet-label for="project" value="เลขที่โครงการ (ถ้ามี)"/>
                        <jet-input id="project" type="text" class="mt-1 block w-full" v-model.trim="form.project" ref="project" required placeholder="เช่น 250/2564"/>
                        <jet-input-error :message="form.errors.project" class="mt-2"/>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submitted="submit">
                <template #title>ออกหนังสือหลายฉบับ</template>
                <template #description>กรณีต้องการออกหนังสือเรื่องเดียวกันไปยังผู้รับหลายคน (เช่น หนังสือเชิญ)</template>
                <template #form>
                    <div class="col-span-6">
                        <jet-label for="amount" value="จำนวนหนังสือ"/>
                        <jet-input id="amount" type="number" class="mt-1 block w-full" v-model.number="form.amount" ref="amount" required/>
                        <jet-input-error :message="form.errors.amount" class="mt-2"/>
                    </div>
                </template>
            </jet-form-section>
            <jet-section-border/>
            <jet-form-section @submitted="submit">
                <template #title>ไฟล์เอกสาร</template>
                <template #description>กรุณาแนบไฟล์เอกสารทั้งฉบับ รวมเป็นไฟล์เดียว ในรูปแบบ pdf หรือ docx</template>
                <template #form>
                        <div class="col-span-6 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload a file</span>
                                        <input id="file-upload" name="file-upload" type="file" class="sr-only">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    PDF or DOCX up to 10MB
                                </p>
                            </div>
                        </div>
                </template>
                <template #actions>
                    <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                        Saved.
                    </jet-action-message>

                    <jet-button type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Save
                    </jet-button>
                </template>
            </jet-form-section>
        </div>
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

export default {
    components: {
        AppLayout, JetActionMessage,
        JetButton,
        JetFormSection,
        JetInput,
        JetInputError,
        JetLabel,
        JetSectionBorder,
    },

    data() {
        return {
            form: this.$inertia.form({
                _method: this.item.id ? 'PUT' : 'POST',
                title: this.item.title ?? "",
                recipient: this.item.recipient ?? "",
                department: this.item.department ?? "",
                amount: this.item.amount ?? "",
                project: this.item.project ?? "",
                outcomes: this.item.outcomes ?? "",
            }),
        }
    },

    methods: {
        submit() {
            this.form.post(this.item.id
                ? this.route('documents.update', {participant: this.item.id})
                : this.route('documents.store')
            )
        }
    },

    props: {
        item: Object,
        static_departments: Array,
    }
};
</script>
