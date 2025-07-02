<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { MoreHorizontal, Plus, Search, Upload, Download, Filter, ArrowUpDown, ArrowUp, ArrowDown, Laptop } from 'lucide-vue-next';
import { debounce } from 'lodash';
import { type BreadcrumbItem } from '@/types';

const props = defineProps({
    assets: Object,
    filters: Object,
    dropdowns: Object,
    can: Object,
})

// --- FILTER & SORT LOGIC ---
const search = ref(props.filters?.search)
const status = ref(props.filters?.status)
const category = ref(props.filters?.category)
const location = ref(props.filters?.location)

watch([search, status, category, location], debounce(() => {
    router.get(route('assets.index'), {
        search: search.value,
        status: status.value,
        category: category.value,
        location: location.value,
        sort_by: props.filters?.sort_by,
        sort_direction: props.filters?.sort_direction,
    }, { preserveState: true, replace: true })
}, 300))

const sortBy = (column: string) => {
    router.get(route('assets.index'), {
        ...props.filters,
        sort_by: column,
        sort_direction: props.filters?.sort_by === column && props.filters.sort_direction === 'asc' ? 'desc' : 'asc',
    }, { preserveState: true, replace: true })
}

// --- SELECTION LOGIC ---
const selectedAssets = ref<number[]>([])
const pageAssetIds = computed(() => props.assets?.data.map((asset: any) => asset.id))
const allOnPageSelected = computed(() =>
    pageAssetIds.value.length > 0 && pageAssetIds.value.every(id => selectedAssets.value.includes(id))
)
function toggleAssetSelection(assetId: number) {
    const index = selectedAssets.value.indexOf(assetId)
    if (index === -1) {
        selectedAssets.value.push(assetId)
    }
    else {
        selectedAssets.value.splice(index, 1)
    }
}
function toggleSelectAllOnPage(checked: boolean) {
    if (checked) {
        selectedAssets.value = [...new Set([...selectedAssets.value, ...pageAssetIds.value])]
    }
    else {
        selectedAssets.value = selectedAssets.value.filter(id => !pageAssetIds.value.includes(id))
    }
}

// --- DELETE LOGIC ---
const isDialogOpen = ref(false)
const assetToDelete = ref(null)
const isBulkDelete = ref(false)

const confirmSingleDeletion = (asset) => {
    isBulkDelete.value = false
    assetToDelete.value = asset
    isDialogOpen.value = true
}
const confirmBulkDeletion = () => {
    isBulkDelete.value = true
    isDialogOpen.value = true
}
const processDeletion = () => {
    const onSuccess = () => { selectedAssets.value = [] }
    if (isBulkDelete.value) {
        router.post(route('assets.bulkDelete'), { ids: selectedAssets.value }, { preserveScroll: true, onSuccess })
    } else if (assetToDelete.value) {
        router.delete(route('assets.delete', assetToDelete.value.id), { preserveScroll: true, onSuccess })
    }
}

// --- HELPER FUNCTIONS ---
const getStatusVariant = (statusName: string) => { return 'default' }
const getInitials = (name: string | null | undefined) => {
    if (!name) return '??'
    return name.split(' ').map(n => n[0]).join('').toUpperCase()
}
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Assets', href: route('assets.index') },
];
</script>

<template>

    <Head title="Assets" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex items-center justify-between m-2">
                <h2 class="text-2xl font-bold tracking-tight">Asset Management</h2>
                <div class="flex items-center space-x-2">
                    <Button variant="outline">
                        <Upload class="w-4 h-4 mr-2" />Import
                    </Button>
                    <Link :href="route('assets.export')">
                    <Button variant="outline">
                        <Download class="w-4 h-4 mr-2" />Export
                    </Button>
                    </Link>
                    <Link v-if="can?.create_asset" :href="route('assets.create')">
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />Add Asset
                    </Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="p-1">
            <div class="space-y-4 rounded-lg border bg-card text-card-foreground shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="relative w-full max-w-sm">
                            <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Search..." class="pl-8" />
                        </div>
                        <Select v-model="status">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Filter by Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Statuses</SelectItem>
                                <SelectItem v-for="item in dropdowns?.statuses" :key="item.id" :value="String(item.id)">
                                    {{ item.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="category">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Filter by Category" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Categories</SelectItem>
                                <SelectItem v-for="item in dropdowns?.categories" :key="item.id"
                                    :value="String(item.id)">
                                    {{ item.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="location">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Filter by Location" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Location</SelectItem>
                                <SelectItem v-for="item in dropdowns?.locations" :key="item.id"
                                    :value="String(item.id)">
                                    {{ item.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Button v-if="selectedAssets.length > 0" variant="destructive" @click="confirmBulkDeletion">
                            Delete Selected ({{ selectedAssets.length }})
                        </Button>
                    </div>
                </div>
                <div class="border rounded-md">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[40px]">
                                    <Checkbox :checked="allOnPageSelected" @update:checked="toggleSelectAllOnPage" />
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" @click="sortBy('name')">
                                        Assets name
                                        <template v-if="filters?.sort_by === 'name'">
                                            <ArrowUp v-if="filters.sort_direction === 'asc'" class="w-4 h-4 ml-2" />
                                            <ArrowDown v-else class="w-4 h-4 ml-2" />
                                        </template>
                                        <ArrowUpDown v-else class="w-4 h-4 ml-2 text-muted-foreground" />
                                    </Button>
                                </TableHead>
                                <TableHead>Category</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Assigned To</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="assets?.data.length === 0">
                                <TableCell colspan="6" class="text-center text-muted-foreground py-8">No assets found.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="asset in assets?.data" :key="asset.id">
                                <TableCell>
                                    <Checkbox :checked="selectedAssets.includes(asset.id)"
                                        @update:checked="() => toggleAssetSelection(asset.id)" />
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <Laptop class="h-8 w-8 text-muted-foreground" />
                                        <div>
                                            <div class="font-medium">{{ asset.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ asset.serial_number }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>{{ asset.category.name }}</TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusVariant(asset.status.name)">{{ asset.status.name }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div v-if="asset.assigned_to_user" class="flex items-center gap-2">
                                        <Avatar class="h-8 w-8">
                                            <AvatarImage v-if="asset.assigned_to_user.avatar_url"
                                                :src="asset.assigned_to_user.avatar_url" alt="Avatar" />
                                            <AvatarFallback>{{ getInitials(asset.assigned_to_user.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <span>{{ asset.assigned_to_user.name }}</span>
                                    </div>
                                    <div v-else class="text-muted-foreground">N/A</div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="w-8 h-8 p-0">
                                                <MoreHorizontal class="w-4 h-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child>
                                                <Link :href="route('assets.edit', asset.id)">Edit</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="confirmSingleDeletion(asset)"
                                                class="text-red-600">Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <Pagination v-if="assets?.data.length > 0" :links="assets?.links" :from="assets?.from" :to="assets?.to"
                    :total="assets?.total" />
            </div>
        </div>
    </AppLayout>

    <AlertDialog :open="isDialogOpen" @update:open="(value) => isDialogOpen = value">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will delete the selected
                    {{ isBulkDelete ? selectedAssets.length + ' asset(s)' : 'asset' }}.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction as-child>
                    <Button variant="destructive" @click="processDeletion">Continue</Button>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>