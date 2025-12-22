<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\pages\brands/Index.vue

import { ref, watch, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
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
import { Plus, MoreHorizontal, ExternalLink, Search } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import PageHeader from '@/components/PageHeader.vue'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonBlock from '@/components/SkeletonBlock.vue'
import { type BreadcrumbItem } from '@/types';

const props = defineProps({
    brands: Object,
    filters: Object,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Brands', href: route('brands.index') },
];

const deleteDialog = ref(false);
const brandToDelete = ref<any>(null);
const isLoading = ref(false);
const search = ref('');
if ((props as any)?.filters?.search) {
    search.value = (props as any).filters.search
}

const confirmDelete = (brand: any) => {
    brandToDelete.value = brand;
    deleteDialog.value = true;
};

const deleteBrand = () => {
    if (brandToDelete.value) {
        router.delete(route('brands.destroy', brandToDelete.value.id), {
            onSuccess: () => {
                deleteDialog.value = false;
                brandToDelete.value = null;
            },
        });
    }
};

function onStart() { isLoading.value = true }
function onFinish() { isLoading.value = false }

onMounted(() => {
    window.addEventListener('inertia:start', onStart)
    window.addEventListener('inertia:finish', onFinish)
})

onUnmounted(() => {
    window.removeEventListener('inertia:start', onStart)
    window.removeEventListener('inertia:finish', onFinish)
})

watch(search, (value) => {
    router.get(route('brands.index'), { search: value }, {
        preserveState: true,
        replace: true,
        onStart: () => { isLoading.value = true },
        onFinish: () => { isLoading.value = false },
    })
})
</script>

<template>

    <Head title="Brands" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="m-2">
                <PageHeader title="Brands" subtitle="Manage asset brands">
                    <template #actions>
                        <Link :href="route('brands.create')">
                            <Button>
                                <Plus class="h-4 w-4 mr-2" />
                                Add Brand
                            </Button>
                        </Link>
                    </template>
                </PageHeader>
            </div>
        </template>

        <div class="p-4">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="border-b p-4">
                    <div class="relative w-full max-w-xs ml-auto">
                        <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                        <Input v-model="search" placeholder="Search brands..." class="pl-10" />
                    </div>
                </div>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead class="hidden md:table-cell">Website</TableHead>
                            <TableHead class="hidden md:table-cell">Assets</TableHead>
                            <TableHead class="hidden md:table-cell">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="isLoading" v-for="n in 6" :key="`sk-${n}`">
                            <TableCell class="font-medium">
                                <SkeletonBlock class="h-4 w-40" />
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                <SkeletonBlock class="h-4 w-28" />
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                <SkeletonBlock class="h-4 w-16" />
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                <SkeletonBlock class="h-5 w-20 rounded-full" />
                            </TableCell>
                            <TableCell class="text-right">
                                <SkeletonBlock class="h-8 w-8 ml-auto rounded" />
                            </TableCell>
                        </TableRow>
                        <TableRow v-else-if="brands?.data.length === 0">
                            <TableCell colspan="5" class="py-10">
                                <EmptyState title="No brands found"
                                    description="Create your first brand to organize assets." />
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="brand in brands?.data" :key="brand.id">
                            <TableCell class="font-medium">{{ brand.name }}</TableCell>
                            <TableCell class="hidden md:table-cell">
                                <a v-if="brand.website" :href="brand.website" target="_blank"
                                    class="text-blue-600 hover:underline flex items-center gap-1">
                                    Visit
                                    <ExternalLink class="h-3 w-3" />
                                </a>
                                <span v-else class="text-muted-foreground">N/A</span>
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                <Badge variant="secondary">{{ brand.assets_count }} assets</Badge>
                            </TableCell>
                            <TableCell class="hidden md:table-cell">
                                <Badge :variant="brand.is_active ? 'default' : 'secondary'">
                                    {{ brand.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
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
                                            <Link :href="route('brands.edit', brand.id)">Edit</Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="confirmDelete(brand)" class="text-red-600">
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <Pagination v-if="brands?.data.length > 0" :links="brands?.links" :from="brands?.from" :to="brands?.to"
                    :total="brands?.total" />
            </div>
        </div>
    </AppLayout>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog :open="deleteDialog" @update:open="deleteDialog = $event">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Delete Brand?</AlertDialogTitle>
                <AlertDialogDescription>
                    Are you sure you want to delete "{{ brandToDelete?.name }}"?
                    This action cannot be undone.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction as-child>
                    <Button variant="destructive" @click="deleteBrand">
                        Delete
                    </Button>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>