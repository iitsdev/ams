<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
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

watch(search, (value) => {
    router.get(route('asset-statuses.index'), { search: value }, {
        preserveState: true,
        replace: true,
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
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">Asset Statuses</h2>
                    <p class="text-muted-foreground">Manage asset status types</p>
                </div>
                <Link :href="route('asset-statuses.create')">
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        Add Status
                    </Button>
                </Link>
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
                            <TableRow v-if="!statuses?.data?.length">
                                <TableCell colspan="5" class="text-center text-muted-foreground py-8">
                                    No statuses found
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