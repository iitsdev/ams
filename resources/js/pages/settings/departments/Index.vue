<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\pages\settings\departments\Index.vue

import { ref, watch, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Badge } from '@/components/ui/badge'
import { Plus, Pencil, Trash2, Search } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonBlock from '@/components/SkeletonBlock.vue'

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
    filters?: {
        search?: string
    }
}

const props = defineProps<Props>()

const deleteDialog = ref(false)
const departmentToDelete = ref<Department | null>(null)
const isLoading = ref(false)
const search = ref('')
if (props?.filters?.search) {
    search.value = props.filters.search as string
}

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
    router.get(route('departments.index'), { search: value }, {
        preserveState: true,
        replace: true,
        onStart: () => { isLoading.value = true },
        onFinish: () => { isLoading.value = false },
    })
})
</script>

<template>

    <Head title="Departments" />
    <AppLayout>
        <div class="space-y-6">
            <div class="m-2">
                <PageHeader title="Departments" subtitle="Manage departments and owners">
                    <template #actions>
                        <Link v-if="can.create_department" :href="route('departments.create')">
                            <Button>
                                <Plus class="h-4 w-4 mr-2" />
                                Add Department
                            </Button>
                        </Link>
                    </template>
                </PageHeader>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between gap-3">
                        <CardTitle>All Departments</CardTitle>
                        <div class="relative w-full max-w-xs">
                            <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Search departments..." class="pl-10" />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead class="hidden md:table-cell">Description</TableHead>
                                <TableHead class="hidden md:table-cell">Users</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="isLoading" v-for="n in 6" :key="`sk-${n}`">
                                <TableCell class="font-medium">
                                    <SkeletonBlock class="h-4 w-40" />
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <SkeletonBlock class="h-4 w-48" />
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <SkeletonBlock class="h-4 w-16" />
                                </TableCell>
                                <TableCell class="text-right">
                                    <SkeletonBlock class="h-8 w-8 ml-auto rounded" />
                                </TableCell>
                            </TableRow>
                            <TableRow v-else-if="!departments.data?.length">
                                <TableCell colspan="4" class="py-10">
                                    <EmptyState title="No departments found"
                                        description="Create your first department to group users." />
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="department in departments.data" :key="department.id">
                                <TableCell class="font-medium">
                                    {{ department.name }}
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    {{ department.description || '-' }}
                                </TableCell>
                                <TableCell class="hidden md:table-cell">
                                    <Badge variant="secondary">
                                        {{ department.users_count }} users
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button v-if="can.edit_department" size="sm" variant="outline"
                                            aria-label="Edit department" title="Edit"
                                            @click="router.visit(route('departments.edit', department.id))">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button v-if="can.delete_department" size="sm" variant="destructive"
                                            aria-label="Delete department" title="Delete"
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