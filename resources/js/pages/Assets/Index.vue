<script setup lang="ts">

import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button, buttonVariants } from '@/components/ui/button';
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

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'

import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command'

import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    MoreHorizontal,
    Plus,
    UserPlus,
    Search,
    Upload,
    Download,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    Laptop,
    ChevronsUpDown,
    Check,
    Clock,
    Calendar as CalendarIcon,
    User,
    FileText,
    UserMinus,
    MapPin
} from 'lucide-vue-next';
import { debounce } from 'lodash';
import { type BreadcrumbItem } from '@/types';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger
} from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AssignmentHistory from '@/components/AssignmentHistory.vue'
import axios from 'axios'
import { getInitials } from '@/composables/useInitials'
import PageHeader from '@/components/PageHeader.vue'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonBlock from '@/components/SkeletonBlock.vue'

const props = defineProps({
    assets: Object,
    filters: Object,
    dropdowns: Object,
    can: Object,
})
// Loading state for list fetches
const isLoading = ref(false)


// --- FILTER & SORT LOGIC ---
const search = ref(props.filters?.search)
const status = ref(props.filters?.status)
const category = ref(props.filters?.category)
const location = ref(props.filters?.location)
const assignedUser = ref(props.filters?.assigned_user)
const ageMinMonths = ref(props.filters?.age_min_months)
const ageMaxMonths = ref(props.filters?.age_max_months)

watch([search, status, category, location, assignedUser, ageMinMonths, ageMaxMonths], debounce(() => {
    router.get(route('assets.index'), {
        search: search.value,
        status: status.value,
        category: category.value,
        location: location.value,
        assigned_user: assignedUser.value,
        age_min_months: ageMinMonths.value,
        age_max_months: ageMaxMonths.value,
        sort_by: props.filters?.sort_by,
        sort_direction: props.filters?.sort_direction,
    }, {
        preserveState: true,
        replace: true,
        onStart: () => { isLoading.value = true },
        onFinish: () => { isLoading.value = false },
    })
}, 300))

// Sorting functions
const toggleSort = (column: string) => {
    router.get(route('assets.index'), {
        ...props.filters,
        sort_by: column,
        sort_direction: props.filters?.sort_by === column && props.filters.sort_direction === 'asc' ? 'desc' : 'asc',
    }, {
        preserveState: true,
        replace: true,
        onStart: () => { isLoading.value = true },
        onFinish: () => { isLoading.value = false },
    })
}

const isSorted = (column: string) => {
    return props.filters?.sort_by === column
}

const sortDirection = computed(() => props.filters?.sort_direction || 'desc')

// --- SELECTION LOGIC (FIXED WITH DEBUGGING) ---
const selectedAssets = ref<number[]>([])

const pageAssetIds = computed(() => props.assets?.data.map((asset: any) => asset.id) || [])

const allOnPageSelected = computed(() => {
    if (pageAssetIds.value.length === 0) return false
    return pageAssetIds.value.every(id => selectedAssets.value.includes(id))
})

const isAssetSelected = (assetId: number) => {
    return selectedAssets.value.includes(assetId)
}

// Simpler toggle function
function toggleSelectAll() {
    if (allOnPageSelected.value) {
        // Deselect all on current page
        selectedAssets.value = selectedAssets.value.filter(
            id => !pageAssetIds.value.includes(id)
        )
    } else {
        // Select all on current page
        const newSelections = [...selectedAssets.value]
        pageAssetIds.value.forEach(id => {
            if (!newSelections.includes(id)) {
                newSelections.push(id)
            }
        })
        selectedAssets.value = newSelections
    }
}

function toggleAssetSelection(assetId: number) {
    const index = selectedAssets.value.indexOf(assetId)
    if (index === -1) {
        selectedAssets.value = [...selectedAssets.value, assetId]
    } else {
        selectedAssets.value = selectedAssets.value.filter(id => id !== assetId)
    }
}

// --- DELETE LOGIC ---
const isDialogOpen = ref(false)
const assetToDelete = ref(null)
const isBulkDelete = ref(false)

