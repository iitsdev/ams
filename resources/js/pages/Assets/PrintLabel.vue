<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    asset: Object,
});

// This hook runs as soon as the component is loaded
onMounted(() => {
    window.print();
});
</script>

<template>

    <Head :title="`Print Label - ${props.asset?.asset_tag}`" />

    <div class="print-container p-4">
        <div class="label-content border-2 border-dashed border-black p-4 flex flex-col items-center gap-2">
            <h1 class="text-lg font-bold">
                {{ props.asset?.name }}
            </h1>
            <img :src="route('assets.barcode', props.asset?.id)" alt="Barcode" class="w-full max-w-xs">
            <!-- <p class="text-center font-mono">
                {{ props.asset?.asset_tag }}
            </p> -->
        </div>
    </div>
</template>

<style>
@media print {

    /* 1. Hide everything on the page by default */
    body * {
        visibility: hidden;
    }

    /* 2. Make the print container and ALL its children visible */
    .print-container,
    .print-container * {
        visibility: visible;
    }

    /* 3. Ensure the print container takes up the full page */
    .print-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 50%;
    }
}
</style>