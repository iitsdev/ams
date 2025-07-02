<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Pie } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
    Title,
} from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend, Title);

const props = defineProps({
    stats: Object,
    assetsByStatusChart: Object,
});

const chartData = computed(() => props.assetsByStatusChart);

const chartOptions = {
    responsive: true,
    maintainAspectRation: false,
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex items-center justify-between m-2">
                <h2 class="text-2xl font-bold tracking-tight">Dashboard</h2>
            </div>
        </template>

        <div class="p-1">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Assets</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Assets Deployed</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats?.deployed }}</div>
                    </CardContent>
                </Card>
            </div>

            <div class="mt-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Assets by Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-[350px]">
                            <Pie :data="assetsByStatusChart" :options="chartOptions" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