const confirmSingleDeletion = (asset: any) => {
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

// --- ASSIGN LOGIC ---
const isAssignDialogOpen = ref(false);
const assetToAssign = ref<any>(null);
const comboboxOpen = ref(false);
const assignForm = useForm({
    user_id: '',
    notes: '',
    document: null,
});

const openAssignDialog = (asset: any) => {
    assetToAssign.value = asset;
    assignForm.user_id = '';
    assignForm.notes = '';
    assignForm.document = null;
    isAssignDialogOpen.value = true;
};

const assignAsset = () => {
    assignForm.post(route('assets.assign', assetToAssign.value.id), {
        onSuccess: () => {
            isAssignDialogOpen.value = false;
            assignForm.reset();
        }
    });
};

// Import Logic
const isImportDialogOpen = ref(false);
const importForm = useForm({
    file: null,
});

const submitImport = () => {
    importForm.post(route('assets.import'), {
        onSuccess: () => {
            isImportDialogOpen.value = false;
            importForm.reset();
        }
    })
};

// Reassign Dialog
const reassignDialog = ref(false)
const selectedAsset = ref<any | null>(null)
const reassignComboboxOpen = ref(false)

const reassignForm = useForm({
    user_id: '',
    notes: '',
    document: null as File | null,
})

const openReassignDialog = (asset: any) => {
    selectedAsset.value = asset
    reassignForm.user_id = asset.assigned_to_user?.id?.toString() || ''
    reassignForm.notes = ''
    reassignForm.document = null
    reassignDialog.value = true
}

const submitReassign = () => {
    if (selectedAsset.value) {
        reassignForm.post(route('assets.reassign', selectedAsset.value.id), {
            onSuccess: () => {
                reassignDialog.value = false
                reassignForm.reset()
            }
        })
    }
}

// History Dialog
const historyDialog = ref(false)
const selectedAssetForHistory = ref<any>(null)
const loadingHistory = ref(false)

const viewAssignmentHistory = async (asset: any) => {
    selectedAssetForHistory.value = {
        id: asset.id,
        name: asset.name,
        asset_tag: asset.asset_tag,
        assignments: []
    }
    historyDialog.value = true
    loadingHistory.value = true

    try {
        const response = await axios.get(route('assets.assignments', asset.id))
        selectedAssetForHistory.value = response.data.asset
    } catch (error) {
        console.error('Failed to load assignment history:', error)
    } finally {
        loadingHistory.value = false
    }
}

// Unassign Dialog
const unassignDialog = ref(false)
const assetToUnassign = ref<any>(null)

const confirmUnassign = (asset: any) => {
    assetToUnassign.value = asset
    unassignDialog.value = true
}

const unassignAsset = () => {
    if (assetToUnassign.value) {
        router.post(route('assets.unassign', assetToUnassign.value.id), {}, {
            onSuccess: () => {
                unassignDialog.value = false
                assetToUnassign.value = null
            }
        })
    }
}

// Filter helpers
const hasActiveFilters = computed(() => {
    return (search.value && search.value !== '') ||
        (status.value && status.value !== 'all') ||
        (category.value && category.value !== 'all') ||
        (location.value && location.value !== 'all') ||
        (assignedUser.value && assignedUser.value !== 'all') ||
        (ageMinMonths.value && ageMinMonths.value !== 'all') ||
        (ageMaxMonths.value && ageMaxMonths.value !== 'all')
})

const clearAllFilters = () => {
    search.value = ''
    status.value = 'all'
    category.value = 'all'
    location.value = 'all'
    assignedUser.value = 'all'
    ageMinMonths.value = 'all'
    ageMaxMonths.value = 'all'
}

const formatDate = (date: string | null) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

// Helper functions
const getStatusColor = (status: any) => {
    return status?.color || '#6b7280' // default gray if no color
}
// Using shared initials helper from composable

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Assets', href: route('assets.index') },
];
</script>

