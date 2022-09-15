<template>
    <jet-dialog-modal :show="showModal" @close="$emit('close')">
        <template #title>
            ค้นหานิสิตด้วยเลขประจำตัวนิสิต
        </template>

        <template #content>
            <div>
                <jet-label for="sid" value="เลขประจำตัวนิสิต"/>
                <jet-input id="sid" type="text" class="mt-1 block w-full" v-model.trim="addStudentId" required placeholder="10 หลัก" @keyup.enter="onInputEnter"/>
                <jet-input-error v-if="keywordError" :message="keywordError" class="mt-2"/>
                <a v-else-if="addStudentId !== $page.props.user.student_id && !list.find(x => x.student_id === $page.props.user.student_id)"
                   class="text-sm cursor-pointer text-green-500"
                   @click="addStudentId = $page.props.user.student_id">
                    {{ $page.props.user.student_id }}
                </a>
            </div>
            <div v-if="searchResult.length > 0" class="mt-4 border-l border-r border-b border-gray-200">
                <div class="border-t px-3 py-2 flex" :class="{'hover:bg-gray-50 cursor-pointer': !list.find(x => x.student_id === item.student_id)}" v-for="item in searchResult" :key="item.id" @click="selectStudent(item)">
                    <div class="flex-auto items-center">
                        {{ item.name }} <span v-if="item.nickname">({{ item.nickname }})</span>
                    </div>
                    <div class="flex-initial items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" v-if="list.find(x => x.student_id === item.student_id)" class="block h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" v-else class="block h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <jet-secondary-button @click.native="$emit('close')">
                Close
            </jet-secondary-button>
        </template>
    </jet-dialog-modal>
</template>

<script>
import JetDialogModal from "@/Jetstream/DialogModal";
import JetInput from "@/Jetstream/Input";
import JetInputError from "@/Jetstream/InputError";
import JetLabel from "@/Jetstream/Label";
import JetSecondaryButton from "@/Jetstream/SecondaryButton";

export default {
    components: {
        JetDialogModal,
        JetInput,
        JetInputError,
        JetLabel,
        JetSecondaryButton
    },
    data() {
        return {
            addStudentId: '',
            keywordError: '',
            searchResult: []
        }
    },
    methods: {
        search: _.debounce(function (keyword) {
            this.keywordError = 'กำลังค้นหา...';
            axios.get(route('projects.searchNewParticipant'), {
                params: {
                    q: keyword
                },
            }).then((response) => {
                this.keywordError = '';
                this.searchResult = [response.data];
            }).catch((error) => {
                this.keywordError = 'Error! Could not reach the API. ' + error;
            })
        }, 500),
        onInputEnter() {
            if (this.searchResult.length === 1) {
                this.$emit('selected', this.searchResult[0]);
                this.addStudentId='';
            }
        },
        selectStudent(item) {
            if (this.list.filter(x => x.student_id === item.student_id).length === 0) {
                this.$emit('selected', item);
                this.addStudentId = '';
            }
        }
    },
    watch: {
        // whenever question changes, this function will run
        addStudentId: function (newValue, oldValue) {
            this.keywordError = "กำลังพิมพ์...";
            if (newValue === "") {
                this.keywordError = "";
                return;
            } else if (!newValue.match(/^\d{10}$/)) {
                this.keywordError = 'เลขประจำตัวนิสิตต้องมี 10 หลัก';
                return;
            }
            this.search(newValue);
        },
    },
    props: {'showModal': Boolean, 'list': Array},
    emits: ['close', 'selected']
}
</script>
