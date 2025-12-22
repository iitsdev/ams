<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Badge } from '@/components/ui/badge'
import { Plus, Pencil, Trash2, Mail, Phone, Globe, Search } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonBlock from '@/components/SkeletonBlock.vue'

interface Supplier {
    id: number
    name: string
    contact_person: string | null
    email: string | null
    phone: string | null
    address: string | null
    website: string | null
    assets_count: number
    created_at: string
}

interface Props {
    suppliers: {
        data: Supplier[]
        links: any
        meta: any
    }
    can: {
        create_supplier: boolean
        edit_supplier: boolean
        delete_supplier: boolean
    }
    filters?: {
        search?: string
    }
}

const props = defineProps<Props>()

const deleteDialog = ref(false)
const supplierToDelete = ref<Supplier | null>(null)
const isLoading = ref(false)
const search = ref('')
if (props?.filters?.search) {
    search.value = props.filters.search as string
}

const openDeleteDialog = (supplier: Supplier) => {
    supplierToDelete.value = supplier
    deleteDialog.value = true
}

const deleteSupplier = () => {
    if (supplierToDelete.value) {
        router.delete(route('suppliers.destroy', supplierToDelete.value.id), {
            onSuccess: () => {
                deleteDialog.value = false
                supplierToDelete.value = null
            }
        })
    }
}

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

watch(search, (value: string) => {
    router.get(route('suppliers.index'), { search: value }, {
        preserveState: true,
        replace: true,
        onStart: () => { isLoading.value = true },
        onFinish: () => { isLoading.value = false },
    })
})
</script>

<template>

    <Head title="Suppliers" />
    <AppLayout>
        <div class="space-y-6">
            <div class="m-2">
                <PageHeader title="Suppliers" subtitle="Manage asset suppliers">
                    <template #actions>
                        <Link v-if="can.create_supplier" :href="route('suppliers.create')">
                            <Button>
                                <Plus class="h-4 w-4 mr-2" />
                                Add Supplier
                            </Button>
                        </Link>
                    </template>
                </PageHeader>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between gap-3">
                        <CardTitle>All Suppliers</CardTitle>
                        <div class="relative w-full max-w-xs">
                            <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Search suppliers..." class="pl-10" />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead class="hidden md:table-cell">Contact Person</TableHead>
                                <TableHead>Contact Info</TableHead>
                                <TableHead class="hidden md:table-cell">Assets</TableHead>
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
                                <TableCell>
                                    <div class="space-y-2">
                                        <SkeletonBlock class="h-4 w-48" />
                                        <SkeletonBlock class="h-4 w-40" />
                                    </div>
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <SkeletonBlock class="h-4 w-16" />
                                </TableCell>
                                <TableCell class="text-right">
                                    <SkeletonBlock class="h-8 w-8 ml-auto rounded" />
                                </TableCell>
                            </TableRow>
                            <TableRow v-else-if="!suppliers.data?.length">
                                <TableCell colspan="5" class="py-10">
                                    <EmptyState title="No suppliers found"
                                        description="Create a supplier to track procurement details." />
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="supplier in suppliers.data" :key="supplier.id">
                                <TableCell class="font-medium">
                                    {{ supplier.name }}
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    {{ supplier.contact_person || '-' }}
                                </TableCell>
                                <TableCell>
                                    <div class="space-y-1 text-sm">
                                        <div v-if="supplier.email" class="flex items-center gap-1">
                                            <Mail class="h-3 w-3" />
                                            <a :href="`mailto:${supplier.email}`" class="text-blue-600 hover:underline">
                                                {{ supplier.email }}
                                            </a>
                                        </div>
                                        <div v-if="supplier.phone" class="flex items-center gap-1">
                                            <Phone class="h-3 w-3" />
                                            {{ supplier.phone }}
                                        </div>
                                        <div v-if="supplier.website" class="flex items-center gap-1">
                                            <Globe class="h-3 w-3" />
                                            <a :href="supplier.website" target="_blank"
                                                class="text-blue-600 hover:underline">
                                                Website
                                            </a>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <Badge variant="secondary">
                                        {{ supplier.assets_count }} assets
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button v-if="can.edit_supplier" size="sm" variant="outline"
                                            aria-label="Edit supplier" title="Edit"
                                            @click="router.visit(route('suppliers.edit', supplier.id))">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button v-if="can.delete_supplier" size="sm" variant="destructive"
                                            aria-label="Delete supplier" title="Delete"
                                            @click="openDeleteDialog(supplier)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <AlertDialog v-model:open="deleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Are you sure?</AlertDialogTitle>
                    <AlertDialogDescription>
                        This will permanently delete "{{ supplierToDelete?.name }}". This action cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="deleteSupplier">Delete</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>