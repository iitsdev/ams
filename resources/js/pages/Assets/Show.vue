<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardHeader,
    CardDescription,
    CardContent,
    CardAction,
    CardFooter,
    CardTitle
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Separator } from '@/components/ui/separator';
import { Plus } from 'lucide-vue-next';

const props = defineProps({
    asset: Object,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Assets', href: route('assets.index') },
    { title: 'Show', href: route('assets.show', props.asset?.id), },
];

const getStatusVariant = (statusName: string) => {
    if (['Retired', 'Archived'].includes(statusName)) return 'desctructive';
    if (['In Repair'].includes(statusName)) return 'secondary';
    return 'default';
}

// -- Modal Logic --

const today = new Date();
const formatedDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`

const isLogDialogOpen = ref(false);
const logForm = useForm({
    maintenance_type: '',
    description: '',
    cost: 0,
    performed_at: formatedDate
});

const submitLog = () => {
    logForm.post(route('assets.maintenance_logs.store', props.asset?.id), {
        preserveScroll: true,
        onSuccess: () => {
            isLogDialogOpen.value = false;
            logForm.reset();
        },
    });
};

</script>
<template>

    <Head :title="`Asset - ${props.asset?.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex items-center justify-between m-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">
                        {{ props.asset?.name }}
                    </h2>
                    <p class="text-muted-foreground">
                        {{ props.asset?.asset_tag }}
                    </p>
                </div>
                <Link :href="route('assets.edit', props.asset?.id)">
                <Button>Edit Asset</Button>
                </Link>
            </div>
        </template>
        <div class="grid gap-6 md:grid-cols-3">
            <div class="md:col-span-1 space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Status</span>
                            <Badge :variant="getStatusVariant(props.asset?.status.name)">
                                {{ props.asset?.status.name }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Category</span>
                            <span>{{ props.asset?.category.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Location</span>
                            <span>{{ props.asset?.location.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Assigned To</span>
                            <span>
                                {{ props.asset?.assigned_to_user ? props.asset?.assigned_to_user.name : 'N/A' }}
                            </span>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Serial Number</span>
                            <span>{{ props.asset?.serial_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Purchase Cost</span>
                            <span>{{ props.asset?.purchase_cost }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Purchase Date</span>
                            <span>{{ new Date(props.asset?.purchase_date).toLocaleDateString() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Warranty Expiry</span>
                            <span>{{ new Date(props.asset?.warranty_expiry).toLocaleDateString() }}</span>
                        </div>
                        <Separator />
                        <div class="flex justify-between font-semibold">
                            <span>Current Value</span>
                            <span>{{ props.asset?.depreciation.current_value ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-muted-foreground">
                            <span>Monthly Depreciation</span>
                            <span>{{ props.asset?.depreciation.monthly_depreciation ?? 'N/A' }}</span>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Barcode</CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-col items-center justify-center space-y-4">
                        <img :src="route('assets.barcode', props.asset?.id)" alt="Barcode" class="w-full">
                        <Link :href="route('assets.print', props.asset?.id)" target="_blank" as="button" class="w-full">
                        <Button variant="outline" class="w-full">Print Label</Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
            <div class="md:col-span-2">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle>Maintenance History</CardTitle>
                        <Button size="sm" @click="isLogDialogOpen = true">
                            <Plus class="w-4 h-4 mr-2" />
                            Add Log
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="props.asset?.maintenance_logs.length > 0" class="space-y-4">
                            <div v-for="log in props.asset?.maintenance_logs" :key="log.id" class="border-b pb-4">
                                <p class="font-semibold">{{ log.maintenance_type }}</p>
                                <p class="text-sm">{{ log.description }}</p>
                                <p class="text-xs text-muted-foreground mt-2">
                                    Performed by {{ log.performed_by_user.name }} on {{ log.performed_at }} - Cost: {{
                                        log.cost }}
                                </p>
                            </div>
                        </div>
                        <div v-else class="text-center text-muted-foregroud py-8">
                            No maintenance logs found.
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>

    <Dialog :open="isLogDialogOpen" @update:open="isLogDialogOpen = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Add Maintenance Log</DialogTitle>
                <DialogDescription>
                    Add new maintenance record for "{{ props.asset?.name }}."
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitLog" class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="maintenace_type">Maintenace Type</Label>
                    <Input id="maintenance_type" v-model="logForm.maintenance_type"
                        placeholder="e.g., Repair, Upgrade, Cleaning" />
                    <div v-if="logForm.errors.maintenance_type" class="text-sm text-red-600">{{
                        logForm.errors.maintenance_type }}</div>
                </div>
                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="logForm.description"
                        placeholder="Describe the work performed..." />
                    <div v-if="logForm.errors.description" class="text-sm text-red-600">{{
                        logForm.errors.description }}</div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for=cost>Cost (Optional)</Label>
                        <Input id="cost" v-model="logForm.cost" type="number" step="0.1" placeholder="e.g., 99.50" />
                        <div v-if="logForm.errors.cost" class="text-sm text-red-600">{{
                            logForm.errors.cost }}</div>
                    </div>
                    <div class="space-y-2">
                        <Label for=perfomed_at>Date Performed</Label>
                        <Input id="perfomed_at" v-model="logForm.performed_at" type="date" />
                        <div v-if="logForm.errors.performed_at" class="text-sm text-red-600">{{
                            logForm.errors.performed_at }}</div>
                    </div>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="isLogDialogOpen = false">Cancel</Button>
                    <Button type="submit" :disabled="logForm.processing">Save Log</Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>