<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import AssetCategoryCard from '@/components/AssetCategoryCard.vue'; // <-- 1. Import our new component

// Define the prop passed from the DashboardController
defineProps({
    assetSummary: Object,
});
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <template #header>
            <h2 class="text-2xl font-bold tracking-tight">
                Asset Dashboard
            </h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

            <AssetCategoryCard v-for="category in assetSummary" :key="category.id" :category="category" />

        </div>
    </AppLayout>
</template>