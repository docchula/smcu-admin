<template>
    <div>
        <div class="my-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
            </div>
            <input type="text" id="searchFood" placeholder="Search" ref="keyword" autofocus
                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-8 sm:text-sm border-gray-300 rounded-md"
                   v-bind:value="value"
                   v-on:input="$emit('input', $event.target.value)"/>
        </div>
        <p class="text-sm text-blue-600" v-if="status">{{ status }}</p>
    </div>
</template>

<script>
export default {
    props: {
        value: String,
        status: String
    },
}
</script>
