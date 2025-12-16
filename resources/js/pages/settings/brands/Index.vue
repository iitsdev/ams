<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\pages\brands/Index.vue

import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
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
import { Plus, MoreHorizontal, ExternalLink } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps({
    brands: Object,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Brands', href: route('brands.index') },
];

const deleteDialog = ref(false);
const brandToDelete = ref<any>(null);

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
</script>

<template>
    <Head title="Brands" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex items-center justify-between m-2">
                <h2 class="text-2xl font-bold tracking-tight">Brands</h2>
                <Link :href="route('brands.create')">
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />Add Brand
                    </Button>
                </Link>
            </div>
        </template>

        <div class="p-4">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Website</TableHead>
                            <TableHead>Assets</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="brands?.data.length === 0">
                            <TableCell colspan="5" class="text-center text-muted-foreground py-8">
                                No brands found.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="brand in brands?.data" :key="brand.id">
                            <TableCell class="font-medium">{{ brand.name }}</TableCell>
                            <TableCell>
                                <a v-if="brand.website" :href="brand.website" target="_blank"
                                    class="text-blue-600 hover:underline flex items-center gap-1">
                                    Visit <ExternalLink class="h-3 w-3" />
                                </a>
                                <span v-else class="text-muted-foreground">N/A</span>
                            </TableCell>
                            <TableCell>
                                <Badge variant="secondary">{{ brand.assets_count }} assets</Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="brand.is_active ? 'default' : 'secondary'">
                                    {{ brand.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
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
                <Pagination v-if="brands?.data.length > 0" :links="brands?.links" :from="brands?.from"
                    :to="brands?.to" :total="brands?.total" />
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