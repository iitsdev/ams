<script setup lang="ts">
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Badge } from '@/components/ui/badge'
import { Plus, Pencil, Trash2, Mail, Phone, Globe } from 'lucide-vue-next'

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
}

const props = defineProps<Props>()

const deleteDialog = ref(false)
const supplierToDelete = ref<Supplier | null>(null)

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
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading>Suppliers</Heading>
                <Button v-if="can.create_supplier" @click="router.visit(route('suppliers.create'))">
                    <Plus class="h-4 w-4 mr-2" />
                    Add Supplier
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Suppliers</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Contact Person</TableHead>
                                <TableHead>Contact Info</TableHead>
                                <TableHead>Assets</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="supplier in suppliers.data" :key="supplier.id">
                                <TableCell class="font-medium">
                                    {{ supplier.name }}
                                </TableCell>
                                <TableCell>
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
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ supplier.assets_count }} assets
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button v-if="can.edit_supplier" size="sm" variant="outline"
                                            @click="router.visit(route('suppliers.edit', supplier.id))">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button v-if="can.delete_supplier" size="sm" variant="destructive"
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