<template>

    <Head title="Assets" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="m-2">
                <PageHeader title="Asset Management">
                    <template #actions>
                        <Button variant="outline" @click="isImportDialogOpen = true">
                            <Upload class="w-4 h-4 mr-2" />Import
                        </Button>
                        <a :href="route('assets.export')"
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                            <Download class="w-4 h-4 mr-2" />
                            Export
                        </a>
                        <Link v-if="can?.create_asset" :href="route('assets.create')">
                            <Button>
                                <Plus class="h-4 w-4 mr-2" />Add Asset
                            </Button>
                        </Link>
                    </template>
                </PageHeader>
            </div>
        </template>

        <div class="p-1">
            <div class="space-y-4 rounded-lg border bg-card text-card-foreground shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 flex-wrap gap-2">
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
                                    {{ item.name }}
                                </SelectItem>
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
                                    {{ item.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="location">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Filter by Location" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Locations</SelectItem>
                                <SelectItem v-for="item in dropdowns?.locations" :key="item.id"
                                    :value="String(item.id)">
                                    {{ item.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="assignedUser">
                            <SelectTrigger class="w-[200px]">
                                <SelectValue placeholder="Filter by User" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Users</SelectItem>
                                <SelectItem value="unassigned">
                                    <div class="flex items-center gap-2">
                                        <UserMinus class="h-4 w-4" />
                                        <span>Unassigned</span>
                                    </div>
                                </SelectItem>
                                <SelectItem v-for="user in dropdowns?.users" :key="user.id" :value="String(user.id)">
                                    <div class="flex items-center gap-2">
                                        <User class="h-4 w-4" />
                                        <span>{{ user.name }}</span>
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="ageMinMonths">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Min Age (months)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Any Age</SelectItem>
                                <SelectItem value="6">6+ months</SelectItem>
                                <SelectItem value="12">12+ months</SelectItem>
                                <SelectItem value="24">24+ months</SelectItem>
                                <SelectItem value="36">36+ months</SelectItem>
                                <SelectItem value="60">60+ months</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="ageMaxMonths">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Max Age (months)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">No Max</SelectItem>
                                <SelectItem value="6">Up to 6 months</SelectItem>
                                <SelectItem value="12">Up to 12 months</SelectItem>
                                <SelectItem value="24">Up to 24 months</SelectItem>
                                <SelectItem value="36">Up to 36 months</SelectItem>
                                <SelectItem value="60">Up to 60 months</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Button v-if="selectedAssets.length > 0" variant="destructive" @click="confirmBulkDeletion">
                            Delete Selected ({{ selectedAssets.length }})
                        </Button>
                    </div>
                </div>

                <div v-if="hasActiveFilters" class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm text-muted-foreground">Active filters:</span>
                    <Badge v-if="search" variant="secondary" class="gap-1">
                        Search: {{ search }}
                        <button @click="search = ''" class="ml-1 hover:text-destructive">×</button>
                    </Badge>
                    <Badge v-if="status && status !== 'all'" variant="secondary" class="gap-1">
                        Status: {{dropdowns?.statuses.find(s => String(s.id) === status)?.name}}
                        <button @click="status = 'all'" class="ml-1 hover:text-destructive">×</button>
                    </Badge>
                    <Badge v-if="category && category !== 'all'" variant="secondary" class="gap-1">
                        Category: {{dropdowns?.categories.find(c => String(c.id) === category)?.name}}
                        <button @click="category = 'all'" class="ml-1 hover:text-destructive">×</button>
                    </Badge>
                    <Badge v-if="location && location !== 'all'" variant="secondary" class="gap-1">
                        Location: {{dropdowns?.locations.find(l => String(l.id) === location)?.name}}
                        <button @click="location = 'all'" class="ml-1 hover:text-destructive">×</button>
                    </Badge>
                    <Badge v-if="assignedUser && assignedUser !== 'all'" variant="secondary" class="gap-1">
                        User: {{assignedUser === 'unassigned' ? 'Unassigned' :
                            dropdowns?.users.find(u => String(u.id) === assignedUser)?.name}}
                        <button @click="assignedUser = 'all'" class="ml-1 hover:text-destructive">×</button>
                    </Badge>
                    <Badge v-if="ageMinMonths && ageMinMonths !== 'all'" variant="secondary" class="gap-1">
                        Min Age: {{ ageMinMonths }} months
                        <button @click="ageMinMonths = 'all'" class="ml-1 hover:text-destructive">×</button>
                    </Badge>
                    <Badge v-if="ageMaxMonths && ageMaxMonths !== 'all'" variant="secondary" class="gap-1">
                        Max Age: {{ ageMaxMonths }} months
                        <button @click="ageMaxMonths = 'all'" class="ml-1 hover:text-destructive">×</button>
                    </Badge>
                    <Button v-if="hasActiveFilters" variant="ghost" size="sm" @click="clearAllFilters" class="text-xs">
                        Clear all filters
                    </Button>
                </div>

                <div class="border rounded-md overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-12">
                                    <!-- Native checkbox for debugging -->
                                    <Checkbox :checked="allOnPageSelected" @update:checked="toggleSelectAll"
                                        aria-label="Select all on page" />
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" @click="toggleSort('name')" class="flex items-center gap-1">
                                        Asset Name
                                        <ArrowUpDown v-if="!isSorted('name')" class="h-4 w-4" />
                                        <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4" />
                                        <ArrowDown v-else class="h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead class="hidden md:table-cell">
                                    <Button variant="ghost" @click="toggleSort('category')"
                                        class="flex items-center gap-1">
                                        Category
                                        <ArrowUpDown v-if="!isSorted('category')" class="h-4 w-4" />
                                        <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4" />
                                        <ArrowDown v-else class="h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" @click="toggleSort('status')"
                                        class="flex items-center gap-1">
                                        Status
                                        <ArrowUpDown v-if="!isSorted('status')" class="h-4 w-4" />
                                        <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4" />
                                        <ArrowDown v-else class="h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" @click="toggleSort('location')"
                                        class="flex items-center gap-1">
                                        Location
                                        <ArrowUpDown v-if="!isSorted('location')" class="h-4 w-4" />
                                        <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4" />
                                        <ArrowDown v-else class="h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead class="hidden md:table-cell">
                                    <Button variant="ghost" @click="toggleSort('assigned_user_name')"
                                        class="flex items-center gap-1">
                                        Assigned To
                                        <ArrowUpDown v-if="!isSorted('assigned_user_name')" class="h-4 w-4" />
                                        <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4" />
                                        <ArrowDown v-else class="h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead class="hidden md:table-cell">
                                    <Button variant="ghost" @click="toggleSort('purchase_date')"
                                        class="flex items-center gap-1">
                                        Purchase Date
                                        <ArrowUpDown v-if="!isSorted('purchase_date')" class="h-4 w-4" />
                                        <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4" />
                                        <ArrowDown v-else class="h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" @click="toggleSort('purchase_date')"
                                        class="flex items-center gap-1">
                                        Age
                                        <ArrowUpDown v-if="!isSorted('purchase_date')" class="h-4 w-4" />
                                        <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4" />
                                        <ArrowDown v-else class="h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead class="hidden md:table-cell">
                                    <Button variant="ghost" @click="toggleSort('value')"
                                        class="flex items-center gap-1">
                                        Value
                                        <ArrowUpDown v-if="!isSorted('value')" class="h-4 w-4" />
                                        <ArrowUp v-else-if="sortDirection === 'asc'" class="h-4 w-4" />
                                        <ArrowDown v-else class="h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <!-- Loading skeleton rows -->
                            <TableRow v-if="isLoading" v-for="n in 8" :key="`sk-${n}`">
                                <TableCell>
                                    <SkeletonBlock class="h-4 w-4 rounded" />
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <SkeletonBlock class="h-8 w-8 rounded" />
                                        <div class="space-y-2">
                                            <SkeletonBlock class="h-4 w-40" />
                                            <SkeletonBlock class="h-3 w-24" />
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <SkeletonBlock class="h-4 w-28" />
                                </TableCell>
                                <TableCell>
                                    <SkeletonBlock class="h-5 w-20 rounded-full" />
                                </TableCell>
                                <TableCell>
                                    <SkeletonBlock class="h-4 w-28" />
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <div class="flex items-center gap-2">
                                        <SkeletonBlock class="h-8 w-8 rounded-full" />
                                        <SkeletonBlock class="h-4 w-24" />
                                    </div>
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <SkeletonBlock class="h-4 w-24" />
                                </TableCell>
                                <TableCell>
                                    <SkeletonBlock class="h-5 w-16 rounded" />
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <SkeletonBlock class="h-4 w-16" />
                                </TableCell>
                                <TableCell class="text-right">
                                    <SkeletonBlock class="h-8 w-8 rounded" />
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="assets?.data.length === 0">
                                <TableCell colspan="10" class="py-10">
                                    <EmptyState title="No assets found"
                                        description="Try adjusting filters or create a new asset.">
                                        <template #actions>
                                            <div class="flex items-center justify-center gap-2">
                                                <Button variant="outline" @click="clearAllFilters">Clear
                                                    filters</Button>
                                                <Link v-if="can?.create_asset" :href="route('assets.create')">
                                                    <Button>
                                                        <Plus class="h-4 w-4 mr-2" />Add Asset
                                                    </Button>
                                                </Link>
                                            </div>
                                        </template>
                                    </EmptyState>
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="asset in assets?.data" :key="asset.id">
                                <TableCell>
                                    <!-- Native checkbox for debugging -->
                                    <input type="checkbox" :checked="isAssetSelected(asset.id)"
                                        @change="toggleAssetSelection(asset.id)"
                                        class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary focus:ring-2 cursor-pointer" />
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <Laptop class="h-8 w-8 text-muted-foreground" />
                                        <div>
                                            <div class="font-medium">{{ asset.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ asset.asset_tag }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell class="hidden md:table-cell">{{ asset.category?.name || 'N/A' }}</TableCell>
                                <TableCell>
                                    <Badge :style="{
                                        backgroundColor: getStatusColor(asset.status),
                                        color: '#fff'
                                    }">
                                        {{ asset.status?.name || 'N/A' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <MapPin class="h-4 w-4 text-muted-foreground" />
                                        <span>{{ asset.location?.name || 'N/A' }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <div v-if="asset.assigned_to_user" class="flex items-center gap-2">
                                        <Avatar class="h-8 w-8">
                                            <AvatarImage v-if="asset.assigned_to_user.avatar_url"
                                                :src="asset.assigned_to_user.avatar_url" alt="Avatar" />
                                            <AvatarFallback>{{ getInitials(asset.assigned_to_user.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <span>{{ asset.assigned_to_user.name }}</span>

                                        <div class="flex items-center gap-1 ml-auto">
                                            <TooltipProvider>
                                                <Tooltip>
                                                    <TooltipTrigger as-child>
                                                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-full"
                                                            aria-label="Reassign asset" title="Reassign"
                                                            @click="openReassignDialog(asset)">
                                                            <UserPlus class="h-4 w-4" />
                                                        </Button>
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p>Reassign to Another User</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>

                                            <TooltipProvider>
                                                <Tooltip>
                                                    <TooltipTrigger as-child>
                                                        <Button variant="ghost" size="icon"
                                                            aria-label="Remove assignment" title="Remove assignment"
                                                            class="h-8 w-8 rounded-full text-red-600 hover:text-red-700 hover:bg-red-50"
                                                            @click="confirmUnassign(asset)">
                                                            <UserMinus class="h-4 w-4" />
                                                        </Button>
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p>Remove Assignment</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button variant="ghost" size="icon" class="rounded-full"
                                                        aria-label="Assign to user" title="Assign to user"
                                                        @click="openAssignDialog(asset)">
                                                        <UserPlus class="h-4 w-4" />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>Assign to User</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </div>
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <div class="text-sm">{{ formatDate(asset.purchase_date) }}</div>
                                </TableCell>
                                <TableCell>
                                    <TooltipProvider v-if="asset.age">
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <Badge variant="secondary">
                                                    {{ asset.age.years }}y {{ asset.age.months }}m
                                                </Badge>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Total {{ asset.age.total_months }} months</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <span v-else class="text-muted-foreground text-sm">N/A</span>
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <div v-if="asset.depreciation" class="text-sm">
                                        ${{ asset.depreciation.current_value }}
                                    </div>
                                    <span v-else class="text-muted-foreground text-sm">N/A</span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="w-8 h-8 p-0" aria-label="Open actions"
                                                title="Actions">
                                                <MoreHorizontal class="w-4 h-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child>
                                                <Link :href="route('assets.show', asset.id)">View</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem v-if="!asset.assigned_to_user"
                                                @click="openAssignDialog(asset)">
                                                <UserPlus class="h-4 w-4 mr-2" />
                                                Assign to User
                                            </DropdownMenuItem>
                                            <DropdownMenuItem v-else @click="openReassignDialog(asset)">
                                                <UserPlus class="h-4 w-4 mr-2" />
                                                Reassign
                                            </DropdownMenuItem>
                                            <DropdownMenuItem v-if="asset.assigned_to_user"
                                                @click="confirmUnassign(asset)" class="text-red-600">
                                                <UserMinus class="h-4 w-4 mr-2" />
                                                Remove Assignment
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="viewAssignmentHistory(asset)">
                                                <Clock class="h-4 w-4 mr-2" />
                                                Assignment History
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link :href="route('assets.edit', asset.id)">Edit</Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="confirmSingleDeletion(asset)"
                                                class="text-red-600">
                                                Delete
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

    <!-- Assign Dialog -->
    <Dialog :open="isAssignDialogOpen" @update:open="isAssignDialogOpen = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Assign Asset</DialogTitle>
                <DialogDescription>
                    Assign "{{ assetToAssign?.name }}" to a user.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="assignAsset" class="space-y-4 py-4">
                <div>
                    <Label for="user" class="mb-2 block">User</Label>

                    <Popover v-model:open="comboboxOpen">
                        <PopoverTrigger as-child>
                            <Button variant="outline" role="combobox" :aria-expanded="comboboxOpen"
                                class="w-full justify-between">
                                {{assignForm.user_id ? dropdowns?.users.find((user) => user.id ===
                                    assignForm.user_id)?.name : "Select user..."}}
                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-[375px] p-0">
                            <Command>
                                <CommandInput placeholder="Search user..." />
                                <CommandEmpty>No user found.</CommandEmpty>
                                <CommandList>
                                    <CommandGroup>
                                        <CommandItem v-for="user in dropdowns?.users" :key="user.id" :value="user.id"
                                            @select="(ev) => {
                                                assignForm.user_id = ev.detail.value
                                                comboboxOpen = false
                                            }">
                                            <Check
                                                :class="cn('mr-2 h-4 w-4', assignForm.user_id === user.id ? 'opacity-100' : 'opacity-0')" />
                                            {{ user.name }}
                                        </CommandItem>
                                    </CommandGroup>
                                </CommandList>
                            </Command>
                        </PopoverContent>
                    </Popover>
                    <div v-if="assignForm.errors.user_id" class="text-sm text-red-600 mt-1">
                        {{ assignForm.errors.user_id }}
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="notes">Notes (Optional)</Label>
                    <Textarea id="notes" v-model="assignForm.notes" placeholder="Add any assignment notes..." />
                </div>

                <div class="space-y-2">
                    <Label for="document">Attachment (Optional)</Label>
                    <Input id="document" type="file" @input="assignForm.document = $event.target.files[0]"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                    <p class="text-xs text-muted-foreground">Max 5MB. Supported: PDF, DOC, DOCX, JPG, PNG</p>
                </div>

                <div class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="isAssignDialogOpen = false">Cancel</Button>
                    <Button type="submit" :disabled="assignForm.processing">Assign Asset</Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Dialog -->
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

    <!-- Import Dialog -->
    <Dialog :open="isImportDialogOpen" @update:open="isImportDialogOpen = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Import Assets</DialogTitle>
                <DialogDescription>
                    Select a CSV or Excel file to bulk-import assets. The file must have columns with headings: `name`,
                    `serial_number`, `purchase_cost`, `purchase_date`.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitImport" class="space-y-4 py-4">
                <div>
                    <Label for="file">Spreadsheet File</Label>
                    <Input id="file" type="file" @input="importForm.file = $event.target.files[0]" class="mt-1" />
                    <progress v-if="importForm.progress" :value="importForm.progress.percentage" max="100"
                        class="w-full mt-2">
                        {{ importForm.progress.percentage }}%
                    </progress>
                    <div v-if="importForm.errors.file" class="text-sm text-red-600 mt-1">
                        {{ importForm.errors.file }}
                    </div>
                </div>

                <div class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="isImportDialogOpen = false">Cancel</Button>
                    <Button type="submit" :disabled="importForm.processing">Upload & Import</Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Reassign Dialog -->
    <Dialog v-model:open="reassignDialog">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>
                    {{ selectedAsset?.assigned_to_user ? 'Reassign Asset' : 'Assign Asset' }}
                </DialogTitle>
                <DialogDescription>
                    {{ selectedAsset?.assigned_to_user
                        ? `Reassign "${selectedAsset?.name}" to a different user`
                        : `Assign "${selectedAsset?.name}" to a user`
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitReassign" class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="reassign-user" class="mb-2 block">Select User *</Label>

                    <Popover v-model:open="reassignComboboxOpen">
                        <PopoverTrigger as-child>
                            <Button variant="outline" role="combobox" :aria-expanded="reassignComboboxOpen"
                                class="w-full justify-between">
                                {{reassignForm.user_id ? dropdowns?.users.find((user) => user.id.toString() ===
                                    reassignForm.user_id)?.name : "Select user..."}}
                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-[450px] p-0">
                            <Command>
                                <CommandInput placeholder="Search user..." />
                                <CommandEmpty>No user found.</CommandEmpty>
                                <CommandList>
                                    <CommandGroup>
                                        <CommandItem v-for="user in dropdowns?.users" :key="user.id"
                                            :value="user.id.toString()" @select="(ev) => {
                                                reassignForm.user_id = ev.detail.value
                                                reassignComboboxOpen = false
                                            }">
                                            <Check
                                                :class="cn('mr-2 h-4 w-4', reassignForm.user_id === user.id.toString() ? 'opacity-100' : 'opacity-0')" />
                                            {{ user.name }}
                                        </CommandItem>
                                    </CommandGroup>
                                </CommandList>
                            </Command>
                        </PopoverContent>
                    </Popover>
                    <div v-if="reassignForm.errors.user_id" class="text-sm text-red-600 mt-1">
                        {{ reassignForm.errors.user_id }}
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="reassign-notes">Notes (Optional)</Label>
                    <Textarea id="reassign-notes" v-model="reassignForm.notes" placeholder="Add assignment notes..."
                        rows="3" />
                </div>

                <div class="space-y-2">
                    <Label for="reassign-document">Attachment (Optional)</Label>
                    <Input id="reassign-document" type="file"
                        @change="reassignForm.document = ($event.target as HTMLInputElement).files?.[0] || null"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                    <p class="text-xs text-muted-foreground">
                        Max 5MB. Supported: PDF, DOC, DOCX, JPG, PNG
                    </p>
                </div>

                <div class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="reassignDialog = false">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="reassignForm.processing">
                        {{ reassignForm.processing ? 'Processing...' : (selectedAsset?.assigned_to_user ? 'Reassign' :
                            'Assign') }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Assignment History Dialog -->
    <Dialog v-model:open="historyDialog">
        <DialogContent class="max-w-4xl max-h-[85vh] flex flex-col">
            <DialogHeader class="flex-shrink-0">
                <DialogTitle>Assignment History</DialogTitle>
                <DialogDescription v-if="selectedAssetForHistory">
                    {{ selectedAssetForHistory?.name }} - {{ selectedAssetForHistory?.asset_tag }}
                </DialogDescription>
            </DialogHeader>

            <div class="flex-1 overflow-y-auto -mx-6 px-6">
                <div v-if="loadingHistory" class="flex items-center justify-center py-12">
                    <div class="flex flex-col items-center gap-3">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                        <p class="text-sm text-muted-foreground">Loading assignment history...</p>
                    </div>
                </div>

                <div v-else-if="selectedAssetForHistory">
                    <div class="space-y-4">
                        <div v-if="!selectedAssetForHistory.assignments || selectedAssetForHistory.assignments.length === 0"
                            class="text-center py-8 text-muted-foreground">
                            <User class="h-12 w-12 mx-auto mb-3 opacity-50" />
                            <p>No assignment history available</p>
                            <p class="text-sm">This asset has never been assigned to anyone</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div class="flex items-center justify-between mb-4">
                                <Badge variant="secondary">
                                    {{ selectedAssetForHistory.assignments.length }}
                                    {{ selectedAssetForHistory.assignments.length === 1 ? 'Assignment' : 'Assignments'
                                    }}
                                </Badge>
                            </div>

                            <div v-for="assignment in selectedAssetForHistory.assignments" :key="assignment.id"
                                class="relative border rounded-lg p-4 hover:bg-accent/50 transition-colors"
                                :class="{ 'border-primary bg-primary/5': assignment.is_current }">
                                <Badge v-if="assignment.is_current" variant="default" class="absolute top-2 right-2">
                                    Current
                                </Badge>

                                <div class="flex items-start gap-4">
                                    <Avatar class="h-12 w-12">
                                        <AvatarFallback>{{ getInitials(assignment.user.name) }}</AvatarFallback>
                                    </Avatar>

                                    <div class="flex-1 space-y-3">
                                        <div>
                                            <h4 class="font-semibold">{{ assignment.user.name }}</h4>
                                            <p class="text-sm text-muted-foreground">{{ assignment.user.email }}</p>
                                            <Badge v-if="assignment.user.department" variant="outline" class="mt-1">
                                                {{ assignment.user.department.name }}
                                            </Badge>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                            <div class="flex items-center gap-2">
                                                <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                                                <div>
                                                    <p class="text-muted-foreground">Assigned</p>
                                                    <p class="font-medium">{{ new
                                                        Date(assignment.assigned_at).toLocaleDateString() }}</p>
                                                </div>
                                            </div>

                                            <div v-if="assignment.returned_at" class="flex items-center gap-2">
                                                <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                                                <div>
                                                    <p class="text-muted-foreground">Returned</p>
                                                    <p class="font-medium">{{ new
                                                        Date(assignment.returned_at).toLocaleDateString() }}</p>
                                                </div>
                                            </div>
                                            <div v-else class="flex items-center gap-2">
                                                <Clock class="h-4 w-4 text-green-600" />
                                                <div>
                                                    <p class="text-muted-foreground">In Use For</p>
                                                    <p class="font-medium text-green-600">{{ assignment.duration_days }}
                                                        days</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <Badge variant="secondary" class="flex items-center gap-1">
                                                <Clock class="h-3 w-3" />
                                                Duration: {{ assignment.duration_days }} days
                                            </Badge>
                                        </div>

                                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                            <User class="h-4 w-4" />
                                            <span>Assigned by {{ assignment.assigned_by.name }}</span>
                                        </div>

                                        <div v-if="assignment.notes" class="bg-muted/50 rounded-md p-3">
                                            <p class="text-sm font-medium mb-1">Notes:</p>
                                            <p class="text-sm text-muted-foreground">{{ assignment.notes }}</p>
                                        </div>

                                        <div v-if="assignment.document_path">
                                            <Button variant="outline" size="sm" as="a"
                                                :href="route('assets.assignments.document', [selectedAssetForHistory.id, assignment.id])"
                                                target="_blank" rel="noopener" :download="`assignment-${assignment.id}`"
                                                class="gap-2">
                                                <FileText class="h-4 w-4" />
                                                View Document
                                                <Download class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>

    <!-- Unassign Confirmation Dialog -->
    <AlertDialog :open="unassignDialog" @update:open="unassignDialog = $event">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Remove User Assignment?</AlertDialogTitle>
                <AlertDialogDescription>
                    Are you sure you want to remove <strong>{{ assetToUnassign?.assigned_to_user?.name }}</strong> from
                    <strong>"{{ assetToUnassign?.name }}"</strong>?
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