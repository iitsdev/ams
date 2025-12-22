<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import PageHeader from '@/components/PageHeader.vue'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonBlock from '@/components/SkeletonBlock.vue'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import { Plus, Search, Pencil, Trash2, Package } from 'lucide-vue-next'
import { ref, watch } from 'vue'

interface Status {
    id: number
    name: string
    color: string
    description: string | null
    assets_count: number
}

const props = defineProps<{
    statuses: {
        data: Status[]
        links: any
        meta: any
    }
    filters: {
        search: string | null
    }
}>()

const search = ref(props.filters.search || '')
const isLoading = ref(false)

watch(search, (value) => {
    router.get(route('asset-statuses.index'), { search: value }, {
        preserveState: true,
        replace: true,
        onStart: () => { isLoading.value = true },
        onFinish: () => { isLoading.value = false },
    })
})

const deleteStatus = (id: number) => {
    router.delete(route('asset-statuses.destroy', id), {
        preserveScroll: true,
    })
}
</script>

<template>

    <Head title="Asset Statuses" />

    <AppLayout>
        <template #header>
            <div class="m-2">
                <PageHeader title="Asset Statuses" subtitle="Manage asset status types">
                    <template #actions>
                        <Link :href="route('asset-statuses.create')">
                            <Button>
                                <Plus class="h-4 w-4 mr-2" />
                                Add Status
                            </Button>
                        </Link>
                    </template>
                </PageHeader>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Search -->
            <Card>
                <CardContent class="pt-6">
                    <div class="relative">
                        <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                        <Input v-model="search" placeholder="Search statuses..." class="pl-10" />
                    </div>
                </CardContent>
            </Card>

            <!-- Statuses Table -->
            <Card>
                <CardHeader>
                    <CardTitle>All Statuses ({{ statuses?.meta?.total || 0 }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Color</TableHead>
                                <TableHead>Assets</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="isLoading" v-for="n in 6" :key="`sk-${n}`">
                                <TableCell class="font-medium">
                                    <SkeletonBlock class="h-5 w-32" />
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <SkeletonBlock class="w-6 h-6 rounded" />
                                        <SkeletonBlock class="h-4 w-20" />
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <SkeletonBlock class="h-4 w-12" />
                                </TableCell>
                                <TableCell>
                                    <SkeletonBlock class="h-4 w-40" />
                                </TableCell>
                                <TableCell class="text-right">
                                    <SkeletonBlock class="h-8 w-20 ml-auto rounded" />
                                </TableCell>
                            </TableRow>
                            <TableRow v-else-if="!statuses?.data?.length">
                                <TableCell colspan="5" class="py-10">
                                    <EmptyState title="No statuses found"
                                        description="Create a status to track asset lifecycle." />
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="status in statuses?.data" :key="status.id">
                                <TableCell class="font-medium">
                                    <Badge :style="{ backgroundColor: status.color, color: '#fff' }">
                                        {{ status.name }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded border"
                                            :style="{ backgroundColor: status.color }" />
                                        <span class="text-sm text-muted-foreground">{{ status.color }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-1">
                                        <Package class="h-4 w-4 text-muted-foreground" />
                                        <span>{{ status.assets_count }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="max-w-xs truncate">
                                    {{ status.description || '-' }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('asset-statuses.edit', status.id)">
                                            <Button variant="outline" size="sm">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm"
                                                    :disabled="status.assets_count > 0">
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Delete Status</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        Are you sure you want to delete "{{ status.name }}"? This action
                                                        cannot be undone.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction @click="deleteStatus(status.id)">
                                                        Delete
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>