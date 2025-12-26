<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    sessions: any;
    locations: Array<{ id: number; name: string }>;
}>();

const form = ref<{ location_id: number | null; notes: string | null }>({
    location_id: null,
    notes: null,
});

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Audit', href: route('audits.index') }];

function startAudit() {
    router.post(route('audits.store'), form.value);
}
</script>

<template>
    <Head title="Audit Sessions" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Audit Sessions</h1>
            </div>
        </template>
        <div class="space-y-6">
            <div class="space-y-3 rounded-md border p-4">
                <div class="flex items-end gap-3">
                    <div>
                        <label class="block text-sm font-medium">Location (optional)</label>
                        <select v-model="form.location_id" class="mt-1 rounded border px-2 py-1">
                            <option :value="null">All locations</option>
                            <option v-for="loc in props.locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium">Notes</label>
                        <Input v-model="form.notes" class="mt-1 w-full" placeholder="Optional description" />
                    </div>
                    <Button @click="startAudit">Start Audit</Button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border px-3 py-2 text-left">ID</th>
                            <th class="border px-3 py-2 text-left">Location</th>
                            <th class="border px-3 py-2 text-left">Status</th>
                            <th class="hidden border px-3 py-2 text-left md:table-cell">Started By</th>
                            <th class="border px-3 py-2 text-left">Created</th>
                            <th class="hidden border px-3 py-2 text-left md:table-cell">Closed</th>
                            <th class="border px-3 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in props.sessions.data" :key="s.id" class="hover:bg-gray-50">
                            <td class="border px-3 py-2">{{ s.id }}</td>
                            <td class="border px-3 py-2">{{ s.location || '—' }}</td>
                            <td class="border px-3 py-2">{{ s.status }}</td>
                            <td class="hidden border px-3 py-2 md:table-cell">{{ s.started_by }}</td>
                            <td class="border px-3 py-2">{{ s.created_at }}</td>
                            <td class="hidden border px-3 py-2 md:table-cell">{{ s.closed_at || '—' }}</td>
                            <td class="border px-3 py-2">
                                <Link :href="route('audits.show', s.id)" class="text-blue-600 hover:underline"> Open</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
