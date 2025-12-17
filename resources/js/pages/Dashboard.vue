<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\pages\Dashboard.vue

import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import AssetCategoryCard from '@/components/AssetCategoryCard.vue';
import { computed } from 'vue';
import {
    Package,
    DollarSign,
    Users,
    CheckCircle,
    AlertCircle,
    TrendingDown,
    Calendar,
    Wrench,
    UserCheck,
    ArrowRight,
    LayoutGrid
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import BarChart from '@/components/charts/BarChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import LineChart from '@/components/charts/LineChart.vue';

// Define the Props interface and use defineProps once
interface Props {
    assetSummary: any[]
    stats: {
        total_assets: number
        total_value: number
        assigned_assets: number
        available_assets: number
        total_users: number
        total_categories: number
        current_value: number
        depreciation: number
        depreciation_percentage: number
    }
    charts: {
        assets_by_status: Array<{ name: string; count: number }>
        assets_by_category: Array<{ name: string; count: number; value: number }>
        assets_by_location: Array<{ name: string; count: number }>
        monthly_trends: Array<{ month: string; count: number }>
        top_users: Array<{ id: number; name: string; asset_count: number }>
    }
    recent: {
        assignments: Array<any>
        maintenance: Array<any>
        warranty_expiries: Array<any>
    }
}

const props = defineProps<Props>()

// Chart Data Configurations
const statusChartData = computed(() => ({
    labels: props.charts.assets_by_status.map(item => item.name),
    datasets: [{
        data: props.charts.assets_by_status.map(item => item.count),
        backgroundColor: [
            'rgba(34, 197, 94, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(234, 179, 8, 0.8)',
            'rgba(239, 68, 68, 0.8)',
        ],
        borderColor: [
            'rgba(34, 197, 94, 1)',
            'rgba(59, 130, 246, 1)',
            'rgba(234, 179, 8, 1)',
            'rgba(239, 68, 68, 1)',
        ],
        borderWidth: 2,
    }]
}))

const categoryChartData = computed(() => ({
    labels: props.charts.assets_by_category.map(item => item.name),
    datasets: [{
        label: 'Number of Assets',
        data: props.charts.assets_by_category.map(item => item.count),
        backgroundColor: 'rgba(59, 130, 246, 0.8)',
        borderColor: 'rgba(59, 130, 246, 1)',
        borderWidth: 2,
    }]
}))

const locationChartData = computed(() => ({
    labels: props.charts.assets_by_location.map(item => item.name),
    datasets: [{
        label: 'Assets by Location',
        data: props.charts.assets_by_location.map(item => item.count),
        backgroundColor: 'rgba(139, 92, 246, 0.8)',
        borderColor: 'rgba(139, 92, 246, 1)',
        borderWidth: 2,
    }]
}))

const trendsChartData = computed(() => ({
    labels: props.charts.monthly_trends.map(item => item.month),
    datasets: [{
        label: 'New Assets',
        data: props.charts.monthly_trends.map(item => item.count),
        borderColor: 'rgba(59, 130, 246, 1)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true,
    }]
}))

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value || 0)
}

const getWarrantyBadgeVariant = (daysRemaining: number) => {
    if (daysRemaining < 0) return 'destructive'
    if (daysRemaining <= 7) return 'destructive'
    if (daysRemaining <= 30) return 'default'
    return 'secondary'
}

