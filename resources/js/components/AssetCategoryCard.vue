<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\components\AssetCategoryCard.vue

import { Laptop } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    assets_count: number;
    in_use: number;
    in_store: number;
    in_repair: number;
}

defineProps<{
    category: Category;
}>();
</script>

<template>
    <Link :href="route('assets.index', { category: category.id })"
        class="block p-4 border rounded-lg hover:shadow-md transition-all hover:border-primary/50 bg-card">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 rounded-lg">
                    <Laptop class="h-5 w-5 text-primary" />
                </div>
                <div>
                    <h3 class="font-semibold">{{ category.name }}</h3>
                    <p class="text-sm text-muted-foreground">({{ category.assets_count }})</p>
                </div>
            </div>
            <button class="p-2 hover:bg-accent rounded-full transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="1" />
                    <circle cx="12" cy="5" r="1" />
                    <circle cx="12" cy="19" r="1" />
                </svg>
            </button>
        </div>

        <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">In Use</span>
                <span class="font-medium">{{ category.in_use }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">In Store</span>
                <span class="font-medium">{{ category.in_store }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">In Repair</span>
                <span class="font-medium">{{ category.in_repair }}</span>
            </div>
        </div>
    </Link>
</template>