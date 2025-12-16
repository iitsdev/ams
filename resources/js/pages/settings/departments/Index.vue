<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\pages\settings\departments\Index.vue

import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Badge } from '@/components/ui/badge'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'

interface Department {
    id: number
    name: string
    description: string | null
    users_count: number
    created_at: string
}

interface Props {
    departments: {
        data: Department[]
        links: any
        meta: any
    }
    can: {
        create_department: boolean
        edit_department: boolean
        delete_department: boolean
    }
}

const props = defineProps<Props>()

const deleteDialog = ref(false)
const departmentToDelete = ref<Department | null>(null)

const openDeleteDialog = (department: Department) => {
    departmentToDelete.value = department
    deleteDialog.value = true
}

const deleteDepartment = () => {
    if (departmentToDelete.value) {
        router.delete(route('departments.destroy', departmentToDelete.value.id), {
            onSuccess: () => {
                deleteDialog.value = false
                departmentToDelete.value = null
            }
        })
    }
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading>Departments</Heading>
                <Button v-if="can.create_department" @click="router.visit(route('departments.create'))">
                    <Plus class="h-4 w-4 mr-2" />
                    Add Department
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Departments</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead>Users</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="department in departments.data" :key="department.id">
                                <TableCell class="font-medium">
                                    {{ department.name }}
                                </TableCell>
                                <TableCell>
                                    {{ department.description || '-' }}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ department.users_count }} users
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button v-if="can.edit_department" size="sm" variant="outline"
                                            @click="router.visit(route('departments.edit', department.id))">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button v-if="can.delete_department" size="sm" variant="destructive"
                                            @click="openDeleteDialog(department)">
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
                        This will permanently delete "{{ departmentToDelete?.name }}". This action cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="deleteDepartment">Delete</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>