<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\pages\assets\Show.vue

import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardHeader,
    CardContent,
    CardTitle
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    Plus,
    UserMinus,
    UserPlus,
    Calendar,
    DollarSign,
    MapPin,
    CalendarClock,
    CalendarCheck,
    Clock,
    TrendingDown,
    QrCode,
    Printer,
    Download
} from 'lucide-vue-next';
import AssignmentHistory from '@/components/AssignmentHistory.vue'

const props = defineProps({
    asset: Object,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Assets', href: route('assets.index') },
    { title: props.asset?.name || 'Asset', href: route('assets.show', props.asset?.id) },
];

const getStatusVariant = (statusName: string) => {
    if (['Retired', 'Archived'].includes(statusName)) return 'destructive';
    if (['In Repair'].includes(statusName)) return 'secondary';
    return 'default';
}

const getStatusColor = (status: any) => {
    return status?.color || '#6b7280'
}

const formatDate = (date: string | null) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

// -- Modal Logic --
const today = new Date();
const formattedDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`

const isLogDialogOpen = ref(false);
const logForm = useForm({
    maintenance_type: '',
    description: '',
    cost: 0,
    performed_at: formattedDate
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

// Unassign Dialog
const unassignDialog = ref(false)

const confirmUnassign = () => {
    unassignDialog.value = true
}

const unassignAsset = () => {
    router.post(route('assets.unassign', props.asset.id), {}, {
        onSuccess: () => {
            unassignDialog.value = false
        }
    })
}

// Barcode Dialog
const barcodeDialog = ref(false)
const barcodeUrl = computed(() => route('assets.barcode', props.asset?.id))

const openBarcodeDialog = () => {
    barcodeDialog.value = true
}

const downloadBarcode = () => {
    const link = document.createElement('a')
    link.href = barcodeUrl.value
    link.download = `barcode-${props.asset?.asset_tag}.png`
    link.click()
}

const printBarcode = () => {
    window.open(route('assets.print-label', props.asset?.id), '_blank')
}

// Assign/Reassign (optional - if you want to add these features)
const openAssignDialog = () => {
    // Implement if needed
    console.log('Open assign dialog')
}

const openReassignDialog = () => {
    // Implement if needed
    console.log('Open reassign dialog')
}
</script>

<template>

    <Head :title="`Asset - ${asset?.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex items-center justify-between m-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">
                        {{ asset?.name }}
                    </h2>
                    <p class="text-muted-foreground">
                        {{ asset?.asset_tag }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="openBarcodeDialog">
                        <QrCode class="h-4 w-4 mr-2" />
                        Barcode
                    </Button>
                    <Link :href="route('assets.edit', asset?.id)">
                        <Button>Edit Asset</Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="grid gap-6 p-4">
            <!-- Asset Information Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Asset Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Basic Information -->
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Asset Tag</span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-medium">{{ asset?.asset_tag }}</span>
                                <Button variant="ghost" size="sm" @click="openBarcodeDialog" class="h-6 px-2">
                                    <QrCode class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Status</span>
                            <Badge :style="{
                                backgroundColor: getStatusColor(asset?.status),
                                color: '#fff'
                            }">
                                {{ asset?.status?.name || 'N/A' }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Category</span>
                            <span>{{ asset?.category?.name || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Location</span>
                            <div class="flex items-center gap-2">
                                <MapPin class="h-4 w-4 text-muted-foreground" />
                                <span>{{ asset?.location?.name || 'N/A' }}</span>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Serial Number</span>
                            <span>{{ asset?.serial_number || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Brand</span>
                            <span>{{ asset?.brand || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Model</span>
                            <span>{{ asset?.model || 'N/A' }}</span>
                        </div>
                    </div>

                    <Separator />

                    <!-- Date Information -->
                    <div>
                        <h4 class="font-semibold mb-4 flex items-center gap-2">
                            <CalendarClock class="h-5 w-5" />
                            Date Information
                        </h4>
                        <div class="space-y-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground flex items-center gap-2">
                                    <Calendar class="h-4 w-4" />
                                    Purchase Date
                                </span>
                                <span>{{ formatDate(asset?.purchase_date) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground flex items-center gap-2">
                                    <CalendarCheck class="h-4 w-4" />
                                    Deployment Date
                                </span>
                                <span>{{ formatDate(asset?.deployed_at) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground flex items-center gap-2">
                                    <Clock class="h-4 w-4" />
                                    Age
                                </span>
                                <Badge v-if="asset?.years_since_purchase" variant="secondary">
                                    {{ asset.years_since_purchase }}
                                </Badge>
                                <span v-else>N/A</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Warranty Expiry</span>
                                <span>{{ formatDate(asset?.warranty_expiry) }}</span>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- Financial Information -->
                    <div>
                        <h4 class="font-semibold mb-4 flex items-center gap-2">
                            <DollarSign class="h-5 w-5" />
                            Financial Information
                        </h4>
                        <div class="space-y-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Purchase Cost</span>
                                <span class="font-medium">${{ asset?.purchase_cost || '0.00' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Current Value</span>
                                <span class="font-medium text-green-600">
                                    ${{ asset?.depreciation?.current_value || '0.00' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Accumulated Depreciation</span>
                                <span class="font-medium text-red-600 flex items-center gap-2">
                                    <TrendingDown class="h-4 w-4" />
                                    ${{ asset?.depreciation?.accumulated_depreciation || '0.00' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Monthly Depreciation</span>
                                <span>${{ asset?.depreciation?.monthly_depreciation || '0.00' }}</span>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- Assigned To -->
                    <div>
                        <div class="text-sm text-muted-foreground mb-3">Assigned To</div>
                        <div v-if="asset?.assigned_to_user" class="flex items-center gap-3">
                            <Avatar class="h-10 w-10">
                                <AvatarFallback>
                                    {{asset.assigned_to_user.name.split(' ').map(n => n[0]).join('')}}
                                </AvatarFallback>
                            </Avatar>
                            <div class="flex-1">
                                <div class="font-medium">{{ asset.assigned_to_user.name }}</div>
                                <div v-if="asset.assigned_to_user.department" class="text-sm text-muted-foreground">
                                    {{ asset.assigned_to_user.department.name }}
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" @click="openReassignDialog">
                                    <UserPlus class="h-4 w-4 mr-2" />
                                    Reassign
                                </Button>
                                <Button variant="outline" size="sm" class="text-red-600 hover:text-red-700"
                                    @click="confirmUnassign">
                                    <UserMinus class="h-4 w-4 mr-2" />
                                    Remove
                                </Button>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground flex items-center gap-3">
                            <span>Not assigned</span>
                            <Button variant="outline" size="sm" @click="openAssignDialog">
                                <UserPlus class="h-4 w-4 mr-2" />
                                Assign User
                            </Button>
                        </div>
                    </div>

                    <!-- Notes/Specifications -->
                    <div v-if="asset?.specifications || asset?.notes">
                        <Separator class="mb-4" />
                        <div v-if="asset?.specifications" class="mb-4">
                            <div class="text-sm text-muted-foreground mb-2">Specifications</div>
                            <div class="text-sm">{{ asset.specifications }}</div>
                        </div>
                        <div v-if="asset?.notes">
                            <div class="text-sm text-muted-foreground mb-2">Notes</div>
                            <div class="text-sm">{{ asset.notes }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Assignment History -->
            <AssignmentHistory v-if="asset?.assignments" :assignments="asset.assignments" />

            <!-- Maintenance Logs Card -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Maintenance History</CardTitle>
                    <Button size="sm" @click="isLogDialogOpen = true">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Log
                    </Button>
                </CardHeader>
                <CardContent>
                    <div v-if="asset?.maintenance_logs && asset.maintenance_logs.length > 0" class="space-y-4">
                        <div v-for="log in asset.maintenance_logs" :key="log.id" class="border-b pb-4 last:border-0">
                            <p class="font-semibold">{{ log.maintenance_type }}</p>
                            <p class="text-sm">{{ log.description }}</p>
                            <p class="text-xs text-muted-foreground mt-2">
                                Performed by {{ log.performed_by_user?.name || 'Unknown' }} on
                                {{ formatDate(log.performed_at) }} - Cost: ${{ log.cost || '0.00' }}
                            </p>
                        </div>
                    </div>
                    <div v-else class="text-center text-muted-foreground py-8">
                        No maintenance logs found.
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>

    <!-- Barcode Dialog -->
    <Dialog :open="barcodeDialog" @update:open="barcodeDialog = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Asset Barcode</DialogTitle>
                <DialogDescription>
                    Scan or print this barcode for "{{ asset?.name }}"
                </DialogDescription>
            </DialogHeader>
            <div class="flex flex-col items-center justify-center py-6 space-y-4">
                <div class="border rounded-lg p-4 bg-white">
                    <img :src="barcodeUrl" :alt="`Barcode for ${asset?.asset_tag}`" class="max-w-full h-auto" />
                </div>
                <p class="text-sm text-muted-foreground font-mono">{{ asset?.asset_tag }}</p>
                <div class="flex gap-2">
                    <Button variant="outline" @click="downloadBarcode">
                        <Download class="h-4 w-4 mr-2" />
                        Download
                    </Button>
                    <Button @click="printBarcode">
                        <Printer class="h-4 w-4 mr-2" />
                        Print Label
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>

    <!-- Add Maintenance Log Dialog -->
    <Dialog :open="isLogDialogOpen" @update:open="isLogDialogOpen = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Add Maintenance Log</DialogTitle>
                <DialogDescription>
                    Add new maintenance record for "{{ asset?.name }}."
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitLog" class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="maintenance_type">Maintenance Type</Label>
                    <Input id="maintenance_type" v-model="logForm.maintenance_type"
                        placeholder="e.g., Repair, Upgrade, Cleaning" />
                    <div v-if="logForm.errors.maintenance_type" class="text-sm text-red-600">
                        {{ logForm.errors.maintenance_type }}
                    </div>
                </div>
                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="logForm.description"
                        placeholder="Describe the work performed..." />
                    <div v-if="logForm.errors.description" class="text-sm text-red-600">
                        {{ logForm.errors.description }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="cost">Cost (Optional)</Label>
                        <Input id="cost" v-model="logForm.cost" type="number" step="0.01" placeholder="e.g., 99.50" />
                        <div v-if="logForm.errors.cost" class="text-sm text-red-600">
                            {{ logForm.errors.cost }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="performed_at">Date Performed</Label>
                        <Input id="performed_at" v-model="logForm.performed_at" type="date" />
                        <div v-if="logForm.errors.performed_at" class="text-sm text-red-600">
                            {{ logForm.errors.performed_at }}
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="isLogDialogOpen = false">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="logForm.processing">
                        Save Log
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Unassign Confirmation Dialog -->
    <AlertDialog :open="unassignDialog" @update:open="unassignDialog = $event">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Remove User Assignment?</AlertDialogTitle>
                <AlertDialogDescription>
                    Are you sure you want to remove <strong>{{ asset?.assigned_to_user?.name }}</strong> from
                    <strong>"{{ asset?.name }}"</strong>?
                    <br><br>
                    The asset status will be changed to <strong>"In Stock"</strong> and the assignment will be marked as
                    returned.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction as-child>
                    <Button variant="destructive" @click="unassignAsset">
                        Remove Assignment
                    </Button>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>