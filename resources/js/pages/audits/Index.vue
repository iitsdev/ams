<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'

const props = defineProps<{
    sessions: any
    locations: Array<{ id: number; name: string }>
}>()

const form = ref<{ location_id: number | null; notes: string | null }>({
    location_id: null,
    notes: null,
})

function startAudit() {
    router.post(route('audits.store'), form.value)
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Audit Sessions</h1>
        </div>

        <div class="p-4 border rounded-md space-y-3">
            <div class="flex gap-3 items-end">
                <div>
                    <label class="block text-sm font-medium">Location (optional)</label>
                    <select v-model="form.location_id" class="mt-1 border rounded px-2 py-1">
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
                        <th class="px-3 py-2 text-left border">ID</th>
                        <th class="px-3 py-2 text-left border">Location</th>
                        <th class="px-3 py-2 text-left border">Status</th>
                        <th class="px-3 py-2 text-left border hidden md:table-cell">Started By</th>
                        <th class="px-3 py-2 text-left border">Created</th>
                        <th class="px-3 py-2 text-left border hidden md:table-cell">Closed</th>
                        <th class="px-3 py-2 text-left border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in props.sessions.data" :key="s.id" class="hover:bg-gray-50">
                        <td class="px-3 py-2 border">{{ s.id }}</td>
                        <td class="px-3 py-2 border">{{ s.location || '—' }}</td>
                        <td class="px-3 py-2 border">{{ s.status }}</td>
                        <td class="px-3 py-2 border hidden md:table-cell">{{ s.started_by }}</td>
                        <td class="px-3 py-2 border">{{ s.created_at }}</td>
                        <td class="px-3 py-2 border hidden md:table-cell">{{ s.closed_at || '—' }}</td>
                        <td class="px-3 py-2 border">
                            <Link :href="route('audits.show', s.id)" class="text-blue-600 hover:underline">
                                Open</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
