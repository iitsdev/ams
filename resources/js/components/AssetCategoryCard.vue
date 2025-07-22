<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Laptop, PlusCircle } from 'lucide-vue-next';

defineProps({
    category: Object,
});
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-center justify-between pb-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <Laptop class="h-6 w-6" />
                </div>
                <CardTitle class="text-lg font-semibold">
                    {{ category?.name }} ({{ category?.total_assets_count }})
                </CardTitle>
            </div>
            <Button size="icon" variant="ghost">
                <PlusCircle class="h-5 w-5" />
            </Button>
        </CardHeader>
        <CardContent>
            <ul class="space-y-1 text-sm text-muted-foreground">
                <li class="flex justify-between">
                    <span>In Use</span>
                    <span class="font-medium text-foreground">{{ category?.assets_in_use_count }}</span>
                </li>
                <li class="flex justify-between">
                    <span>In Store</span>
                    <span class="font-medium text-foreground">{{ category?.assets_in_store_count }}</span>
                </li>
                <li class="flex justify-between">
                    <span>In Repair</span>
                    <span class="font-medium text-foreground">{{ category?.assets_in_repair_count }}</span>
                </li>
            </ul>
        </CardContent>
    </Card>
</template>