const formatDaysRemaining = (days: number) => {
    const roundedDays = Math.floor(Math.abs(days))
    return roundedDays
}
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">Dashboard</h2>
                    <p class="text-muted-foreground">Overview of your asset management system</p>
                </div>
                <Link :href="route('assets.index')">
                    <Button>
                        <LayoutGrid class="h-4 w-4 mr-2" />
                        View All Assets
                    </Button>
                </Link>
            </div>
        </template>

        <div class="space-y-6 max-h-[calc(100vh-12rem)] overflow-y-auto pr-2">
            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Assets -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Assets</CardTitle>
                        <Package class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_assets }}</div>
                        <p class="text-xs text-muted-foreground">
                            Across {{ stats.total_categories }} categories
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Value -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Value</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.total_value) }}</div>
                        <p class="text-xs text-muted-foreground flex items-center gap-1">
                            <TrendingDown class="h-3 w-3 text-red-500" />
                            {{ stats.depreciation_percentage }}% depreciated
                        </p>
                    </CardContent>
                </Card>

                <!-- Assigned Assets -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Assigned Assets</CardTitle>
                        <UserCheck class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.assigned_assets }}</div>
                        <p class="text-xs text-muted-foreground">
                            To {{ stats.total_users }} users
                        </p>
                    </CardContent>
                </Card>

                <!-- Available Assets -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Available Assets</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.available_assets }}</div>
                        <p class="text-xs text-muted-foreground">
                            Ready to assign
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Asset Categories Section -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <LayoutGrid class="h-5 w-5" />
                        Assets by Category
                    </CardTitle>
                    <CardDescription>Quick overview of asset distribution</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <AssetCategoryCard v-for="category in assetSummary" :key="category.id" :category="category" />
                    </div>
                </CardContent>
            </Card>

            <!-- Charts Grid -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Assets by Status -->
                <Card>
                    <CardHeader>
                        <CardTitle>Assets by Status</CardTitle>
                        <CardDescription>Distribution of asset statuses</CardDescription>
                    </CardHeader>
                    <CardContent class="h-[300px]">
                        <DoughnutChart :data="statusChartData" />
                    </CardContent>
                </Card>

                <!-- Assets by Category Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Category Distribution</CardTitle>
                        <CardDescription>Asset count per category</CardDescription>
                    </CardHeader>
                    <CardContent class="h-[300px]">
                        <BarChart :data="categoryChartData" />
                    </CardContent>
                </Card>

                <!-- Monthly Trends -->
                <Card>
                    <CardHeader>
                        <CardTitle>Asset Growth Trend</CardTitle>
                        <CardDescription>New assets added over the last 6 months</CardDescription>
                    </CardHeader>
                    <CardContent class="h-[300px]">
                        <LineChart :data="trendsChartData" />
                    </CardContent>
                </Card>

                <!-- Assets by Location -->
                <Card v-if="charts.assets_by_location.length > 0">
                    <CardHeader>
                        <CardTitle>Assets by Location</CardTitle>
                        <CardDescription>Asset distribution across locations</CardDescription>
                    </CardHeader>
                    <CardContent class="h-[300px]">
                        <BarChart :data="locationChartData" />
                    </CardContent>
                </Card>
            </div>

            <!-- Tables Grid -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Recent Assignments -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Recent Assignments</CardTitle>
                                <CardDescription>Latest asset assignments</CardDescription>
                            </div>
                            <Link :href="route('assets.index')">
                                <Button variant="ghost" size="sm">
                                    View All
                                    <ArrowRight class="h-4 w-4 ml-2" />
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recent.assignments.length === 0" class="text-center py-8 text-muted-foreground">
                            <UserCheck class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p>No recent assignments</p>
                        </div>
                        <div v-else class="space-y-4">
                            <Link v-for="assignment in recent.assignments" :key="assignment.id"
                                :href="route('assets.show', assignment.asset_id)"
                                class="flex items-center justify-between border-b pb-3 last:border-0 hover:bg-accent/50 rounded-md p-2 transition-colors">
                                <div class="space-y-1">
                                    <p class="text-sm font-medium">{{ assignment.asset_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ assignment.asset_tag }}</p>
                                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                        <Users class="h-3 w-3" />
                                        <span>{{ assignment.user_name }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-muted-foreground">{{ assignment.assigned_at }}</p>
                                    <Badge variant="secondary" class="mt-1">
                                        {{ assignment.duration_days }}d
                                    </Badge>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Top Users -->
                <Card>
                    <CardHeader>
                        <CardTitle>Top Users</CardTitle>
                        <CardDescription>Users with most assets assigned</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="charts.top_users.length === 0" class="text-center py-8 text-muted-foreground">
                            <Users class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p>No user assignments yet</p>
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="(user, index) in charts.top_users" :key="user.id"
                                class="flex items-center justify-between border-b pb-3 last:border-0">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold text-sm">
                                        {{ index + 1 }}
                                    </div>
                                    <span class="font-medium">{{ user.name }}</span>
                                </div>
                                <Badge variant="secondary">
                                    {{ user.asset_count }} {{ user.asset_count === 1 ? 'asset' : 'assets' }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Upcoming Warranty Expiries -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calendar class="h-5 w-5" />
                            Warranty Expiries
                        </CardTitle>
                        <CardDescription>Assets with warranties expiring soon</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recent.warranty_expiries.length === 0"
                            class="text-center py-8 text-muted-foreground">
                            <CheckCircle class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p>No warranties expiring soon</p>
                        </div>
                        <div v-else class="space-y-4">
                            <Link v-for="asset in recent.warranty_expiries" :key="asset.id"
                                :href="route('assets.show', asset.id)"
                                class="flex items-center justify-between border-b pb-3 last:border-0 hover:bg-accent/50 rounded-md p-2 transition-colors">
                                <div class="space-y-1">
                                    <p class="text-sm font-medium">{{ asset.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ asset.asset_tag }}</p>
                                    <Badge variant="outline" class="text-xs">{{ asset.category }}</Badge>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-muted-foreground mb-1">{{ asset.warranty_expiry }}</p>
                                    <Badge :variant="getWarrantyBadgeVariant(asset.days_remaining)">
                                        <AlertCircle class="h-3 w-3 mr-1" />
                                        {{ formatDaysRemaining(asset.days_remaining) }} days {{ asset.days_remaining < 0
                                            ? 'overdue' : 'left' }}
                                    </Badge>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Maintenance -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Wrench class="h-5 w-5" />
                                    Recent Maintenance
                                </CardTitle>
                                <CardDescription>Latest maintenance activities</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recent.maintenance.length === 0" class="text-center py-8 text-muted-foreground">
                            <Wrench class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p>No recent maintenance</p>
                        </div>
                        <div v-else class="space-y-4">
                            <Link v-for="log in recent.maintenance" :key="log.id"
                                :href="route('assets.show', log.asset_id)"
                                class="flex items-center justify-between border-b pb-3 last:border-0 hover:bg-accent/50 rounded-md p-2 transition-colors">
                                <div class="space-y-1 flex-1">
                                    <p class="text-sm font-medium">{{ log.asset_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ log.description }}</p>
                                    <div class="flex items-center gap-2">
                                        <Badge variant="outline" class="text-xs">{{ log.type }}</Badge>
                                        <span class="text-xs text-muted-foreground">by {{ log.performed_by }}</span>
                                    </div>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-xs text-muted-foreground">{{ log.performed_at }}</p>
                                    <p v-if="log.cost" class="text-sm font-medium">{{ formatCurrency(log.cost) }}</p>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>